<?php
/**
 * Parameter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\interfaces
 */
namespace fractalCms\importExport\pipeline\interfaces;


interface Parameter
{

    /**
     * @return array
     */
    public function getActiveModelTableNames(): array;

    /**
     * @param array $tables
     * @param string $tableName
     * @return string|false
     */
    public function findTable(array $tables, string $tableName) : string | false;
}
