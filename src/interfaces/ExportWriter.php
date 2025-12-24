<?php
/**
 * ExportWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\writers\WriteTarget;

interface ExportWriter
{
    /**
     * @param WriteTarget $target
     * @param array $row
     * @return void
     */
    public function write(WriteTarget $target, array $row): void;
}
