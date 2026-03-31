<?php
/**
 * ImportConfigController.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package dghyse\importExport\controllers
 *
 */

namespace fractalCms\importExport\controllers;

use fractalCms\core\components\Constant as CoreConstant;
use fractalCms\importExport\components\Constant;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\configuration\factories\ImportConfigColumn;
use fractalCms\importExport\database\services\ConfigDataBaseService;
use fractalCms\importExport\database\services\SourceColumnsResolver;
use fractalCms\importExport\pipeline\services\ActiveRecordParameterService;
use fractalCms\importExport\pipeline\services\RowProcessorService;
use fractalCms\importExport\pipeline\services\ImportExportExecutionService;
use fractalCms\importExport\database\services\DbView;
use fractalCms\importExport\configuration\services\ConfigColumnsPersistenceService;
use fractalCms\importExport\configuration\services\ConfigManagementService;
use fractalCms\importExport\configuration\services\ConfigColumnsGeneratorService;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use Exception;
use Yii;

class ImportConfigController extends BaseController
{

    public RowProcessorService $rowProcessorService;
    protected DbView $dbView;
    protected ActiveRecordParameterService $activeRecordParameter;
    protected SourceColumnsResolver $sourceColumnResolver;
    protected ImportConfigColumn $importConfigColumnFactory;
    protected ConfigDataBaseService $configDatabase;
    protected ConfigManagementService $configManagementService;
    protected ConfigColumnsPersistenceService $configColumnsPersistentService;

