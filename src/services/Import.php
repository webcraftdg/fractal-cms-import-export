<?php
/**
 * Import.php
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

class Import implements ImportInterface
{

    /**
     * Run import
     *
     * @param ImportConfig $importConfig
     * @param string $filePath
     * @param bool $isTest
     * @param $params
     * @return ImportJob
     * @throws NotSupportedException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function run(ImportConfig $importConfig, string $filePath, bool $isTest = false, $params = []): ImportJob
    {
        try {
            return ImportXlsx::run($importConfig, $filePath, $isTest, $params);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
