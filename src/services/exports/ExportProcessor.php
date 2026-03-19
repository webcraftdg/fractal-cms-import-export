<?php
/**
 * ExportXlsx.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services\exports
 */
namespace fractalCms\importExport\services\exports;

use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\interfaces\CountableDataReader;
use fractalCms\importExport\interfaces\DataMapper;
use fractalCms\importExport\interfaces\ExportProcessor as InterfacesExportProcessor;
use fractalCms\importExport\contexts\Export as ExportContext;
use fractalCms\importExport\interfaces\RowExportProcessor;
use Exception;
use Yii;

class ExportProcessor implements InterfacesExportProcessor
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
                $totalCount = $reader->count();
                foreach ($reader->read() as $rowIndex => $row) {
                    //ColumnTransformer
                    $row = $mapper->map($row, $context->config, $rowIndex);
                    $context = $context->withRowNumber($rowIndex);
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
                    $context->writeRow($row, $rowIndex);
                    $importJob->successRows++;
                }
                $status = ImportJob::STATUS_SUCCESS;
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $status = ImportJob::STATUS_FAILED;
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