    /**
     * @inheritDoc
     */
   public function __construct(
        $id,
        $module,
        DbView $dbView,
        RowProcessorService $rowProcessorService,
        SourceColumnsResolver $sourceColumnResolver,
        ActiveRecordParameterService $activeRecordParameter,
        ConfigManagementService $configManagementService,
        ConfigColumnsPersistenceService $configColumnsPersistentService,
        $config = []
    )
   {
        parent::__construct($id, $module, $config);
        $this->dbView = $dbView;
        $this->activeRecordParameter = $activeRecordParameter;
        $this->rowProcessorService = $rowProcessorService;
        $this->sourceColumnResolver = $sourceColumnResolver;
        $this->configManagementService = $configManagementService;
        $this->configColumnsPersistentService = $configColumnsPersistentService;
        $this->importConfigColumnFactory = new ImportConfigColumn();
        $configColumnGenerator = new ConfigColumnsGeneratorService($this->sourceColumnResolver, $this->importConfigColumnFactory);
        $this->configDatabase = new ConfigDataBaseService($dbView, $configColumnGenerator);
   }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index', 'create', 'update', 'test-import'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'test-import'],
                    'roles' => [Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_LIST],
                    'denyCallback' => function ($rule, $action) {
                        return $this->redirect(['default/index']);
                    }
                ],
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => [Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_CREATE],
                    'denyCallback' => function ($rule, $action) {
                        return $this->redirect(['default/index']);
                    }
                ],
                [
                    'allow' => true,
                    'actions' => ['update'],
                    'roles' => [Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_UPDATE],
                    'denyCallback' => function ($rule, $action) {
                        return $this->redirect(['default/index']);
                    }
                ],
            ]
        ];
        return $behaviors;
    }


    /**
     * Dashboard
     *
     * @return string
     * @throws \Throwable
     */
    public function actionIndex()
    {
        try {
            $response = null;
            $request = Yii::$app->request;
            $modelQuery = ImportConfig::find();
            /**
             * @var ImportConfig $model
             */
            $model = Yii::createObject(ImportConfig::class);
            $model->scenario = ImportConfig::SCENARIO_IMPORT_JSON_FILE;
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
                $model->importFile = UploadedFile::getInstance($model, 'importFile');
                if ($model->validate() === true && $model->importFile instanceof UploadedFile) {
                    $model = $this->configManagementService->process($model);
                    if ($model->hasErrors() === false) {
                        $response = $this->redirect(['import-config/update', 'id' => $model->id]);
                    }
                }
            }
            if ($response === null) {
                $response = $this->render('index', [
                    'modelQuery' => $modelQuery,
                    'model' => $model
                ]);
            }
            return $response;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Create import config
     *
     * @return string|\yii\web\Response|null
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionCreate()
    {
        try {
            $request = Yii::$app->request;
            $response = null;
            /**
             * @var ImportConfig $model
             */
            $model = Yii::createObject(ImportConfig::class);
            $model->scenario = ImportConfig::SCENARIO_CREATE;
            $tables = $this->activeRecordParameter->getActiveModelTableNames();
            $rowProcessors = ($this->rowProcessorService instanceof RowProcessorService) ?
                $this->rowProcessorService->getAll() : [];
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
                if ($model->validate() === true) {
                    $buildViewOk = false;
                    try {
                        $this->configDatabase->generateDbView($model);
                        $buildViewOk = true;
                    } catch (Exception $e) {
                        Yii::error($e->getMessage(), __METHOD__);
                        $model->addError('sql', 'Erreur dans la requête SQL. Vérifier les colonnes (doublons, alias, SELECT *, JOIN, etc.)');
                    }
                    if ($buildViewOk === true) {
                        $transaction = Yii::$app->db->beginTransaction();
                        $model->save();
                        $model->refresh();
                        $rawColumns = $this->configDatabase->generateColumns($model);
                        $errorsColumns = $this->configColumnsPersistentService->process($model, $rawColumns);
                        if (empty($errorsColumns) === true) {
                            $transaction->commit();
                            $response = $this->redirect(['import-config/update', 'id' => $model->id]);
                        } else {
                            $transaction->rollBack();
                            $model->addError('name', 'Des erreurs ont été détectées dans la configuration des colonnes, merci, de vérifier');
                        }
                    }

                }
            }
            if ($response === null) {
                $response = $this->render('manage', [
                    'model' => $model,
                    'tables' => $tables,
                    'rowProcessors' => $rowProcessors,
                ]);
            }
            return $response;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Update import config
     *
     * @param $id
     * @return string|\yii\web\Response|null
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionUpdate($id)
    {
        try {
            $request = Yii::$app->request;
            $response = null;
            /**
             * @var ImportConfig $model
             */
            $model = ImportConfig::findOne($id);
            if ($model === null) {
                throw new NotFoundHttpException('Model ImportConfig Not found id : '.$id);
            }
            $tables = $this->activeRecordParameter->getActiveModelTableNames();
            $model->scenario = ImportConfig::SCENARIO_CREATE;

            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                if(empty($body[$model->formName()]['tmpColumns']) === true) {
                    $body[$model->formName()]['tmpColumns'] = [];
                }
                $model->load($body);
                if ($model->validate() === true) {
                    $columns =  (empty($model->tmpColumns) === false) ? $model->tmpColumns : [];
                    $transaction = Yii::$app->db->beginTransaction();
                    $buildViewOk = false;
                    try {
                        $this->configDatabase->generateDbView($model);
                        if (empty($columns) === true) {
                            $columns = $this->configDatabase->generateColumns($model);
                        }
                        $buildViewOk = true;
                    } catch (Exception $e) {
                        Yii::error($e->getMessage(), __METHOD__);
                        $transaction->rollBack();
                        $model->addError('sql', 'Erreur dans la requête SQL. Vérifier les colonnes (doublons, alias, SELECT *, JOIN, etc.)');
                    }
                    if ($buildViewOk === true) {
                        $errorColumns = $this->configColumnsPersistentService->process($model, $columns);
                        $model->save();
                        $model->refresh();
                        if (empty($errorColumns) === true) {
                            $transaction->commit();
                            $response = $this->redirect(['import-config/index']);
                        } else {
                            $transaction->rollBack();
                            $model->addError('name', 'Des erreurs ont été détectées dans la configuration des colonnes, merci, de vérifier');
                        }
                    }
                }
            }
            $rowProcessors = ($this->rowProcessorService instanceof RowProcessorService) ?
                $this->rowProcessorService->getToList($model->type) : [];
            if ($response === null) {
                $response = $this->render('manage', [
                    'model' => $model,
                    'tables' => $tables,
                    'rowProcessors' => $rowProcessors,
                ]);
            }
            return $response;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTestImport()
    {
        try {
            $request = Yii::$app->request;
            $modelQuery = ImportConfig::find()->andWhere(['active' => 1]);
            /** @var ImportConfig $model */
            $model = Yii::createObject(ImportConfig::class);
            $model->scenario = ImportConfig::SCENARIO_IMPORT_EXPORT;
            $importJob = null;
            $readyToDownload = false;
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
                $model->importFile = UploadedFile::getInstance($model, 'importFile');
                if($model->validate() === true) {
                    /** @var ImportConfig $targetModel */
                    $targetModel = ImportConfig::findOne(['id' => $model->importConfigId]);
                    if ($targetModel !== null) {
                        $executionService = new ImportExportExecutionService();
                        if ($targetModel->isImport() === true) {
                            $finalfilePath = $model->generateImportfileTarget();
                            if ($finalfilePath !== null) {
                                $importJob = $executionService->executeImport($targetModel, $finalfilePath);
                                unlink($finalfilePath);
                            }
                        } else {
                            $importJob = $executionService->executeExport($targetModel);
                        }
                        if($importJob !== null) {
                            if($importJob->type === ImportConfig::TYPE_EXPORT && empty($importJob->filePath) === false && $importJob->status === ImportJob::STATUS_SUCCESS) {
                                $readyToDownload = true;
                            } elseif ( $importJob->status === ImportJob::STATUS_FAILED) {
                                $importJob->addError('type', ['L\'action a échouée, veuillez utiliser les commande en ligne "php yii.php fractalCmsImportExport:import-export/index".']);
                                $readyToDownload = false;
                            }
                        } else {
                            $model->addError('importFile', 'Merci de télécharger un fichier');
                        }
                    } else {
                            $model->addError('importFile', 'Merci de sélectionner une configuration');

                    }
                 

                }
            }
            $importConfigs = [];
            /** @var  ImportConfig $importConfig */
            foreach ($modelQuery->each() as $importConfig) {
                $statement = (empty($importConfig->table) === false) ? $importConfig->table : 'Requête SQL';
                $importConfigs[$importConfig->id] = $importConfig->name.': version :'.$importConfig->version.' : "'.$importConfig->type.'" : '.$statement;
            }
            if ($readyToDownload === true) {
                $this->download(Yii::getAlias($importJob->filePath));
            } else {
                return $this->render('test-import', [
                    'importConfigs' => $importConfigs,
                    'model' => $model,
                    'importJob' => $importJob,
                ]);
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $path
     * @return void
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    protected function download(string $path)
    {
        try {
            if (file_exists($path) === true) {
                $info = pathinfo($path);
                $filename = $info['basename'];
                $extension = strtolower($info['extension']);
                $data = file_get_contents($path);
                if ($extension === ImportConfig::FORMAT_CSV) {
                    Yii::$app->response->sendContentAsFile($data, $filename, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
                } else {
                    Yii::$app->response->headers->set('Content-Type', 'text/csv; charset=utf-8');
                    Yii::$app->response->sendContentAsFile($data, $filename, ['mimeType' => 'text/csv']);
                }
                //unlink($path);
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
