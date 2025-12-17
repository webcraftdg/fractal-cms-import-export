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

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;

interface Import
{

    /**
     * @param ImportConfig $importConfig
     * @param string $filePath
     * @param bool $isTest
     * @param array $params
     * @return ImportJob
     */
    public static function run(ImportConfig $importConfig, string $filePath, bool $isTest = false, array $params = []) : ImportJob;
}
