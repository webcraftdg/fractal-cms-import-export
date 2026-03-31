<?php
/**
 * ImportExportExecutionService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\services
 */

namespace fractalCms\importExport\pipeline\services;

use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use Yii;

class ImportExportExecutionService
{

    /**
     * Undocumented function
     *
     * @param  ImportConfig $config
     *
     * @return ImportJob
     */
    public function executeImport(ImportConfig $config, string $finalPathFile): ImportJob
    {
        try {
            $importService = Yii::$container->get(ImportService::class);
            $importJob = $importService->run($config, $finalPathFile, true);
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * execute Export
     *
     * @param  ImportConfig $config
     *
     * @return ImportJob
     */
    public function executeExport(ImportConfig $config): ImportJob
    {
        try {
            $exportService = Yii::$container->get(ExportService::class);
            $importJob = $exportService->run($config);
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
