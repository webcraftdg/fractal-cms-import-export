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

use fractalCms\importExport\interfaces\Export;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\Export as ExportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

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
            $query = $importConfig->getImportExportQuery();
            $importJob = ExportService::prepareImportJob($importConfig, $query->count());

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
            while ($row = $query->read()) {
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
                $importJob->successRows += 1;
            }

            $filename = 'export_' . date('Ymd_His') . '.xlsx';
            $path = Yii::getAlias('@runtime') . '/' . $filename;

            (new Xlsx($spreadsheet))->save($path);
            $importJob->filePath = $path;
            $importJob->status = ImportJob::STATUS_SUCCESS;
            $importJob->save();
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
