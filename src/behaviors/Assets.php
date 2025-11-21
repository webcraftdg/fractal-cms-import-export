<?php
/**
 * Assets.php
 *
 * PHP version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @package fractalCms\content\behaviors
 */
namespace fractalCms\importExport\behaviors;

use fractalCms\importExport\assets\WebpackAsset;
use yii\base\Behavior;
use Exception;
use Yii;
use yii\web\Controller;

class Assets extends Behavior
{
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction($event)
    {
        try {
            $controller = $this->owner;
            if ($controller instanceof Controller) {
                WebpackAsset::register($controller->view);
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
