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
use fractalCms\importExport\services\imports\ImportProcessorService;
use fractalCms\importExport\mappers\Column;
use yii\base\NotSupportedException;
use Exception;
use Yii;

class ImportService implements ImportInterface
{

    /**
     * run
     *
     * @param  ImportConfig $config
     * @param  string       $filePath
     * @param  bool         $isTest
     * @param  array        $params
     *
     * @return ImportJob
     */
    public function run(ImportConfig $config, string $filePath, bool $isTest = false, $params = []): ImportJob
    {
        try {
            $readerFactory = new ImportReader();
            $reader = $readerFactory->create($config->fileFormat);

            $inserterFactory = new ImportInserter();
            $inserter = $inserterFactory->create($config->sourceType);
            $mapper = new Column();
            $processor = new ImportProcessorService();
            return $processor->run($reader, $mapper, $inserter, $config, $filePath, $isTest, $params);

        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
