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

use fractalCms\importExport\interfaces\ImportExport;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

class ExportXlsx implements ImportExport
{
    /**
     * Run export CSv
     * @param ImportConfig $importConfig
     * @return string|null
     * @throws \yii\db\Exception
     */
    public static function run(ImportConfig $importConfig): string|null
    {
        try {
            $rows = $importConfig->getQueryRows();
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $colIndex = 1;
            $row = 1;
            foreach ($importConfig->tmpColumns as $col) {
                $target = ($col['target']) ?? '';
                $sheet->getColumnDimensionByColumn($colIndex)->setWidth(strlen($target));
                $sheet->setCellValue([$colIndex, $row], $target);
                $colIndex++;
            }

            // Data
            $rowIndex = 2;
            foreach ($rows as $row) {
                $colIndex = 1;
                foreach ($importConfig->tmpColumns as $col) {
                    $sheet->setCellValue(
                        [$colIndex, $rowIndex],
                        $row[$col['source']] ?? ''
                    );
                    $colIndex++;
                }
                $rowIndex++;
            }

            $filename = 'export_' . date('Ymd_His') . '.xlsx';
            $path = Yii::getAlias('@runtime') . '/' . $filename;

            (new Xlsx($spreadsheet))->save($path);
            return $path;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
