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

use fractalCms\importExport\interfaces\ExportDataProvider;
use fractalCms\importExport\interfaces\Export as ExportInterface;
use fractalCms\importExport\interfaces\RowExportTransformer as RowTransformerInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\Export as ExportService;
use fractalCms\importExport\services\ColumnTransformer as ColumnTransformerService;
use fractalCms\importExport\contexts\Export as ExportContext;
use fractalCms\importExport\writers\XlsxWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;
use Yii;
use yii\helpers\FileHelper;

class ExportXlsx implements ExportInterface
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
            $spreadsheet = new Spreadsheet();
            $writer = new XlsxWriter($spreadsheet);
            $baseExportContext = new ExportContext(
                config: $importConfig,
                dryRun: false,
                rowNumber: -1,
                writer: $writer,
                params: $params
            );

            $sheet = $spreadsheet->getActiveSheet();
            $configColumns = [];

            // Headers
            $colIndex = 1;
            $row = 1;
            /** @var ImportConfigColumn $col */
            foreach ($importConfig->getImportColumns()->each() as $column) {
                $sheet->getColumnDimensionByColumn($colIndex)->setWidth(strlen($column->target));
                $sheet->setCellValue([$colIndex, $row], $column->target);
                $configColumns[$column->source] = $column;
                $colIndex++;
            }
            $importJob = ExportService::prepareImportJob($importConfig);

            // Data
            $rowIndex = 2;
            try {
                $totalCount = $provider->count();
                foreach ($provider->getIterator() as $row) {
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
                            if( $result->handled === true) {
                                $importJob->successRows++;
                                $rowIndex++;
                                continue;
                            }
                        }
                        $row = $result->attributes ?? $row;
                        $baseExportContext->writeRow($sheet->getTitle(), $row, $rowIndex);
                        $importJob->successRows++;
                        $rowIndex++;
                    } catch (Exception $e) {
                        $importJob->errorRows++;
                        $rowIndex++;
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
            $filename = 'export_' . date('Ymd_His') . '.xlsx';
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            FileHelper::createDirectory(Yii::getAlias('@runtime'));
            $baseExportContext->finalize($path);
            $importJob->filePath = '@runtime/'.$filename;
            $importJob->status = $status;
            $importJob->save();
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ColumnTransformerService $transformerService
     * @param array $configColumns
     * @param array $row
     * @return array
     * @throws Exception
     */
    protected static function prepareRow(ColumnTransformerService $transformerService, array $configColumns, array $row) : array
    {
        try {
            /** @var ImportConfigColumn $column */
            foreach ($configColumns  as $column) {
                $value = $row[$column->source] ?? null;
                if (
                    $value !== null
                    && $transformerService instanceof ColumnTransformerService
                    && $column->transformer !== null
                    && empty($column->transformer['name']) === false
                ) {
                    $value = $transformerService->apply(
                        $column->transformer['name'],
                        $value,
                        $column->transformerOptions
                    );
                }
                $row[$column->source]  = $value;
            }
            return $row;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
