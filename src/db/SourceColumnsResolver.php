<?php
/**
 * SourceColumnsResolver.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\db;

use fractalCms\importExport\interfaces\DbSourceInspector;
use fractalCms\importExport\models\ImportConfig;
use yii\base\Component;
use yii\db\ColumnSchema;
use Exception;
use Override;
use Yii;

class SourceColumnsResolver extends Component implements DbSourceInspector
{

    /**
     * Get columns from config
     *
     * @param  ImportConfig $config
     *
     * @return array
     */
    public function getAvailableColumnsForMapping(ImportConfig $config): array
    {
        try {
            $columns = [];
            $contextName = $config->getContextName();
            if ($config->isImport() === true) {
                $columns =  $this->getTableColumns($contextName);
            }

            if ($config->isExport() === true) {
                if ($config->sourceType === ImportConfig::SOURCE_TYPE_TABLE) {
                    $columns = $this->getTableColumns($contextName);
                }

                if ($config->sourceType === ImportConfig::SOURCE_TYPE_SQL) {
                    if ($config->exportTarget === ImportConfig::TARGET_VIEW) {
                        $columns = $this->getViewColumns($contextName);
                    }

                    if ($config->exportTarget === ImportConfig::TARGET_SQL) {
                        $columns = $this->getSqlColumns($config->sql);
                    }
                }
            }
            return $columns;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * column Exist for config
     *
     * @param  ImportConfig $config
     * @param  string       $columnName
     *
     * @return bool
     */
    public function columnExistsForConfig(ImportConfig $config, string $columnName): bool
    {
        try {
            $exist = false;
            $columns = $this->getAvailableColumnsForMapping($config);
            foreach($columns as $column) {
                if ($column['name'] === $columnName) {
                    $exist = true;
                    break;
                }
            }
            return $exist;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * get Sql Columns
     *
     * @param  string $sql
     *
     * @return array
     */
    public function getSqlColumns(string $sql): array
    {
        try {
            $columns = [];
            
            $db = Yii::$app->db;
            $sql = trim($sql, ';');
            $sql .= ' LIMIT 1';
            $reader =  $db->createCommand($sql)->query();
            foreach($reader as $row) {
                foreach($row as $rowName => $value) {
                    $columns[] = [
                        'name' => $rowName,
                        'format' => null,
                    ];
                }
                break;
            }
            return $columns;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * get Table columns
     *
     * @param  string $tableName
     *
     * @return array
     */
    public function  getTableColumns(string $tableName): array
    {
         try {
            $columns = [];
            $dbTables = Yii::$app->db->getSchema()->tableNames;
            if (in_array($tableName, $dbTables) === true) {
                $columns = array_map(function(ColumnSchema $columnSchema) {
                    return [
                        'name' => $columnSchema->name,
                        'format' => $columnSchema->type,
                    ];
                }, Yii::$app->db->getSchema()->getTableSchema($tableName)->columns);
            }
            return $columns;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * get View Columns
     *
     * @param  string $viewName
     *
     * @return array
     */
    public function getViewColumns(string $viewName): array
    {
        return $this->getTableColumns($viewName);
    }
}
