<?php
/**
 * BaseController.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\controllers
 */

namespace fractalCms\importExport\controllers;

use fractalCms\core\Module as CoreModule;
use fractalCms\importExport\behaviors\Assets;
use yii\web\Controller;
use Exception;
use Yii;

class BaseController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['assets'] = [
            'class' => Assets::class
        ];
        return $behaviors;
    }
    /**
     * @inheritDoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        try {
            parent::init();
            $coreModule = CoreModule::getInstance();
            $this->layout = $coreModule->layoutPath.'/'.$coreModule->layout;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }
}
