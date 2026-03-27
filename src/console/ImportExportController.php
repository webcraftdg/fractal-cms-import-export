<?php
/**
 * ImportConfig.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\console
 */
namespace fractalCms\importExport\console;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\Export;
use fractalCms\importExport\services\Import;
use yii\console\Controller;
use yii\helpers\Json;
use yii\base\Exception as BaseException;
use Exception;
use fractalCms\importExport\services\ExportService;
use fractalCms\importExport\services\ImportService;
use fractalCms\importExport\services\runtimes\ConfigRuntimeService;
use Yii;

class ImportExportController extends Controller
{

    public $name;
    public $version;
    public $pathFile;
    public $isTest = 0;
    public $params;
    public $batchSize;

    protected ConfigRuntimeService $configRuntimeService;


    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return ['name', 'version', 'pathFile', 'isTest', 'params', 'batchSize'];
    }

    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        return [
            'name' => 'name',
            'version' => 'version',
            'pathFile' => 'pathFile',
            'isTest' => 'isTest',
            'params' => 'params',
            'batchSize' => 'batchSize',
        ];
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function init()
    {
        parent::init();
        if (Yii::$container->has(ConfigRuntimeService::class) === true) {
            $this->configRuntimeService = Yii::$container->get(ConfigRuntimeService::class);
        }
    }

    /**
     * php yii.php fractalCmsImportExport:import-export/index -name={name} -version={version} -isTest=1 -params='key:value, key:value' -batchSize=200
     *
     * @return void
     * @throws BaseException
     * @throws \yii\db\Exception
     */
    public function actionIndex()
    {
        try {
            if (empty($this->name) === true) {
                throw new BaseException('paramètre -name est obligatoire');
            }
            if (empty($this->version) === true) {
                throw new BaseException('paramètre -version est obligatoire');
            }
            $importConfig = ImportConfig::find()->andWhere(['name' => $this->name, 'version' => $this->version])->one();
            if ($importConfig === null) {
                throw new BaseException('Aucun configuration trouvé en base de données avec vos paramètres');
            }

            if (empty($this->pathFile) === true && $importConfig->type === ImportConfig::TYPE_IMPORT) {
                throw new BaseException('paramètre -pathFile est obligatoire pour un import');
            }

            $batchSize = $this->batchSize;
            if (empty($batchSize) === true) {
                $batchSize = 1000;
            }
            $isTest = (boolean)$this->isTest;
            $params = [];
            if (empty($this->params) === false) {
                $splits = preg_split('/\,/', $this->params);
                foreach ($splits as $split) {
                    $values = preg_split('/\:/', $split);
                    $key = ($values[0]) ?? null;
                    $value = ($values[1]) ?? null;
                    if ($key !== null && $value !== null) {
                        $params[$key] = $value;
                    }
                }
            }

            if ($importConfig->type === ImportConfig::TYPE_IMPORT) {
                $importService = new ImportService();
                $importJob = $importService->run(
                    config: $importConfig,
                    filePath: $this->pathFile,
                    isTest: $isTest,
                    params: $params
                );
            } else {
                $exportService = new ExportService($this->configRuntimeService);
                $importJob = $exportService->run(
                    config: $importConfig,
                    batchSize: (int)$batchSize,
                    params: $params
                );
            }
            $importJob->refresh();
            $this->stdout('Resultat : '.Json::encode($importJob->toArray(), JSON_PRETTY_PRINT)."\n");

        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

}
