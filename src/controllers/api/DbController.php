<?php
/**
 * DbController.php
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
use yii\filters\AccessControl;
use Exception;
use Yii;
use yii\web\ForbiddenHttpException;

class DbController extends BaseController
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
                    'actions' => ['get-tables'],
                    'verbs' => ['get'],
                    'roles' => [
                        Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_LIST,
                    ],
                ]
            ],
            'denyCallback' => function ($rule, $action) {
                throw new ForbiddenHttpException();
            }
        ];
        return $behaviors;
    }


    /**
     * @return array
     * @throws \yii\base\NotSupportedException
     */
    public function actionGetTables() : array
    {
        try {
            $db = Yii::$app->db;
            $values = [];
            foreach ($db->getSchema()->tableNames as $table) {
                $values[] = [
                    'name' => ucfirst($table),
                    'value' => $table
                ];
            }
            return $values;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

}
