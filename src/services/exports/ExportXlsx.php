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

use fractalCms\importExport\db\SqlIterator;
use fractalCms\importExport\interfaces\Export;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\Export as ExportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;
use yii\db\Query;

class ExportXlsx implements Export
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
            $query = ExportService::getExportQuery($importConfig, 1000);
            $totalCount = 0;
            $successRows = 0;
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $colIndex = 1;
            $row = 1;
            /** @var ImportConfigColumn $col */
            foreach ($importConfig->getImportColumns()->each() as $column) {
                $sheet->getColumnDimensionByColumn($colIndex)->setWidth(strlen($column->target));
                $sheet->setCellValue([$colIndex, $row], $column->target);
                $colIndex++;
            }

            // Data
            $rowIndex = 2;
            try {
                if ($query instanceof Query) {
                    $totalCount = $query->count();
                    foreach ($query->each() as $row) {
                        $colIndex = 1;
                        /** @var ImportConfigColumn $column */
                        foreach ($importConfig->getImportColumns()->each()  as $column) {
                            $sheet->setCellValue(
                                [$colIndex, $rowIndex],
                                $row[$column->source] ?? ''
                            );
                            $colIndex++;
                        }
                        $rowIndex++;
                        $successRows += 1;
                    }
                } elseif ($query instanceof SqlIterator) {
                    $totalCount = $query->getCount();
                    foreach ($query->getIterator() as $rows) {
                        foreach ($rows as $row) {
                            $colIndex = 1;
                            /** @var ImportConfigColumn $column */
                            foreach ($importConfig->getImportColumns()->each()  as $column) {
                                $sheet->setCellValue(
                                    [$colIndex, $rowIndex],
                                    $row[$column->source] ?? ''
                                );
                                $colIndex++;
                            }
                        }
                        $rowIndex++;
                        $successRows += 1;
                    }
                }
                $importJob = ExportService::prepareImportJob($importConfig,  $totalCount);
                $status = ImportJob::STATUS_SUCCESS;
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $status = ImportJob::STATUS_FAILED;
            }
            $filename = 'export_' . date('Ymd_His') . '.xlsx';
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            (new Xlsx($spreadsheet))->save($path);
            $importJob->filePath = $path;
            $importJob->status = $status;
            $importJob->successRows = $successRows;
            $importJob->save();
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
