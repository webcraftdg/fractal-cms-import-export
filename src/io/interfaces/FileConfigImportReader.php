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

interface FileConfigImportReader 
{

    /**
     * Open
     *
     * @return void
     */    
    public function open(): bool;
    
    /**
     * Hydrate
     *
     * @return array
     */
    public function read(): array;

    /**
     * close
     *
     * @return void
     */
    public function delete(): void;
}