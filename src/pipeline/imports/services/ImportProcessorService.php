<?php
/**
 * ImportProcessorService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\imports\services
 */
namespace fractalCms\importExport\pipeline\imports\services;

use fractalCms\importExport\exceptions\ImportErrorCollector;
use fractalCms\importExport\interfaces\ImportProcessor as ImportProcessorInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\contexts\Import as ImportContext;
use fractalCms\importExport\interfaces\ImportInserter;
use fractalCms\importExport\interfaces\ImportReader;
use fractalCms\importExport\exceptions\ImportError;
use fractalCms\importExport\interfaces\RowImportProcessor;
use fractalCms\importExport\interfaces\DataMapper;
use yii\db\Transaction;
use yii\helpers\Json;
use yii\web\Application as WebApplication;
use Exception;
use Yii;

final class ImportProcessorService implements ImportProcessorInterface
{



    /**
     * run
     *
     * @param  ImportReader   $reader
     * @param  ImportMapper   $mapper
     * @param  ImportInserter $inserter
     * @param  ImportConfig   $importConfig
     * @param  string         $filePath
     * @param  bool           $isTest
     * @param  array          $params
     *
     * @return ImportJob
     */
    public function run(
        ImportReader $reader,
        DataMapper $mapper,
        ImportInserter $inserter,
        ImportConfig $importConfig,
        string $filePath,
        bool $isTest = false,
        array $params = []
        ): ImportJob
    {
        try {
            $errorCollector = new ImportErrorCollector();
            $baseImportContext = new ImportContext(
                config: $importConfig,
                errors: $errorCollector,
                stopOnError: $importConfig->stopOnError,
                dryRun:$isTest,
                rowNumber: -1,
                params: $params
            );
            $rowProcessor = $importConfig->getRowProcessor();
            $importJob = new ImportJob(['scenario' => ImportJob::SCENARIO_CREATE]);
            $importJob->importConfigId = $importConfig->id;
            if (Yii::$app instanceof WebApplication) {
                $importJob->userId = Yii::$app->user->identity->getId();
            }
            $reader->open($filePath, ['maxColumns' => $importConfig->getImportColumns()->count()]);
            $importJob->type = ImportJob::TYPE_IMPORT;
            $importJob->filePath = $filePath;
            $importJob->status = ImportJob::STATUS_RUNNING;
            $importJob->successRows = 0;
            $importJob->errorRows = 0;
            //$importJob->totalRows = ;
            $importJob->save();
            $importJob->refresh();
            $transaction = null;
            if ((boolean)$importConfig->stopOnError === true || $baseImportContext->dryRun === true) {
                $transaction = Yii::$app->db->beginTransaction();
            }
            foreach($reader->read() as $indexRow => $rows) {
                foreach($rows as $row) {
                    $row = $mapper->map($row, $importConfig, $indexRow);
                    if ($rowProcessor instanceof RowImportProcessor) {
                        try {
                            $result = $rowProcessor->process(
                                row:$row,
                                context:$baseImportContext->withRowNumber($indexRow),
                                params:$params
                            );
                            if ($result->handled === true) {
                                $importJob->successRows++;
                                continue;
                            }
                            $row = $result->attributes ?? $row;
                        } catch (Exception $e) {
                            $error = new ImportError($indexRow, '*', $e->getMessage());
                            $errorCollector->add($error);
                            $importJob->errorRows++;
                            if ($importConfig->stopOnError) {
                                break;
                            }
                            continue;
                        }
                    }
                    $importResult = $inserter->insert($importConfig, $row, $indexRow);
                    
                    if ($importResult->success === false) {
                        $importJob->errorRows += 1;
                        /** @var ImportError $error */
                        foreach ($importResult->errors as $error) {
                            $errorCollector->add(
                                $error
                            );
                        }
                    } else {
                        $importJob->successRows += 1;
                    }


                }
            }
            if ($errorCollector->hasErrors() === true) {
                $importJob->status = ImportJob::STATUS_FAILED;
            } else {
                $importJob->status = ImportJob::STATUS_SUCCESS;
            }
            if ($transaction instanceof Transaction) {
                if ($baseImportContext->dryRun === true || $errorCollector->hasErrors() === true) {
                    $transaction->rollBack();
                } else {
                    $transaction->commit();
                }
            }
            $importJob->errorCollector = $errorCollector;
            if ($importJob->errorRows < 50) {
                $importJob->errors = Json::encode($errorCollector->toCsvRows());
            }
            $importJob->saveFileErrorCsv();
            $importJob->save(false);
            $importJob->refresh();
            $reader->close();
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}