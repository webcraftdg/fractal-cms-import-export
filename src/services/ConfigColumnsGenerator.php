<?php
/**
 * ConfigColumnsGenerator.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services;

use fractalCms\importExport\db\SourceColumnsResolver;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\factories\ImportConfigColumn;

class ConfigColumnsGenerator
{
    
    /**
     * constructor
     *
     * @param  \fractalCms\importExport\db\SourceColumnsResolver     $resolver
     * @param  \fractalCms\importExport\factories\ImportConfigColumn $factory
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