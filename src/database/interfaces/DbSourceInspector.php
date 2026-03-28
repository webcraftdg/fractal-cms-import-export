<?php
/**
 * DbSourceInspector.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\database\interfaces
 */
namespace fractalCms\importExport\database\interfaces;

use fractalCms\importExport\models\ImportConfig;

interface DbSourceInspector
{
    /**
     * get Table columns
     *
     * @param  string $tableName
     *
     * @return array
     */
    public function getTableColumns(string $tableName): array;

    /**
     * GetViewColumns
     *
     * @param  string $viewName
     *
     * @return array
     */
    public function getViewColumns(string $viewName): array;

    /**
     * Get SQl columns
     *
     * @param  string $sql
     *
     * @return array
     */
    public function getSqlColumns(string $sql): array;

    /**
     * get Available Columns For Mapping
     *
     * @param  ImportConfig $config
     *
     * @return array
     */
    public function getAvailableColumnsForMapping(ImportConfig $config): array;

    /**
     * column exists for config
     *
     * @param  ImportConfig $config
     * @param  string       $columnName
     *
     * @return bool
     */
    public function columnExistsForConfig(ImportConfig $config, string $columnName): bool;
}
