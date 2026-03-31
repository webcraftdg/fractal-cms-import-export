<?php
/**
 * ConfigColumnsGeneratorService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\configuration\services
 */
namespace fractalCms\importExport\configuration\services;

use fractalCms\importExport\configuration\factories\ImportConfigColumn;
use fractalCms\importExport\database\services\SourceColumnsResolver;
use fractalCms\importExport\models\ImportConfig;

class ConfigColumnsGeneratorService
{
    
    /**
     * constructor
     *
     * @param  SourceColumnsResolver     $resolver
     * @param  ImportConfigColumn $factory
     */
    public function __construct(
        private SourceColumnsResolver $resolver,
        private ImportConfigColumn $factory
    ) {
    }

    /**
     * generate for config
     *
     * @param  \fractalCms\importExport\models\ImportConfig $config
     *
     * @return array
     */
    public function generateForConfig(ImportConfig $config): array
    {
        $columns = $this->resolver->getAvailableColumnsForMapping($config);

        return $this->factory->createFromSourceColumns($columns);
    }
}