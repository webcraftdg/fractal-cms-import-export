<?php
/**
 * ExportProcessor.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\interfaces
 */
namespace fractalCms\importExport\pipeline\interfaces;

use fractalCms\importExport\runtime\contexts\Export as ExportContext;
use fractalCms\importExport\io\interfaces\CountableDataReader;
use fractalCms\importExport\models\ImportJob;

interface ExportProcessor
{
    /**
     * run
     *
     * @param  CountableDataReader   $reader
     * @param  DataMapper   $mapper
     * @param  ExportContext $exportcontext
     * @param  string       $filePath
     * @param  bool         $isTest
     * @param  array        $params
     *
     * @return ImportJob
     */
    public function run( 
        CountableDataReader $reader,
        DataMapper $mapper,
        ExportContext $exportcontext,
        string $filePath,
        bool $isTest = false,
        array $params = []
    ): ImportJob;
}
