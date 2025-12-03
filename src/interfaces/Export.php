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

interface Export
{

    /**
     * @param ImportConfig $importConfig
     * @return string | null
     */
    public static function run(ImportConfig $importConfig) : string | null;
}
