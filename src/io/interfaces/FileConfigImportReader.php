<?php
/**
 * FileConfigImportReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\io\interfaces
 */
namespace fractalCms\importExport\io\interfaces;

use fractalCms\importExport\models\ImportConfig;

interface FileConfigImportReader 
{

    /**
     * Open
     *
     * @return void
     */    
    public function open(): void;
    
    /**
     * Hydrate
     *
     * @return ImportConfig
     */
    public function hydrate(): ImportConfig;

    /**
     * close
     *
     * @return void
     */
    public function delete(): void;
}