<?php
/**
 * DefaultController.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package dghyse\importExport\controllers
 *
 */

namespace fractalCms\importExport\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use Exception;
use Yii;

class DefaultController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index'],
                    'roles' => ['?'],
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
            return $this->render('index', [
            ]);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
