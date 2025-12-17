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

use fractalCms\importExport\interfaces\Export as ExportInterfaces;
use fractalCms\importExport\interfaces\ExportDataProvider as ExportDataProviderInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\exports\ExportCsv;
use fractalCms\importExport\services\exports\ExportXlsx;
use Yii;
use Exception;
use yii\web\Application;

class Export implements ExportInterfaces
{

    /**
     * @param ImportConfig $importConfig
     * @param array $params
     * @return ImportJob
     * @throws \yii\db\Exception
     */
    public static function run(ImportConfig $importConfig, array $params = []): ImportJob
    {
        try {
            switch ($importConfig->exportFormat) {
                case ImportConfig::FORMAT_EXCEL_X:
                case ImportConfig::FORMAT_EXCEL:
                    $importJob = ExportXlsx::run($importConfig, $params);
                    break;
                default:
                    $importJob = ExportCsv::run($importConfig, $params);
            }
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ImportConfig $importConfig
     * @param int|null $rowsCount
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
     * @param int $batchSize
     * @return ExportDataProviderInterface
     * @throws Exception
     */
    public static function getExportQueryProvider(ImportConfig $importConfig, int $batchSize = 1000) : ExportDataProviderInterface
    {
        try {
            return $importConfig->getImportExportQueryProvider(batchSize: $batchSize);;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

}
