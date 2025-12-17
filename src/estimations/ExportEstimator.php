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

use fractalCms\importExport\providers\SqlExportData as SqlExportDataProvider;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\Export;
use Yii;
use Exception;
use yii\db\Query;

class ExportEstimator
{

    /**
     * @param ImportConfig $config
     * @return int
     * @throws \yii\db\Exception
     */
    public static function estimateRows(ImportConfig $config): int
    {
        try {
            $query = Export::getExportQueryProvider($config);
            $count = 0;
            if(($query instanceof Query) || ($query instanceof SqlExportDataProvider)) {
                $count = $query->count();
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
