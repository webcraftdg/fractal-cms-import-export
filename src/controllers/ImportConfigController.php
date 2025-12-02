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

use fractalCms\importExport\components\Constant;
use fractalCms\core\components\Constant as CoreConstant;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\DbView;
use fractalCms\importExport\services\Export;
use yii\filters\AccessControl;
use Exception;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\helpers\Json;

class ImportConfigController extends BaseController
{

    protected DbView $dbView;

    /**
     * @inheritDoc
     */
   public function __construct($id, $module, DbView $dbView, $config = [])
   {
       parent::__construct($id, $module, $config);
       $this->dbView = $dbView;
   }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index', 'create', 'update'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => [Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_LIST],
                    'denyCallback' => function ($rule, $action) {
                        return $this->redirect(['default/index']);
                    }
                ],
                [
                    'allow' => true,
                    'actions' => ['index'],
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
            $model->scenario = ImportConfig::SCENARIO_IMPORT_FILE;
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
            $tables = Constant::getDbTable();
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
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
                        $model->buildJson($this->dbView);
                        $model->save();
                        $model->refresh();
                        $response = $this->redirect(['import-config/update', 'id' => $model->id]);
                    }

                }
            }
            if ($response === null) {
                $response = $this->render('manage', [
                    'model' => $model,
                    'tables' => $tables
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
            $tables = Constant::getDbTable();
            $model->scenario = ImportConfig::SCENARIO_CREATE;
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                if(empty($body[$model->formName()]['tmpColumns']) === true) {
                    $body[$model->formName()]['tmpColumns'] = [];
                }
                $model->load($body);
                if ($model->validate() === true) {
                    $tmpColumns =  (empty($model->tmpColumns) === false) ? Json::encode($model->tmpColumns) : null;
                    $model->jsonConfig = $tmpColumns;
                    $model->save();
                    $model->refresh();
                    $response = $this->redirect(['import-config/index']);
                }
            }
            if ($response === null) {
                $response = $this->render('manage', [
                    'model' => $model,
                    'tables' => $tables
                ]);
            }
            return $response;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Export
     *
     * @param $id
     * @return void
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function actionExport($id)
    {
        try {
            $model = ImportConfig::findOne($id);
            if ($model === null) {
                throw new NotFoundHttpException('Model ImportConfig Not found id : '.$id);
            }
            $path = Export::run($model);
            $filename = 'export_'.date('d_m-Y');
            if (file_exists($path) === true) {
                $data = file_get_contents($path);
                if (in_array($model->exportFormat, [ImportConfig::FORMAT_EXCEL , ImportConfig::FORMAT_EXCEL_X ]) === true) {
                    $filename = $filename.'.xlsx';
                    Yii::$app->response->sendContentAsFile($data, $filename, ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
                } else {
                    $filename = $filename.'.csv';
                    Yii::$app->response->headers->set('Content-Type', 'text/csv; charset=utf-8');
                    Yii::$app->response->sendContentAsFile($data, $filename, ['mimeType' => 'text/csv']);
                }
                unlink($path);
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
