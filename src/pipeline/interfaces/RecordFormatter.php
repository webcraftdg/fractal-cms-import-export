<?php
/**
 * RecordFormatter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\interfaces
 */
namespace fractalCms\importExport\pipeline\interfaces;

use fractalCms\importExport\models\ImportConfig;

interface RecordFormatter
{
    /**
     * format
     *
     * @param  array        $mappedRow
     * @param  ImportConfig $config
     *
     * @return array
     */
    public function format(array $mappedRow, ImportConfig $config): array;
}