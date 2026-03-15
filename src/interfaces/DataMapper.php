<?php
/**
 * DataMapper.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\models\ImportConfig;

interface DataMapper {
    
    /**
     * map
     *
     * @param  array        $rawRecord
     * @param  ImportConfig $config
     * @param  int|string   $rowNumber
     *
     * @return array
     */
    public function map(array $rawRecord, ImportConfig $config, int|string $rowNumber): array;
}