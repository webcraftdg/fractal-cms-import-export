<?php
/**
 * Module.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport
 */

namespace fractalCms\importExport;

use Exception;
use fractalCms\core\interfaces\FractalCmsCoreInterface;
use fractalCms\core\Module as CoreModule;
use fractalCms\core\components\Constant as CoreConstant;
use fractalCms\importExport\components\Constant;
use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\helpers\Url;

class Module extends \yii\base\Module implements BootstrapInterface, FractalCmsCoreInterface
{


    public $version = 'v1.0.0';
    public string $name = 'importExport';
    public string $filePathImport = '@webroot/imports';
    public string $commandNameSpace = 'fractalCmsImportExport:';

    private string $contextId = 'importExport';
    public function bootstrap($app)
    {
        try {
            Yii::setAlias('@fractalCms/importExport', __DIR__);

            if ($app instanceof ConsoleApplication) {
                $this->configConsoleApp($app);
            }
            $filePath = Yii::getAlias($this->filePathImport);
            if(file_exists($filePath) === false) {
                mkdir($filePath);
            }
        } catch (Exception $e){
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }


    /**
     * Config Console Application
     *
     * @param ConsoleApplication $app
     * @return void
     * @throws Exception
     */
    protected function configConsoleApp(ConsoleApplication $app) : void
    {
        try {
            //Init migration
            if (isset($app->controllerMap['migrate']) === true) {
                //Add migrations namespace
                if (isset($app->controllerMap['migrate']['migrationNamespaces']) === true) {
                    $app->controllerMap['migrate']['migrationNamespaces'][] = 'fractalCms\importExport\migrations';
                } else {
                    $app->controllerMap['migrate']['migrationNamespaces'] = ['fractalCms\importExport\migrations'];
                }
            }
        }catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Get module name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }


    public function getInformations() : array
    {
        return [];
    }

    /**
     * Return context Permission
     * @return array
     */
    public function getPermissions(): array
    {
        return [
            Constant::PERMISSION_MAIN_UPLOAD => 'Télécharger un fichier JSON',
            Constant::PERMISSION_MAIN_EXPORT => 'Exporter les données',
        ];
    }

    public function getMenu() : array
    {
        try {
            Yii::debug(Constant::TRACE_DEBUG, __METHOD__, __METHOD__);
            $importExport = [
                'title' => 'ImportExport',
                'url' => null,
                'optionsClass' => [],
                'children' => []
            ];
            if (Yii::$app->user->can(Constant::PERMISSION_MAIN_EXPORT.CoreConstant::PERMISSION_ACTION_LIST) === true) {
                $optionsClass = [];
                if (Yii::$app->controller->id == 'config-type') {
                    $optionsClass[] = 'text-primary fw-bold';
                }
                if(empty($importExport['optionsClass']) === true) {
                    $importExport['optionsClass'] = $optionsClass;
                }
                $importExport['children'][] = [
                    'title' => 'Configuration import',
                    'url' => Url::to(['/'.$this->contextId.'/import-config/index']),
                    'optionsClass' => $optionsClass,
                    'children' => [],
                ];
            }
            $data = [];
            if (empty($importExport['children']) === false) {
                $data[] = $importExport;
            }
            return $data;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Get context routes
     *
     * @return array[]
     */
    public function getRoutes(): array
    {
        $coreId = CoreModule::getInstance()->id;
        $contextId = $this->contextId;
        return [
            $coreId.'/configuration-des-imports-export/liste' => $contextId.'/import-config/index',
            $coreId.'/configuration-des-imports-export/creer' => $contextId.'/import-config/create',
            $coreId.'/configuration-des-imports-export/<id:([^/]+)>/editer' => $contextId.'/import-config/update',
            $coreId.'/configuration-des-imports-export/<id:([^/]+)>/supprimer' => $contextId.'/api/import-config/delete',
        ];
    }

    /**
     * Get context id
     *
     * @return string
     * @throws Exception
     */
    public function getContextId() : string
    {
        try {
            return $this->contextId;
        }catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Set context id
     *
     * @param $id
     * @return void
     * @throws Exception
     */
    public function setContextId($id) : void
    {
        try {
            $this->contextId = $id;
        }catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
