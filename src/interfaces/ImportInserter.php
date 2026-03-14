<?php
/**
 * ImportInserter.php
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

interface ImportInserter {

    /**
     * insert
     *
     * @param  ImportConfig $config
     * @param  array        $attributes
     * @param  int|string   $rowNumber
     *
     * @return InsertResult
     */
    public function insert(ImportConfig $config, array $attributes, int|string $rowNumber): InsertResult;
}