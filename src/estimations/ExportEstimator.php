<?php
/**
 * ExportEstimator.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\estimations;

use fractalCms\importExport\models\ImportConfig;
use Yii;
use Exception;
use fractalCms\importExport\interfaces\CountableDataReader;

class ExportEstimator
{

    /**
     * @param ImportConfig $config
     * @return int
     * @throws Exception
     */
    public static function estimateRows(ImportConfig $config): int
    {
        try {
            $dataReader = $config->getDataReader();
            $count = -1;
            if ($dataReader instanceof CountableDataReader) {
                $count = $dataReader->count();
            }
            return $count;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ImportConfig $config
     * @return int
     * @throws Exception
     */
    public static function estimateColumns(ImportConfig $config): int
    {
        try {
            return $config->getImportColumns()->count();
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param int $rows
     * @param int $columns
     * @param int $cellSize
     * @return int
     */
    public static function estimateSizeMb(int $rows, int $columns, int $cellSize = 20): int
    {
        $bytes = $rows * $columns * $cellSize;
        return (int) round($bytes / 1024 / 1024);
    }
}
