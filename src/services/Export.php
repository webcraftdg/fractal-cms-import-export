<?php
/**
 * Export.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */

namespace fractalCms\importExport\services;

use fractalCms\importExport\interfaces\ImportExport;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\exports\ExportCsv;
use fractalCms\importExport\services\exports\ExportXlsx;
use Yii;
use Exception;

class Export implements ImportExport
{

    /**
     * Run export
     *
     * @param ImportConfig $importConfig
     * @return string
     * @throws \yii\db\Exception
     */
    public static function run(ImportConfig $importConfig): string
    {
        try {
            switch ($importConfig->exportFormat) {
                case ImportConfig::FORMAT_EXCEL_X:
                case ImportConfig::FORMAT_EXCEL:
                    $path = ExportXlsx::run($importConfig);
                    break;
                default:
                    $path = ExportCsv::run($importConfig);
            }
            return $path;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
