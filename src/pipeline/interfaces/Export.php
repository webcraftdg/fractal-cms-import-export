<?php
/**
 * Export.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\interfaces
 */
namespace fractalCms\importExport\pipeline\interfaces;

use fractalCms\importExport\io\interfaces\DataReader;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;

interface Export
{

    /**
     * run
     *
     * @param  ImportConfig $config
     * @param  int          $batchSize
     * @param  array        $params
     *
     * @return ImportJob
     */
    public function run(ImportConfig $config, int $batchSize = 1000, array $params = []): ImportJob;

    /**
     * run with data reader
     *
     * @param  ImportConfig $config
     * @param  DataReader   $dataReader
     * @param  array        $params
     *
     * @return ImportJob
     */
    public function runWithDataReader(ImportConfig $config, DataReader $dataReader, array $params = []) : ImportJob;


}