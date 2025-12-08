<?php
/**
 * ImportExport.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */

namespace fractalCms\importExport\services;

use fractalCms\importExport\interfaces\Import as ImportInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\imports\ImportXlsx;
use Yii;
use Exception;
use yii\base\NotSupportedException;

class ImportExport implements ImportInterface
{

    /**
     * Run import
     *
     * @param ImportConfig $importConfig
     * @param string $filePath
     * @param bool $isTest
     * @return ImportJob
     * @throws \yii\db\Exception
     */
    public static function run(ImportConfig $importConfig, string $filePath, bool $isTest = false): ImportJob
    {
        try {
            switch ($importConfig->exportFormat) {
                case ImportConfig::FORMAT_EXCEL_X:
                case ImportConfig::FORMAT_EXCEL:
                case ImportConfig::FORMAT_CSV:
                    $importJob = ImportXlsx::run($importConfig, $filePath, $isTest);
                    break;
                default:
                    throw new NotSupportedException('Import de ce type de fichier non supporté');
            }
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
