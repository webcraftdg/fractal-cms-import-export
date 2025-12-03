<?php
/**
 * Parameter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;


interface Parameter
{

    /**
     * @return array
     */
    public function getTables(): array;

    /**
     * @param array $tables
     * @param string $tableName
     * @return string|false
     */
    public function findTable(array $tables, string $tableName) : string | false;
}
