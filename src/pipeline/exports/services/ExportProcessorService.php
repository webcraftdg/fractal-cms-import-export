<?php
/**
 * ExportProcessorService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\exports\services
 */
namespace fractalCms\importExport\pipeline\exports\services;

use fractalCms\importExport\pipeline\interfaces\ExportProcessor;
use fractalCms\importExport\io\interfaces\CountableDataReader;
use fractalCms\importExport\pipeline\interfaces\DataMapper;
use fractalCms\importExport\runtime\contexts\Export as ExportContext;
use fractalCms\importExport\pipeline\interfaces\RowExportProcessor;
use fractalCms\importExport\models\ImportJob;
use Exception;
use Yii;

class ExportProcessorService implements ExportProcessor
{
    public function run(
        CountableDataReader $reader,
        DataMapper $mapper,
        ExportContext $context,
        string $filePath,
        bool $isTest = false,
        array $params = []
        ): ImportJob
    {
        try {
            $totalCount = 0;
            $rowProcessor = $context->config->getRowProcessor();
            $importJob = $context->config->createImportJob($totalCount);
            $context->writer->open($context->writerContext);
            $context->writePreambleOne($context->writerContext->preamble, $context->rowOffset, $context->colOffset);
            try {
                $indexRow = 0;
                $totalCount = $reader->count();
                foreach ($reader->read() as $rows) {
                    foreach($rows as $row) {
                        //ColumnTransformer
                        $row = $mapper->map($row, $context->config, $indexRow);
                        $context = $context->withRowNumber($indexRow);
                        try {
                            if ($rowProcessor instanceof RowExportProcessor) {
                                $result = $rowProcessor->process(
                                    row: $row,
                                    context: $context,
                                    params:$params
                                );
                                if ($result->handled === true) {
                                    $importJob->successRows++;
                                    continue;
                                }
                            }
                            $row = $result->attributes ?? $row;
                        } catch (Exception $e) {
                            $importJob->errorRows++;
                            if ($context->config->stopOnError) {
                                break;
                            }
                            continue;
                        }
                        $context->writeRow($row, $indexRow);
                        $importJob->successRows++;
                        $indexRow++;
                    }
                }
                $status = ImportJob::STATUS_SUCCESS;
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $status = ImportJob::STATUS_FAILED;
            } finally {
                $reader->close();
            }
            $importJob->totalRows = $totalCount;
            $importJob->status = $status;
            $importJob->filePath = $context->writerContext->relativePath;
            $importJob->save();
            $context->finalize($context->writerContext);
            return $importJob;

        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
