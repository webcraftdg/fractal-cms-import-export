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

use Exception;
use fractalCms\core\components\Constant as CoreConstant;
use fractalCms\importExport\components\Constant;
use fractalCms\importExport\db\DbView;
use fractalCms\importExport\interfaces\DbView as DbViewInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\Parameter;
use fractalCms\importExport\services\RowTransformer as RowTransformerService;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ImportConfigController extends BaseController
{

    protected DbViewInterface $dbView;
    protected Parameter $parameter;
    public RowTransformerService $rowTransformersService;

    /**
     * @inheritDoc
     */
   public function __construct($id, $module, DbView $dbView, $config = [])
   {
       parent::__construct($id, $module, $config);
       $this->dbView = $dbView;
       if (Yii::$app->has('importDbParameters') === true) {
           $this->parameter = Yii::$app->importDbParameters;
       }
       if (Yii::$container->has(RowTransformerService::class) === true) {
           $this->rowTransformersService = Yii::$container->get(RowTransformerService::class);
       }
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
            $model = Yii::createObject(ImportConfig::class);
            $model->scenario = ImportConfig::SCENARIO_IMPORT_JSON_FILE;
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
                $model->importFile = UploadedFile::getInstance($model, 'importFile');
                if ($model->validate() === true && $model->importFile instanceof UploadedFile) {
                    $valid = $model->manageImportFile();
                    if ($valid === true) {
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
            $model = Yii::createObject(ImportConfig::class);
            $model->scenario = ImportConfig::SCENARIO_CREATE;
            $tables = $this->parameter->getActiveModelTableNames();
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
                $model->version = $model->checkVersion($model->name, $model->version);
                if ($model->validate() === true) {
                    $buildViewOk = false;
                    try {
                        $model->buildDbView($this->dbView);
                        $buildViewOk = true;
                    } catch (Exception $e) {
                        Yii::error($e->getMessage(), __METHOD__);
                        $model->addError('sql', 'Erreur dans la requête SQL. Vérifier les colonnes (doublons, alias, SELECT *, JOIN, etc.)');
                    }
                    if ($buildViewOk === true) {
                        $transaction = Yii::$app->db->beginTransaction();
                        $model->save();
                        $model->refresh();
                        $errorsColumns = $model->buildInitColumns($this->dbView);
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
                    'rowTransformers' => [],
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
            $model = ImportConfig::findOne($id);
            if ($model === null) {
                throw new NotFoundHttpException('Model ImportConfig Not found id : '.$id);
            }
            $tables = $this->parameter->getActiveModelTableNames();
            $model->scenario = ImportConfig::SCENARIO_CREATE;

            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                if(empty($body[$model->formName()]['tmpColumns']) === true) {
                    $body[$model->formName()]['tmpColumns'] = [];
                }
                $model->load($body);
                if ($model->validate() === true) {
                    $tmpColumns =  (empty($model->tmpColumns) === false) ? $model->tmpColumns : [];
                    $transaction = Yii::$app->db->beginTransaction();
                    $errorColumns = $model->manageColumns($tmpColumns);
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
            $rowTransformers = ($this->rowTransformersService instanceof RowTransformerService) ?
                $this->rowTransformersService->getToList($model->type) : [];
            if ($response === null) {
                $response = $this->render('manage', [
                    'model' => $model,
                    'tables' => $tables,
                    'rowTransformers' => $rowTransformers,
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
            $modelQuery = ImportConfig::find();
            $model = Yii::createObject(ImportConfig::class);
            $model->scenario = ImportConfig::SCENARIO_IMPORT_EXPORT;
            $importJob = Yii::createObject(ImportJob::class);
            $importJob->scenario = ImportJob::SCENARIO_CREATE;
            $readyToDownload = false;
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
                $model->importFile = UploadedFile::getInstance($model, 'importFile');
                if($model->validate() === true) {
                    $importJob = $model->manageImportExport();
                    if($importJob !== null) {
                        if($model->type === ImportConfig::TYPE_EXPORT && empty($importJob->filePath) === false && $importJob->status === ImportJob::STATUS_SUCCESS) {
                            $readyToDownload = true;
                        } elseif ( $importJob->status === ImportJob::STATUS_FAILED) {
                            $importJob->addError('type', ['L\'action a échouée, veuillez utiliser les commande en ligne "php yii.php fractalCmsImportExport:import-export/index".']);
                            $readyToDownload = false;
                        }
                    } else {
                        $model->addError('importFile', 'Merci de télécharger un fichier');
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
                $this->download($importJob->filePath, $model->exportFormat);
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
     * @param $type
     * @return void
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    protected function download(string $path, $type)
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
