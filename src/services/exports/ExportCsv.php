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

use fractalCms\importExport\interfaces\Export;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use Yii;

class ExportCsv implements Export
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
            $filename = 'export_' . date('Ymd_His') . '.csv';
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            $f = fopen($path, 'w');

            $headers = array_column($importConfig->tmpColumns, 'target');
            fputcsv($f, $headers, ';');

            foreach ($rows as $row) {
                $line = [];
                foreach ($importConfig->tmpColumns as $column) {
                    $line[] = $row[$column['source']] ?? '';
                }
                fputcsv($f, $line, ';');
            }

            fclose($f);
            return $path;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
