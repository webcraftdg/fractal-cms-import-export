<?php
/**
 * Import.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\services
 */

namespace fractalCms\importExport\pipeline\services;

use fractalCms\importExport\pipeline\interfaces\Import as ImportInterface;
use fractalCms\importExport\pipeline\imports\factories\ImportInserter;
use fractalCms\importExport\pipeline\imports\factories\ImportReader;
use fractalCms\importExport\pipeline\imports\services\ImportProcessorService;
use fractalCms\importExport\pipeline\mappers\Column;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use Yii;

class ImportService implements ImportInterface
{


    public function __construct(
        private ImportProcessorService $importProcessorService,
        private ImportReader $readerFactory,
        private ImportInserter $inserterFactory,
        private Column $mapperColumn
    )
    {
    }
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
            $reader = $this->readerFactory->create($config->fileFormat);
            $inserter = $this->inserterFactory->create($config->sourceType);
            return $this->importProcessorService->run($reader, $this->mapperColumn, $inserter, $config, $filePath, $isTest, $params);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
