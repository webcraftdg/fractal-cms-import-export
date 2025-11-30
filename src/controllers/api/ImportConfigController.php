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

namespace fractalCms\importExport\controllers\api;

use fractalCms\importExport\components\Constant;
use fractalCms\core\components\Constant as CoreConstant;
use fractalCms\core\controllers\api\BaseController;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\models\ImportJobLog;
use yii\filters\AccessControl;
use Exception;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
            'only' => ['get', 'post-columns'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['get', 'post-columns'],
                    'verbs' => ['get', 'post'],
                    'roles' => [
                        Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_LIST,
                        Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_CREATE,
                        Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_UPDATE
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['delete'],
                    'verbs' => ['delete'],
                    'roles' => [
                        Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_DELETE,
                    ],
                ],
            ],
            'denyCallback' => function ($rule, $action) {
                throw new ForbiddenHttpException();
            }
        ];
        return $behaviors;
    }


    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGet($id) : array
    {
        try {
            $importConfig = ImportConfig::findOne($id);
            if ($importConfig === null) {
                throw new NotFoundHttpException('Import config not Found : '.$id);
            }
            return $importConfig->toArray();
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * Post
     *
     * @return array
     * @throws \Throwable
     */
    public function actionPostColumns($id) : array
    {
        try {
            $request = Yii::$app->request;
            $importConfig = ImportConfig::findOne($id);
            if ($importConfig === null) {
                throw new NotFoundHttpException('Import config not Found : '.$id);
            }
            $columns =  $importConfig->tmpColumns;
            $body = $request->getBodyParams();
            $importConfig->scenario = ImportConfig::SCENARIO_UPDATE;
            if (is_array($body) === true && empty($body) === false) {
                $importConfig->tmpColumns = $body;
                if ($importConfig->validate() === true) {
                    $importConfig->jsonConfig = Json::encode($importConfig->tmpColumns);
                    $importConfig->save(false,
                    [
                        'jsonConfig',
                        'dateUpdate'
                    ]);
                    $importConfig->refresh();
                    $columns =  $importConfig->tmpColumns;
                }
            }
            return  $columns;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    public function actionGetTableColumns($id) : array
    {
        try {
            $db = Yii::$app->db;
            $importConfig = ImportConfig::findOne($id);
            if ($importConfig === null) {
                throw new NotFoundHttpException('Import config not Found : '.$id);
            }
            $values = [];
            foreach ($db->getSchema()->getTableSchema($importConfig->table)->columns as $column) {
                $values[] = [
                    'name' => ucfirst($column->name),
                    'value' => $column->name
                ];
            }
            return $values;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id) : Response
    {
        try {
            $response = Yii::$app->getResponse();
            /** @var ImportConfig $model */
            $model = ImportConfig::findOne(['id' => $id]);
            if ($model === null) {
                throw new NotFoundHttpException('import config not found');
            }
            /** @var ImportJob $importJob */
            foreach ($model->getImportJobs()->each() as $importJob) {
                ImportJobLog::deleteAll(['importJobId' => $importJob->id]);
                $importJob->delete();
            }
            $model->delete();
            $response->statusCode = 204;
            return $response;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
