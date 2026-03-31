<?php
/**
 * ConfigManagementService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\configuration\services
 */

namespace fractalCms\importExport\configuration\services;

use fractalCms\importExport\pipeline\services\ActiveRecordParameterService;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\exceptions\ImportError;
use yii\web\UploadedFile;
use Exception;
use Yii;

class ConfigManagementService
{


    /**
     * process
     *
     * @param  \fractalCms\importExport\models\ImportConfig $config
     *
     * @return \fractalCms\importExport\models\ImportConfig
     */
    public function process(ImportConfig $config) : ImportConfig
    {
        try {
            if ($config->importFile instanceof UploadedFile) {
                $finalPathFile = $config->generateImportfileTarget();
                $config->importFile->saveAs($finalPathFile);
                $config->scenario = ImportConfig::SCENARIO_CREATE;
                /**@var ConfigFileImportService $ConfigFileImportService */
                $ConfigFileImportService = new ConfigFileImportService($config, $finalPathFile);
                $config = $ConfigFileImportService->run();
                if (Yii::$container->has(ActiveRecordParameterService::class) === true) {
                    $activeRecordParameterService = Yii::$container->get(ActiveRecordParameterService::class);
                    $config->table = $activeRecordParameterService->parseTable($config->table);
                }
                $transaction = Yii::$app->db->beginTransaction();
                if ($config->hasErrors() === false && $config->validate() === true) {
                    $config->save();
                    $config->refresh();
                    $errorsColumns = null;
                    if (Yii::$container->has(ConfigColumnsPersistenceService::class) === true) {
                        $columnPersistentService = Yii::$container->get(ConfigColumnsPersistenceService::class);
                        $errorsColumns = $columnPersistentService->process($config, $config->tmpColumns);
                    }
                    if ($errorsColumns !== null && empty($errorsColumns) === true) {
                        $transaction->commit();
                    } else {
                        $transaction->rollBack();
                        /** @var ImportError $errorsColumn */
                        foreach ($errorsColumns as $errorsColumn) {
                            $config->addError('tmpColumns', $errorsColumn->message);
                            break;
                        }
                    }
                } else {
                    $transaction->rollBack();
                }
            }
            return $config;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
