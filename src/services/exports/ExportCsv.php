<?php
/**
 * ExportCsv.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services\exports
 */
namespace fractalCms\importExport\services\exports;

use fractalCms\importExport\db\SqlIterator;
use fractalCms\importExport\interfaces\Export;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\services\Export as ExportService;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\Transformer as TransformerService;
use Yii;
use yii\db\Query;

class ExportCsv implements Export
{

    /**
     * Run export CSv
     * @param ImportConfig $importConfig
     * @return ImportJob
     * @throws \yii\db\Exception
     */
    public static function run(ImportConfig $importConfig): ImportJob
    {
        try {
            $transformerService = Yii::$container->get(TransformerService::class);
            $query = ExportService::getExportQuery($importConfig, 1000);
            $totalCount = 0;
            $successRows = 0;
            $filename = 'export_' . date('Ymd_His') . '.csv';
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            $f = fopen($path, 'w');
            $headers = [];
            $configTransformerColumns = [];
            /** @var ImportConfigColumn $column */
            foreach ($importConfig->getImportColumns()->each() as $column) {
                $headers[] = $column->target;
                if (empty($column->transformer) === false) {
                    $configTransformerColumns[$column->source] = $column;
                }
            }

            fputcsv($f, $headers, ';');
            try {
                if ($query instanceof Query) {
                    $totalCount = $query->count();
                    foreach ($query->each() as $row) {
                        $successRows = static::writeRow($f,
                            $configTransformerColumns,
                            $row,
                            $transformerService,
                            $successRows
                        );
                    }
                } elseif ($query instanceof SqlIterator) {
                    $totalCount = $query->getCount();
                    foreach ($query->getIterator() as $rows) {
                        foreach ($rows as $row) {
                            $successRows = static::writeRow($f,
                                $configTransformerColumns,
                                $row,
                                $transformerService,
                                $successRows
                            );
                        }
                    }
                }
                $status = ImportJob::STATUS_SUCCESS;
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $status = ImportJob::STATUS_FAILED;
            }
            $importJob = ExportService::prepareImportJob($importConfig,  $totalCount);
            $importJob->successRows = $successRows;
            $importJob->status = $status;
            fclose($f);
            $importJob->filePath = $path;
            $importJob->save();
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    protected static function writeRow($f, array $configTransformerColumns, $row, $transformerService, $successRows) : int
    {
        try {
            $line = $row;
            foreach ($configTransformerColumns as $source => $column) {
                if (isset($row[$source]) === true
                    && isset($column->transformer['name']) === true
                    && $transformerService instanceof TransformerService) {
                    $line[$source] = $transformerService->apply(
                        $column->transformer['name'],
                        $row[$source],
                        $column->transformerOptions
                    );
                }
            }
            fputcsv($f, $line, ';');
            $successRows += 1;
            return $successRows;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
