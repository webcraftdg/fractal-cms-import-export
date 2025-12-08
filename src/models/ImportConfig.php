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
use fractalCms\importExport\services\DbView;
use fractalCms\importExport\services\Export;
use fractalCms\importExport\services\Import;
use fractalCms\importExport\services\Parameter;
use yii\behaviors\TimestampBehavior;
use yii\db\DataReader;
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
 * @property string $exportFormat
 * @property int|null $truncateTable
 * @property string $table
 * @property resource|null $sql
 * @property string|null $dateCreate
 * @property string|null $dateUpdate
 *
 * @property ImportJob[] $importJobs
 * @property ImportConfigColumn[] $importColumns;
 */
class ImportConfig extends \yii\db\ActiveRecord
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_IMPORT_FILE = 'importFile';
    const SCENARIO_MANAGE_COLUMN = 'manageColumn';
    const SCENARIO_IMPORT_EXPORT = 'importExport';
    const FORMAT_EXCEL = 'xls';
    const FORMAT_EXCEL_X = 'xlsx';
    const FORMAT_CSV = 'csv';

    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';

    public $importFile;
    public $type;
    public $testModelId;

    public $tmpColumns = [];

    protected Parameter $parameter;

    public function init()
    {
        parent::init();
        if (Yii::$app->has('importDbParameters') === true) {
            $this->parameter = Yii::$app->importDbParameters;
        }
    }

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
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql','exportFormat'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql','exportFormat'
        ];

        $scenarios[self::SCENARIO_IMPORT_FILE] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql','exportFormat'
        ];

        $scenarios[self::SCENARIO_MANAGE_COLUMN] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql','exportFormat'
        ];
        $scenarios[self::SCENARIO_IMPORT_EXPORT] = [
            'type', 'importFile', 'testModelId'
        ];
        return $scenarios;
    }


    /**
     * @inheritDoc
     */
    public function afterFind()
    {
        parent::afterFind();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'version', 'dateCreate', 'dateUpdate', 'table', 'sql', 'exportFormat'], 'default', 'value' => null],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['active', 'truncateTable'], 'default', 'value' => 0],
            [['active', 'version', 'truncateTable'], 'integer'],
            [['importFile'],
                'required',
                'message' => 'Veuillez télécharger un fichier',
                'on' => [self::SCENARIO_IMPORT_FILE],
            ],
            [['importFile'] ,
                'required',
                'message' => 'Le fichier est obligatoire en type IMPORT',
                'on' => [self::SCENARIO_IMPORT_EXPORT],
                'when' => function () {
                return $this->type === static::TYPE_IMPORT;
            }],
            [['importFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => ['json'],
                'checkExtensionByMimeType' => false,
                'maxFiles' => 1,
                'message' => 'Le fichier doit être au format JSON',
                'on' => [self::SCENARIO_IMPORT_FILE],
            ],
            [['importFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => ['xlsx', 'xls', 'csv'],
                'checkExtensionByMimeType' => false,
                'maxFiles' => 1,
                'message' => 'Le fichier doit être au format Xlsx, Xls, CSV',
                'on' => [self::SCENARIO_IMPORT_EXPORT],
                'when' => function () {
                    return $this->type === static::TYPE_IMPORT;
                }
            ],
            [['version'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['type', 'testModelId'], 'required', 'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['name'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name'], 'match', 'pattern' => '/^[a-z][a-z0-9_]{0,63}$/i', 'message' => 'Le nom n\'accepte pas les caractères spéciaux (éè-#@!àç&).', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['type', 'table', 'sql'], 'string'],
            [['table'] , 'required', 'message' => 'La table ou le SQL doit-être valorisé', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return empty($this->sql) === true;
            }],
            [['sql'] , 'required', 'message' => 'La table ou le SQL doit-être valorisé', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return empty($this->table) === true;
            }],
            [['sql'], 'validateSql','message' => 'Le SQL doit être conforme (uniquement verb "SELECT")', 'on' => [self::SCENARIO_IMPORT_FILE, self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['table'], 'validateTable','message' => 'La table doit être présente dans votre base de données', 'on' => [self::SCENARIO_IMPORT_FILE, self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name'], 'string', 'max' => 150],
            [['exportFormat'], 'string', 'max' => 10],
            ['exportFormat', 'in', 'range' => array_keys(self::optsFormats())],
            [['name', 'version'], 'unique', 'targetAttribute' => ['name', 'version'],'message' => 'name-version doit être unique'],
        ];
    }

    /**
     * Validate attributes
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function validateTable($attribute, $params) : bool
    {
        try {
            $table = $this->table;
            $dbTables = [];
            if ($this->parameter instanceof Parameter) {
                $dbTables = $this->parameter->getTables();
            }
            $success = in_array($table, array_keys($dbTables));
            if( $success === false) {
                $this->addError('table', 'La table : "'.$table.'" doit être présente dans votre base de données');
            }
            return $success;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Validate SQL
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function validateSql($attribute, $params) : bool
    {
        try {
            $db = Yii::$app->db;
            $success = true;
            $sql = $this->sql;
            $dbTables = $db->schema->tableNames;
            $tablesAvailables = implode('|', $dbTables);
            $message = '';
            // 1. SELECT obligatoire
            if (preg_match('/^\s*SELECT\s+/i', $sql) === false) {
                $message .= 'Seules les requêtes SELECT sont autorisées. ';
            }

            // 2. Blocage des commandes interdites
            if (preg_match('/\b(INSERT|UPDATE|DELETE|DROP|TRUNCATE|ALTER|CREATE|REPLACE|LOAD|GRANT|REVOKE)\b/i', $sql) === 1) {
                $message .= ' Commande SQL dangereuse détectée.';
            }
            //SEcurise les tables
            if (!preg_match('/\bFROM\s+'.$tablesAvailables.'\b/i', $sql)) {
                $message .= ' La requête doit cibler les tables existantes.';
            }
            // 3. Blocage des INTO / commentaires / injections
            $danger = [
                '/\bINTO\b/i',
                '/(--|#|\/\*)/',
                '/;.*\S/',
            ];

            foreach ($danger as $rule) {
                if (preg_match($rule, $sql) === 1) {
                    $message .= ' Contenu SQL interdit.';
                    break;
                }
            }
            //TEST SQL
            try {
                $stmtName = 'validate_stmt_' . uniqid();
                // PREPARE
                $db->createCommand('PREPARE '.$stmtName.' FROM :escapedSql', [':escapedSql' => $sql])->execute();
                // DEALLOCATE
                $db->createCommand('DEALLOCATE PREPARE '.$stmtName)->execute();
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $message .= ' Requête SQL invalide';
            }

            if(empty($message) === false) {
                $success = false;
                $this->addError('sql', $message);
            }

            return $success;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Build db view
     *
     * @return void
     */
    public function buildDbView(DbView $dbView) : void
    {
        try {
            if (empty($this->sql) === false) {
                $name = $this->getContextName();
                $dbView->create($name, $this->sql);
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    public function buildInitColumns(DbView $dbView) : void
    {
        try {
            $columns = $this->getContextColumns($dbView);
            $this->manageColumns($columns);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Get context columns
     *
     * @param DbView $dbView
     * @return array
     * @throws \yii\base\NotSupportedException
     */
    public function getContextColumns(DbView $dbView) : array
    {
        try {
            $values = [];
            $name = $this->getContextName();
            $columns =  $dbView->getColumns($name);
            /**
             * @var string $id
             * @var ColumnModel $params
             */
            foreach ($columns as $params) {
                $values[] = $params->toArray();
            }
            return $values;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }


    /**
     * @return DataReader
     * @throws \yii\db\Exception
     */
    public function getImportExportQuery() : DataReader
    {
        try {
            $sql = $this->sql;
            if(empty($sql) === true) {
                $cols = array_map(function($config) {
                    return $config['source'];
                }, $this->tmpColumns);
                $table = $this->getContextName();
                $sql = "SELECT " . implode(',', $cols) . " FROM " . $table;
            }
            return Yii::$app->db->createCommand($sql)->query();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
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
            'dateCreate' => 'Date Create',
            'dateUpdate' => 'Date Update',
        ];
    }

    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsFormats()
    {
        return [
            self::FORMAT_EXCEL_X => 'Xlsx',
            self::FORMAT_EXCEL => 'Xls',
            self::FORMAT_CSV => 'csv',
        ];
    }

    public static function optsTypes()
    {
        return [
            self::TYPE_IMPORT => 'Import',
            self::TYPE_EXPORT => 'Export',
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
                    $transaction = Yii::$app->db->beginTransaction();
                    $jsonData = Json::decode($contentJson);
                    $columns = ($jsonData['columns']) ?? [];
                    unset($jsonData['columns']);
                    $this->attributes = $jsonData;
                    $this->truncateTable = (int)$this->truncateTable;
                    $this->version = $this->checkVersion($this->name, $this->version);
                    if ($this->validate() === true) {
                        $this->save();
                        $this->refresh();
                        $this->manageColumns($columns);
                        $transaction->commit();
                    } else {
                        $valid = false;
                        $transaction->rollBack();
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


    /**
     * Manage columns
     *
     * @param array $columns
     * @return void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function manageColumns(array $columns)
    {
        try {

            foreach ($columns as $index => $column) {
                $importColumn = null;
                if (isset($column['id']) === true) {
                    $importColumn = ImportConfigColumn::findOne($column['id']);
                }
                if ($importColumn === null) {
                    $importColumn = Yii::createObject(ImportConfigColumn::class);
                    $importColumn->scenario = ImportConfigColumn::SCENARIO_CREATE;
                    $importColumn->importConfigId = $this->id;
                } else {
                    $importColumn->scenario = ImportConfigColumn::SCENARIO_UPDATE;
                }
                $importColumn->order = $index ++;
                $importColumn->attributes = $column;
                if ($importColumn->validate() === true) {
                    $importColumn->save();
                } else {
                    Yii::debug(Json::encode($importColumn->errors), __FUNCTION__);
                }
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @return ImportJob|null
     * @throws \yii\db\Exception
     */
    public function manageImportExport() : ImportJob | null
    {
        try {
            $modulePath = Yii::getAlias(Module::getInstance()->filePathImport);
            $importJob = null;
            $targetModel = ImportConfig::findOne(['id' => $this->testModelId]);
            if($targetModel !== null) {
                if ($this->importFile instanceof UploadedFile && $this->type === static::TYPE_IMPORT) {
                    $finalPathFile = $modulePath.'/'. $this->importFile->baseName . '.' . $this->importFile->extension;
                    $this->importFile->saveAs($finalPathFile);
                    $importJob = Import::run($targetModel, $finalPathFile, true);
                    unlink($finalPathFile);
                } elseif ($this->type === static::TYPE_EXPORT) {
                    $importJob = Export::run($targetModel);
                }
            } else {
                $this->addError('name', 'Config non trouvé');
            }
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * Check available version
     *
     * @param $name
     * @param $version
     * @return int
     * @throws Exception
     */
    public function checkVersion($name, $version) : int
    {
        try {
            $importConfig = ImportConfig::find()
                ->where(['name' => $name, 'version' => $version])
                ->one();
            if ($importConfig !== null) {
                $newVersion = (int)$version + 1;
                return $this->checkVersion($name, $newVersion);
            }
            return (int)$version;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getContextName() :string
    {
        try {
            $name = $this->name.'_'.$this->version;
            if(empty($this->table) === false) {
                $name = $this->table;
                if ($this->parameter instanceof Parameter) {
                    $dbTables = $this->parameter->getTables();
                    $name = (isset($dbTables[$this->table]) === true) ? $dbTables[$this->table] : $this->table;
                }
            }
            return $name;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function fields()
    {
        $data =  parent::fields();
        return $data;
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

    /**
     * Gets query for [[ImportColumns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImportColumns()
    {
        return $this->hasMany(ImportConfigColumn::class, ['importConfigId' => 'id']);
    }
}
