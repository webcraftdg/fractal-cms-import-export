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
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\Export;
use fractalCms\importExport\services\Import;
use yii\console\Controller;
use Yii;
use Exception;
use yii\base\Exception as BaseException;
use yii\helpers\Json;

class ImportExportController extends Controller
{

    public $name;
    public $version;
    public $pathFile;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return ['name', 'version', 'pathFile',];
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
        ];
    }

    /**
     * php yii.php fractalCmsImportExport:import-export/index -name={name} -version={version}
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

            if ($importConfig->type === ImportConfig::TYPE_IMPORT) {
                $importJob = Import::run($importConfig, $this->pathFile);
            } else {
                $importJob = Export::run($importConfig);
            }
            $importJob->refresh();
            $this->stdout('Resultat : '.Json::encode($importJob->toArray(), JSON_PRETTY_PRINT)."\n");

        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

}
