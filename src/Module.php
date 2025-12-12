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
use fractalCms\core\components\Constant as CoreConstant;
use fractalCms\core\interfaces\FractalCmsCoreInterface;
use fractalCms\core\Module as CoreModule;
use fractalCms\importExport\components\Constant;
use fractalCms\importExport\console\ImportExportController;
use fractalCms\importExport\db\DbView;
use fractalCms\importExport\estimations\ExportLimiter;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\Parameter;
use fractalCms\importExport\services\Transformer;
use fractalCms\importExport\services\Transformer as TransformService;
use fractalCms\importExport\transformers\BooleanTransformer;
use fractalCms\importExport\transformers\DateTransformer;
use fractalCms\importExport\transformers\LowerTransformer;
use fractalCms\importExport\transformers\NumberTransformer;
use fractalCms\importExport\transformers\ReplaceTransformer;
use fractalCms\importExport\transformers\TrimTransformer;
use fractalCms\importExport\transformers\UpperTransformer;
use Yii;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\helpers\Url;

class Module extends \yii\base\Module implements BootstrapInterface, FractalCmsCoreInterface
{


    public $version = 'v1.0.0';
    public string $name = 'importExport';
    public string $filePathImport = '@webroot/imports';
    public int $maxRows = 20000;
    public int $maxColumns = 80;
    public int $maxEstimatedMb = 500;
    public array $pathsNamespacesModels = [];
    public string $commandNameSpace = 'fractalCmsImportExport:';

    private string $contextId = 'importExport';
    public function bootstrap($app)
    {
        try {
            Yii::setAlias('@fractalCms/importExport', __DIR__);

            Yii::$container->setSingleton(DbView::class, [
                'class' => DbView::class,
            ]);
            Yii::$container->setDefinitions([
                TransformService::class => function() {
                    return new TransformService([
                        new DateTransformer(),
                        new TrimTransformer(),
                        new UpperTransformer(),
                        new LowerTransformer(),
                        new ReplaceTransformer(),
                        new NumberTransformer(),
                        new BooleanTransformer(),
                    ]);
                }
            ]);
            $app->setComponents([
                'importDbParameters' => [
                    'class' => Parameter::class
                ]
            ]);
            $app->setComponents([
                'exportLimiter' => [
                    'class' => ExportLimiter::class,
                    'maxRows' => $this->maxRows,
                    'maxColumns' => $this->maxColumns,
                    'maxEstimatedMb' => $this->maxEstimatedMb
                ]
            ]);

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
            $app->controllerMap[$this->commandNameSpace.'import-export'] = [
                'class' => ImportExportController::class,
            ];
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


    /**
     * [
     *  'key' => 'value',
     * ]
     * @return array
     */
    public function getInformations() : array
    {
        $importCount = ImportConfig::find()->count();
        return [
            'nombre de configuration' => $importCount
        ];
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

    /**
     * @return array
     * @throws Exception
     */
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

                $importExport['children'][] = [
                    'title' => 'Test import/export',
                    'url' => Url::to(['/'.$this->contextId.'/import-config/test-import']),
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
            $coreId.'/configuration-des-imports-export/test-import-export' => $contextId.'/import-config/test-import',
            $coreId.'/configuration-des-imports-export/<id:([^/]+)>/exporter' => $contextId.'/import-config/export',
            $coreId.'/configuration-des-imports-export/<id:([^/]+)>/editer' => $contextId.'/import-config/update',
            $coreId.'/configuration-des-imports-export/<id:([^/]+)>/supprimer' => $contextId.'/api/import-config/delete',
            $coreId.'/api/import-config/transformers' => $contextId.'/api/import-config/get-transform-services',
            $coreId.'/api/import-config/<id:([^/]+)>' => $contextId.'/api/import-config/get',
            $coreId.'/api/import-config/<id:([^/]+)>/post-columns' => $contextId.'/api/import-config/post-columns',
            $coreId.'/api/import-config/<id:([^/]+)>/get-columns' => $contextId.'/api/import-config/get-columns',
            $coreId.'/api/import-config/<id:([^/]+)>/columns/<columnId:([^/]+)>/delete' => $contextId.'/api/import-config/delete-column',
            $coreId.'/api/import-config/<id:([^/]+)>/table-columns' => $contextId.'/api/import-config/get-table-columns',
            $coreId.'/api/db/tables' => $contextId.'/api/db/get-tables',
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
