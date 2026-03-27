<?php
/**
 * ConfigDataBaseService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services;

use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\db\DbView;
use fractalCms\importExport\db\SourceColumnsResolver;
use fractalCms\importExport\factories\ImportConfigColumn;
use Yii;

class ConfigDataBaseService
{
    
    private ConfigColumnsGeneratorService $configColumnGenerator;
    
    /**
     * constructor
     *
     * @param  \fractalCms\importExport\db\DbView                    $dbView
     * @param  \fractalCms\importExport\db\SourceColumnsResolver     $resolver
     * @param  \fractalCms\importExport\factories\ImportConfigColumn $factory
     */
    public function __construct(
        private DbView $dbView,
        private SourceColumnsResolver $resolver,
        private ImportConfigColumn $factory
    ) {
        $this->configColumnGenerator = new ConfigColumnsGeneratorService($resolver, $factory);
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