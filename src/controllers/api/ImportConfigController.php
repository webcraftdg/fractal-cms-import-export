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

use fractalCms\core\models\Parameter;
use fractalCms\importExport\components\Constant;
use fractalCms\core\components\Constant as CoreConstant;
use fractalCms\core\controllers\api\BaseController;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\DbView;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use Exception;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ImportConfigController extends BaseController
{

    private DbView $dbView;

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
            'only' => ['get', 'post',  'get-columns', 'post-columns', 'get-table-columns', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['get', 'post',  'get-columns', 'post-columns', 'get-table-columns'],
                    'verbs' => ['get', 'post'],
                    'roles' => [
                        Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_LIST,
                        Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_CREATE,
                        Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_UPDATE
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['delete', 'delete-column'],
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
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionPost($id) : array
    {
        try {
            $request = Yii::$app->request;
            $importConfig = ImportConfig::findOne($id);
            if ($importConfig === null) {
                throw new NotFoundHttpException('Import config not Found : '.$id);
            }
            if ($request->isPost === true) {
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
                    }
                }
            }
            return  $importConfig->toArray();
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
                $importJob->delete();
            }
            /** @var ImportConfigColumn $importColumn */
            foreach ($model->getImportColumns()->each() as $importColumn) {
                $importColumn->delete();
            }
            if (empty($model->sql) === false && empty($model->table) === true) {
                $name = $model->getContextName();
                $this->dbView->drop($name);
            }
            $model->delete();
            $response->statusCode = 204;
            return $response;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGetColumns($id) : array
    {
        try {
            $paginationPerPage = Parameter::getParameter('PAGINATION', 'PER_PAGE');
            $page = Yii::$app->request->getQueryParam('page', 0);
            $search = Yii::$app->request->getQueryParam('search', 0);
            $importConfig = ImportConfig::findOne($id);
            if ($importConfig === null) {
                throw new NotFoundHttpException('Import config not Found : '.$id);
            }
            $query =  $importConfig->getImportColumnsWithSearch($search);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => ($paginationPerPage) ?? 15
                ],
            ]);
            $dataProvider->pagination->setPage($page);
            $models = $dataProvider->getModels();
            $this->addHeader($dataProvider->getPagination());
            return $models;
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
            $paginationPerPage = Parameter::getParameter('PAGINATION', 'PER_PAGE');
            $page = Yii::$app->request->getQueryParam('page', 0);
            $search = Yii::$app->request->getQueryParam('search', null);
            $importConfig = ImportConfig::findOne($id);
            if ($importConfig === null) {
                throw new NotFoundHttpException('Import config not Found : '.$id);
            }
            if ($request->isPost === true) {
                $body = $request->getBodyParams();
                if (is_array($body) === true && empty($body) === false) {
                    $importConfig->manageColumns($body);
                }
            }
            $query = $importConfig->getImportColumnsWithSearch($search);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => ($paginationPerPage) ?? 15
                ],
            ]);
            $dataProvider->pagination->setPage($page);
            $this->addHeader($dataProvider->pagination);
            return  $dataProvider->getModels();
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param $id
     * @param $columnId
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteColumn($id, $columnId) : Response
    {
        try {
            $response = Yii::$app->getResponse();
            $importConfig = ImportConfig::findOne($id);
            if ($importConfig === null) {
                throw new NotFoundHttpException('Import config not Found : '.$id);
            }

            /** @var ImportConfigColumn $model */
            $model = ImportConfigColumn::findOne(['id' => $columnId]);
            if ($model === null) {
                throw new NotFoundHttpException('import column not found');
            }
            $model->delete();
            $response->statusCode = 204;
            return $response;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     * @throws \yii\base\NotSupportedException
     */
    public function actionGetTableColumns($id) : array
    {
        try {
            $importConfig = ImportConfig::findOne($id);
            if ($importConfig === null) {
                throw new NotFoundHttpException('Import config not Found : '.$id);
            }
            return $importConfig->getContextColumns($this->dbView);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param Pagination $pagination
     * @return void
     * @throws Exception
     */
    protected function addHeader(Pagination $pagination) : void
    {
        try {
            Yii::$app->response->headers->set('X-pagination-current-Page', $pagination->getPage());
            Yii::$app->response->headers->set('X-pagination-total-page', $pagination->getPageCount());
            Yii::$app->response->headers->set('X-pagination-per-page', $pagination->getPageSize());
            Yii::$app->response->headers->set('X-pagination-total-entries', $pagination->totalCount);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
