<?php
/**
 * Import.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\exceptions\InsertResult;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;

interface Import
{

    /**
     * @param ImportConfig $config
     * @param string $filePath
     * @param bool $isTest
     * @param array $params
     * @return ImportJob
     */
    public function run(ImportConfig $config, string $filePath, bool $isTest = false, array $params = []) : ImportJob;
}
