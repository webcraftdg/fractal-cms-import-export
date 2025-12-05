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
use fractalCms\importExport\services\Export as ExportService;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\models\ImportJob;
use Yii;

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

            $query = $importConfig->getImportExportQuery();
            $importJob = ExportService::prepareImportJob($importConfig, $query->count());

            $filename = 'export_' . date('Ymd_His') . '.csv';
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            $f = fopen($path, 'w');

            $headers = array_column($importConfig->tmpColumns, 'target');
            fputcsv($f, $headers, ';');
            while ($row = $query->read()) {
                $line = $row;
                fputcsv($f, $line, ';');
                $importJob->successRows += 1;
            }
            /*
            foreach ($query->read() as $row) {
                $line = [];
                foreach ($importConfig->tmpColumns as $column) {
                    $line[] = $row[$column['source']] ?? '';
                }
            }*/

            fclose($f);
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
