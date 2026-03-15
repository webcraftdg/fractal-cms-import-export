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

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\interfaces\CountableDataReader;
use fractalCms\importExport\interfaces\DataMapper;
use fractalCms\importExport\interfaces\ExportProcessor as InterfacesExportProcessor;
use fractalCms\importExport\interfaces\WriterInterface;
use fractalCms\importExport\contexts\Export as ExportContext;
use Exception;
use fractalCms\importExport\interfaces\RowExportProcessor;
use Yii;

class ExportProcessor implements InterfacesExportProcessor
{
    public function run(
        CountableDataReader $reader,
        DataMapper $mapper,
        WriterInterface $writer,
        ImportConfig $config,
        string $filePath,
        bool $isTest = false,
        array $params = []
        ): ImportJob
    {
        try {
            $totalCount = 0;
            $baseExportContext = new ExportContext(
                config: $config,
                dryRun: false,
                rowNumber: -1,
                writer: $writer,
                params: $params
            );
            $rowProcessor = $config->getRowProcessor();
            $headers = [];
            $configColumns = [];
            /** @var ImportConfigColumn $column */
            foreach ($config->getImportColumns()->each() as $column) {
                $headers[] = $column->target;
                $configColumns[] = $column;
            }
            $baseExportContext->writeRow('csv', $headers);
            $importJob = $config->createImportJob($totalCount);
            try {
                $totalCount = $reader->count();
                foreach ($reader->read() as $rowIndex => $row) {
                    //ColumnTransformer
                    $row = $mapper->map($row, $config, $rowIndex);
                    $baseExportContext = $baseExportContext->withRowNumber($rowIndex);
                    try {
                        if ($rowProcessor instanceof RowExportProcessor) {

                            $result = $rowProcessor->process(
                                $row,
                                $baseExportContext
                            );
                            if ($result->handled === true) {
                                $importJob->successRows++;
                                continue;
                            }
                        }
                        $row = $result->attributes ?? $row;
                    } catch (Exception $e) {
                        $importJob->errorRows++;
                        if ($config->stopOnError) {
                            break;
                        }
                        continue;
                    }
                    $baseExportContext->writeRow('csv', $row, $rowIndex);
                    $importJob->successRows++;
                }
                $status = ImportJob::STATUS_SUCCESS;
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $status = ImportJob::STATUS_FAILED;
            }
            $importJob->totalRows = $totalCount;
            $importJob->status = $status;
            $importJob->filePath = $filePath;
            $importJob->save();
            $writer->close();
            return $importJob;

        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
