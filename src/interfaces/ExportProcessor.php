<?php
/**
 * ExportProcessor.php
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

interface ExportProcessor
{
    /**
     * run
     *
     * @param  CountableDataReader   $reader
     * @param  DataMapper   $mapper
     * @param  ExportWriter $writer
     * @param  ImportConfig $config
     * @param  string       $filePath
     * @param  bool         $isTest
     * @param  array        $params
     *
     * @return ImportJob
     */
    public function run( 
        CountableDataReader $reader,
        DataMapper $mapper,
        WriterInterface $writer,
        ImportConfig $config,
        string $filePath,
        bool $isTest = false,
        array $params = []
    ): ImportJob;
}
