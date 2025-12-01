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
namespace fractalCms\importExport\services;

use yii\base\Component;
use Exception;
use Yii;

class DbView extends Component implements \fractalCms\importExport\interfaces\DbView
{

    /**
     * @param string $name
     * @param string $sql
     * @return bool
     * @throws Exception
     */
    public function create(string $name, string $sql): bool
    {
        try {
            return true;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $name
     * @param string $sql
     * @return bool
     * @throws Exception
     */
    public function replace(string $name, string $sql): bool
    {
        try {
            return true;
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
    public function drop(string $name): bool
    {
        try {
            return true;
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
            return true;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $name
     * @return array
     * @throws Exception
     */
    public function getColumns(string $name): array
    {
        try {
            return [];
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
