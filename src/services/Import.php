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
use fractalCms\importExport\services\imports\factories\ImportInserter;
use fractalCms\importExport\services\imports\factories\ImportReader;
use fractalCms\importExport\services\imports\mappers\ConfigImport;
use yii\base\NotSupportedException;
use Exception;
use fractalCms\importExport\services\imports\ImportProcessor;
use Yii;

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
            $readerFactory = new ImportReader();
            $reader = $readerFactory->create($importConfig->fileFormat);

            $inserterFactory = new ImportInserter();
            $inserter = $inserterFactory->create($importConfig->sourceType);
            $mapper = new ConfigImport();
            $processor = new ImportProcessor();
            return $processor->run($reader, $mapper, $inserter, $importConfig, $filePath, $isTest, $params);

        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
