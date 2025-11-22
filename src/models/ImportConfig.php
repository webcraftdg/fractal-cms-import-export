<?php
/**
 * ImportConfig.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\models
 */
namespace fractalCms\importExport\models;

use fractalCms\importExport\Module;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use Exception;
use Yii;
use yii\helpers\Json;
use yii\web\UploadedFile;

/**
 * This is the model class for table "importConfigs".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $version
 * @property int|null $active
 * @property int|null $truncateTable
 * @property string $table
 * @property resource|null $jsonConfig
 * @property string|null $dateCreate
 * @property string|null $dateUpdate
 *
 * @property ImportJob[] $importJobs
 */
class ImportConfig extends \yii\db\ActiveRecord
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_IMPORT_FILE = 'importFile';



    public $importFile;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['timestamp'] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'dateCreate',
            'updatedAtAttribute' => 'dateUpdate',
            'value' => new Expression('NOW()'),
        ];
        return $behaviors;
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'importConfigs';
    }

    public function scenarios() : array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'name', 'version', 'jsonConfig', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'name', 'version', 'jsonConfig', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table'
        ];

        $scenarios[self::SCENARIO_IMPORT_FILE] = [
            'name', 'version', 'jsonConfig', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table'
        ];
        return $scenarios;
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'version', 'jsonConfig', 'dateCreate', 'dateUpdate', 'table'], 'default', 'value' => null],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['active', 'truncateTable'], 'default', 'value' => 0],
            [['active', 'version', 'truncateTable'], 'integer'],
            [['importFile'],
                'required',
                'message' => 'Veuillez télécharger un fichier',
                'on' => [self::SCENARIO_IMPORT_FILE],
            ],
            [['importFile'], 'file',
                'skipOnEmpty' => false,
                'extensions' => ['json'],
                'checkExtensionByMimeType' => false,
                'maxFiles' => 1,
                'message' => 'Le fichier doit être au format JSON',
                'on' => [self::SCENARIO_IMPORT_FILE],
            ],
            [['name'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['type', 'jsonConfig', 'table'], 'string'],
            [['table'], 'validateTable','message' => 'La table doit être présente dans votre base de données', 'on' => [self::SCENARIO_IMPORT_FILE, self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name'], 'string', 'max' => 150],
            [['name', 'version'], 'unique', 'targetAttribute' => ['name', 'version'],'message' => 'name-version doit être unique'],
        ];
    }

    /**
     * @param $attribute
     * @param $arams
     *
     * @return bool
     */
    public function validateTable($attribute, $params) : bool
    {
        try {
            $table = $this->table;
            $dbTables = Yii::$app->db->schema->tableNames;
            $success = in_array($table, $dbTables);
            if( $success === false) {
                $this->addError('table', 'La table : "'.$table.'" doit être présente dans votre base de données');
            }
            return $success;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'active' => 'Active',
            'version' => 'Version',
            'type' => 'Type',
            'importFile' => 'Import fichier',
            'jsonConfig' => 'Json Config',
            'dateCreate' => 'Date Create',
            'dateUpdate' => 'Date Update',
        ];
    }

    /**
     * Manage import file
     * @return bool|mixed
     * @throws Exception
     */
    public function manageImportFile()
    {
        try {
            $modulePath = Yii::getAlias(Module::getInstance()->filePathImport);
            $valid = true;
            if ($this->importFile instanceof UploadedFile) {
                $finalPathFile = $modulePath.'/'. $this->importFile->baseName . '.' . $this->importFile->extension;
                $this->importFile->saveAs($finalPathFile);
                $contentJson = file_get_contents($finalPathFile);
                $valid = json_validate($contentJson);
                if ($valid === false) {
                    $this->addError('importFile', 'Le fichier n\'est un JSON Valide');
                } else {
                    $jsonData = Json::decode($contentJson);
                    $columns = ($jsonData['columns']) ?? [];
                    unset($jsonData['columns']);
                    $this->attributes = $jsonData;
                    $this->truncateTable = (int)$this->truncateTable;
                    $this->version = $this->checkVersion($this->name, $this->version);
                    if ($this->validate() === true) {
                        $this->save();
                        $this->refresh();
                    } else {
                        $valid = false;
                    }
                }
                unlink($finalPathFile);
            }
            return $valid;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    protected function checkVersion($name, $version)
    {
        try {
            $importConfig = ImportConfig::find()
                ->where(['name' => $name, 'version' => $version])
                ->one();
            if ($importConfig !== null) {
                $newVersion = $version + 1;
                return $this->checkVersion($name, $newVersion);
            }
            return $version;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }



    /**
     * Gets query for [[ImportJobs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImportJobs()
    {
        return $this->hasMany(ImportJob::class, ['importConfigId' => 'id']);
    }
}
