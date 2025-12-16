<?php
/**
 * Export.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */

namespace fractalCms\importExport\services;

use fractalCms\importExport\db\SqlIterator;
use fractalCms\importExport\interfaces\Export as ExportInterfaces;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\exports\ExportCsv;
use fractalCms\importExport\services\exports\ExportXlsx;
use Yii;
use Exception;
use yii\db\Query;
use yii\web\Application;

class Export implements ExportInterfaces
{

    /**
     * Run export
     *
     * @param ImportConfig $importConfig
     * @return ImportJob
     * @throws \yii\db\Exception
     */
    public static function run(ImportConfig $importConfig): ImportJob
    {
        try {
            switch ($importConfig->exportFormat) {
                case ImportConfig::FORMAT_EXCEL_X:
                case ImportConfig::FORMAT_EXCEL:
                    $importJob = ExportXlsx::run($importConfig);
                    break;
                default:
                    $importJob = ExportCsv::run($importConfig);
            }
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ImportConfig $importConfig
     * @param int $rowsCount
     * @return ImportJob
     * @throws Exception
     */
    public static function prepareImportJob(ImportConfig $importConfig, int $rowsCount = null) : ImportJob
    {
        try {
            $importJob = new ImportJob(['scenario' => ImportJob::SCENARIO_CREATE]);
            $importJob->importConfigId = $importConfig->id;
            $importJob->userId = (Yii::$app instanceof Application) ? Yii::$app->user->identity->getId() : null;
            $importJob->type = ImportJob::TYPE_EXPORT;
            $importJob->status = ImportJob::STATUS_RUNNING;
            $importJob->totalRows = $rowsCount;
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ImportConfig $importConfig
     * @param $batchSize
     * @return SqlIterator|Query
     * @throws \yii\db\Exception
     */
    public static function getExportQuery(ImportConfig $importConfig, $batchSize = 1000) : SqlIterator | Query
    {
        try {
            $query = $importConfig->getImportExportQueryDb();
            if (empty($importConfig->sql) === false && $importConfig->exportTarget === ImportConfig::TARGET_SQL) {
                $query = $importConfig->getImportExportQueryIterator($batchSize);
            }
            return $query;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

}
