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
    public $type;
    public $pathFile;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return ['name', 'version', 'type', 'pathFile',];
    }

    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        return [
            'name' => 'name',
            'version' => 'version',
            'type' => 'type',
            'pathFile' => 'pathFile',
        ];
    }

    /**
     * php yii.php fractalCmsImportExport:import-export/index -name={name} -version={version} -type=export
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
            if (empty($this->type) === true || in_array($this->type, array_keys(ImportConfig::optsTypes())) === false) {
                throw new BaseException('paramètre -type est obligatoire, et doit-etre \'inport\'|\'export\'');
            }
            if (empty($this->pathFile) === true && $this->type === ImportConfig::TYPE_IMPORT) {
                throw new BaseException('paramètre -pathFile est obligatoire pour un import');
            }

            $importConfig = ImportConfig::find()->andWhere(['name' => $this->name, 'version' => $this->version])->one();
            if ($importConfig === null) {
                throw new BaseException('Aucun configuration trouvé en base de données avec vos paramètres');
            }

            if ($this->type === ImportConfig::TYPE_IMPORT) {
                $importJob = Import::run($importConfig, $this->pathFile);
            } else {
                $importJob = Export::run($importConfig);
            }
            $this->stdout('Resultat : '.Json::encode($importJob->toArray(), JSON_PRETTY_PRINT)."\n");

        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

}
