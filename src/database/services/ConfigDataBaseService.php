<?php
/**
 * ConfigDataBaseService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\database\services
 */
namespace fractalCms\importExport\database\services;

use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\configuration\factories\ImportConfigColumn;
use fractalCms\importExport\configuration\services\ConfigColumnsGeneratorService;
use Yii;

class ConfigDataBaseService
{
    
    
    /**
     * constructor
     *
     * @param  DbView                                                                        $dbView
     * @param  \fractalCms\importExport\configuration\services\ConfigColumnsGeneratorService $configColumnGenerator
     */
    public function __construct(
        private DbView $dbView,
        private ConfigColumnsGeneratorService $configColumnGenerator
    ) {
    }

    /**
     * generate from Config
     *
     * @param  \fractalCms\importExport\models\ImportConfig $config
     *
     * @return bool
     */
    public function generateDbView(ImportConfig $config): int
    {
        try {
            $rows = -1;
            if (empty($config->sql) === false && $config->exportTarget === ImportConfig::TARGET_VIEW) {
                $name = $config->getContextName();
                $rows = $this->dbView->create($name, $config->sql);
            }
            return $rows;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * generate columns
     *
     * @param  \fractalCms\importExport\models\ImportConfig $config
     *
     * @return array
     */
    public function generateColumns(ImportConfig $config) : array
    {
        try {
            return $this->configColumnGenerator->generateForConfig($config);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
        }
    }
}