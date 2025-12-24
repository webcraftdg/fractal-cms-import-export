<?php
/**
 * DbView.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

interface DbView
{

    /**
     * @param string $name
     * @param string $sql
     * @return int
     */
    public function create(string $name, string $sql) : int;

    /**
     * @param string $name
     * @param string $sql
     * @return bool
     */
    public function replace(string $name, string $sql) : int;

    /**
     * @param string $name
     * @return bool
     */
    public function drop(string $name) : int;

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name) : bool;

    /**
     * @param string $tableTableName
     * @return array
     */
    public function getTableColumns(string $tableTableName) : array;

    /**
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    public function columnExists(string $tableName, string $columnName) : bool;
}
