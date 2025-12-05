<?php
/**
 * Export.php
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

interface Export
{

    /**
     * @param ImportConfig $importConfig
     * @return ImportJob
     */
    public static function run(ImportConfig $importConfig) : ImportJob;
}
