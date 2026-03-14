<?php
/**
 * ImportProcessor.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\interfaces\ImportReader;
use fractalCms\importExport\interfaces\ImportInserter;

interface ImportProcessor
{
    
    /**
     * run
     *
     * @param  ImportReader   $reader
     * @param  ImportMapper   $mapper
     * @param  ImportInserter $inserter
     * @param  ImportConfig   $config
     * @param  string         $filePath
     * @param  bool           $isTest
     * @param  array          $params
     *
     * @return ImportJob
     */
    public function run( 
        ImportReader $reader,
        ImportMapper $mapper,
        ImportInserter $inserter,
        ImportConfig $config,
        string $filePath,
        bool $isTest = false,
        array $params = []
    ): ImportJob;
}