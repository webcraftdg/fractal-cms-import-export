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

use fractalCms\importExport\contexts\Export as ExportContext;
use fractalCms\importExport\interfaces\ExportDataProvider;
use fractalCms\importExport\interfaces\Export;
use fractalCms\importExport\interfaces\RowExportTransformer as RowTransformerInterface;
use fractalCms\importExport\services\Export as ExportService;
use fractalCms\importExport\services\ColumnTransformer as ColumnTransformerService;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\writers\CsvWriter;
use fractalCms\importExport\transformers\ColumnsTransform;
use Exception;
use Yii;
use yii\helpers\FileHelper;

class ExportCsv extends ColumnsTransform implements Export
{

    /**
     * @param ImportConfig $importConfig
     * @param ExportDataProvider $provider
     * @param array $params
     * @return ImportJob
     * @throws Exception
     */
    public static function run(ImportConfig $importConfig, ExportDataProvider $provider, array $params = []): ImportJob
    {
        try {
            $transformerService = Yii::$container->get(ColumnTransformerService::class);
            $rowTransformer = $importConfig->getRowTransformer();
            $totalCount = 0;
            $filename = 'export_' . date('Ymd_His') . '.csv';
            FileHelper::createDirectory(Yii::getAlias('@runtime'));
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            $f = fopen($path, 'w');

            $writer = new CsvWriter($f);
            $baseExportContext = new ExportContext(
                config: $importConfig,
                dryRun: false,
                rowNumber: -1,
                writer: $writer,
                params: $params
            );

            $headers = [];
            $configColumns = [];
            /** @var ImportConfigColumn $column */
            foreach ($importConfig->getImportColumns()->each() as $column) {
                $headers[] = $column->target;
                $configColumns[] = $column;
            }
            $baseExportContext->writeRow('csv', $headers);
            $importJob = ExportService::prepareImportJob($importConfig,  $totalCount);
            try {
                $totalCount = $provider->count();
                foreach ($provider->getIterator() as $rowIndex => $row) {
                    //ColumnTransformer
                    $row = static::prepareRow(
                        transformerService: $transformerService,
                        configColumns: $configColumns,
                        row: $row
                    );
                    $baseExportContext = $baseExportContext->withRowNumber($rowIndex);
                    try {
                        if ($rowTransformer instanceof RowTransformerInterface) {

                            $result = $rowTransformer->transformRow(
                                $row,
                                $baseExportContext
                            );
                            if ($result->handled === true) {
                                $importJob->successRows++;
                                continue;
                            }
                        }
                        $row = $result->attributes ?? $row;
                        $baseExportContext->writeRow('csv', $row, $rowIndex);
                        $importJob->successRows++;
                    } catch (Exception $e) {
                        $importJob->errorRows++;
                        if ($importConfig->stopOnError) {
                            break;
                        }
                        continue;
                    }
                }
                $status = ImportJob::STATUS_SUCCESS;
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $status = ImportJob::STATUS_FAILED;
            }
            $importJob->totalRows = $totalCount;
            $importJob->status = $status;
            fclose($f);
            $importJob->filePath = '@runtime/'.$filename;
            $importJob->save();
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
