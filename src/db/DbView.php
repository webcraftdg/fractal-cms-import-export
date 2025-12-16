<?php
/**
 * DbView.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\db;

use Exception;
use fractalCms\importExport\models\ColumnModel;
use fractalCms\importExport\services\Parameter;
use fractalCms\importExport\interfaces\DbView as DbViewInterface;
use Yii;
use yii\base\Component;
use yii\db\ColumnSchema;

class DbView extends Component implements DbViewInterface
{

    protected Parameter $parameter;
    /**
     * @inheritDoc
     */
    public function __construct(Parameter $parameter, $config = [])
    {
        parent::__construct($config);
        $this->parameter = $parameter;
    }

    /**
     * @param string $name
     * @param string $sql
     * @return int
     * @throws Exception
     */
    public function create(string $name, string $sql): int
    {
        try {
            $db = Yii::$app->db;
            if ($this->exists($name) === true) {
                $this->drop($name);
            }
            $sql = trim($sql, ';');
            $command = 'CREATE VIEW '.$name.' AS '.$sql;
            $rows =  $db->createCommand($command)->execute();
            $db->getSchema()->refresh();
            return $rows;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $name
     * @param string $sql
     * @return int
     * @throws Exception
     */
    public function replace(string $name, string $sql): int
    {
        try {
            return $this->create($name, $sql);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $name
     * @return int
     * @throws Exception
     */
    public function drop(string $name): int
    {
        try {
            $db = Yii::$app->db;
            return $db->createCommand('DROP VIEW '.$name)->execute();
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $name
     * @return bool
     * @throws Exception
     */
    public function exists(string $name): bool
    {
        try {
            $dbTables = Yii::$app->db->schema->tableNames;
            return in_array($name, $dbTables);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $tableName
     * @return array
     * @throws Exception
     */
    public function getTableColumns(string $tableName): array
    {
        try {
            $columns = [];
            $dbTables = Yii::$app->db->getSchema()->tableNames;
            $hasTable = in_array($tableName, $dbTables);
            if ($this->exists($tableName) === true && $hasTable === true) {
                $columns = array_map(function(ColumnSchema $columnSchema) {
                    $newColumn = new ColumnModel(['scenario' => ColumnModel::SCENARIO_CREATE]);
                    $newColumn->source = $columnSchema->name;
                    $newColumn->target = ucfirst($columnSchema->name);
                    $newColumn->format = $columnSchema->type;
                    return $newColumn;
                }, Yii::$app->db->getSchema()->getTableSchema($tableName)->columns);
            }
            return $columns;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function columnExists(string $tableName, string $columnName): bool
    {
        try {
            $exists = false;
            $dbTables = Yii::$app->db->getSchema()->tableNames;
            $hasTable = in_array($tableName, $dbTables);
            if ($this->exists($tableName) === true && $hasTable === true) {
                $columns = Yii::$app->db->getSchema()->getTableSchema($tableName)->columns;
                $exists = in_array($columnName, array_keys($columns));
            }
            return $exists;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
