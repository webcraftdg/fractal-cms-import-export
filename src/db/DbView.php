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

use fractalCms\importExport\interfaces\DbView as DbViewInterface;
use yii\base\Component;
use Exception;
use Yii;

class DbView extends Component implements DbViewInterface
{
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
}
