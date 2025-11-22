<?php
/**
 * ConfigImportExportController.php
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
use yii\filters\AccessControl;
use Exception;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ImportConfigController extends BaseController
{
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
                    $vadid = $model->manageImportFile();
                    if ($vadid === true) {
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

    public function actionImportFile()
    {
        try {
            $request = Yii::$app->request;
            $response = null;
            $model = Yii::createObject(ImportConfig::class);
            $model->scenario = ImportConfig::SCENARIO_CREATE;
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);

            }
            if ($response === null) {
                $response = $this->render('import-file', [
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
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
                if ($model->validate() === true) {
                    $model->save();
                    $model->refresh();
                    $response = $this->redirect(['import-config/index']);
                }
            }
            if ($response === null) {
                $response = $this->render('manage', [
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
            $model->scenario = ImportConfig::SCENARIO_CREATE;
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                $model->load($body);
                if ($model->validate() === true) {
                    $model->save();
                    $model->refresh();
                    $response = $this->redirect(['import-config/index']);
                }
            }
            if ($response === null) {
                $response = $this->render('manage', [
                    'model' => $model
                ]);
            }
            return $response;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
