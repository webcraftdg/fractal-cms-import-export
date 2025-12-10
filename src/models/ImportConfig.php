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

use Exception;
use fractalCms\importExport\db\DbView;
use fractalCms\importExport\db\SqlIterator;
use fractalCms\importExport\estimations\ExportEstimator;
use fractalCms\importExport\estimations\ExportLimiter;
use fractalCms\importExport\Module;
use fractalCms\importExport\services\Export;
use fractalCms\importExport\services\Import;
use fractalCms\importExport\services\Parameter;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\db\Query;
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
 * @property string|null $exportTarget
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

    const TARGET_SQL = 'sql';
    const TARGET_VIEW = 'view';

    public $importFile;
    public $type;
    public $importConfigId;

    public $tmpColumns = [];

    protected Parameter $parameter;
    protected ExportLimiter $exportLimiter;

    public function init()
    {
        parent::init();
        if (Yii::$app->has('importDbParameters') === true) {
            $this->parameter = Yii::$app->importDbParameters;
        }
        if (Yii::$app->has('exportLimiter') === true) {
            $this->exportLimiter = Yii::$app->exportLimiter;
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
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql','exportFormat', 'exportTarget'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql','exportFormat', 'exportTarget'
        ];

        $scenarios[self::SCENARIO_IMPORT_FILE] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql','exportFormat', 'exportTarget'
        ];

        $scenarios[self::SCENARIO_MANAGE_COLUMN] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql','exportFormat', 'exportTarget'
        ];
        $scenarios[self::SCENARIO_IMPORT_EXPORT] = [
            'type', 'importFile', 'importConfigId'
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
            [['name', 'version', 'dateCreate', 'dateUpdate', 'table', 'sql', 'exportFormat', 'exportTarget'], 'default', 'value' => null],
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
            [['type', 'importConfigId'], 'required', 'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['importConfigId'], 'required', 'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['importConfigId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ImportConfig::class,
                'targetAttribute' => ['importConfigId' => 'id'],
                'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['importConfigId'], 'validateLimit', 'on' => [self::SCENARIO_IMPORT_EXPORT]],
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
            ['exportTarget', 'in', 'range' => array_keys(self::optsTargets())],
            [['exportTarget'] , 'required', 'message' => 'La cible de l\'export doit-être valorisé avec un valeur SQL', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return empty($this->sql) === false;
            }],
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
                $dbTables = $this->parameter->getActiveModelTableNames();
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
     * @param $attribute
     * @param $params
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function validateLimit($attribute, $params) : bool
    {
        try {
            $importConfig = ImportConfig::findOne($this->importConfigId);
            $success = true;
            $message = null;
            if ($importConfig !== null) {
                $limitModel = $importConfig->getLimits();
                $limitModel->name = $importConfig->name;
                $message = $this->exportLimiter->assertAllowed($limitModel);
            }
            if(empty($message) === false) {
                $this->addError('type', $message);
                $success = false;
            }
            return $success;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }


    /**
     * @return LimiterModel
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function getLimits() : LimiterModel
    {
        try {
            $limiter = Yii::createObject(LimiterModel::class);
            $limiter->scenario = LimiterModel::SCENARIO_CREATE;
            $limiter->rows = ExportEstimator::estimateRows($this);
            $limiter->format = $this->exportFormat;
            $limiter->columns = ExportEstimator::estimateColumns($this);
            $limiter->estimatedMb = ExportEstimator::estimateSizeMb($limiter->rows, $limiter->columns);
            return $limiter;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }
    /**
     * @param DbView $dbView
     * @return void
     * @throws Exception
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
     * @return Query
     * @throws \yii\db\Exception
     */
    public function getImportExportQueryDb() : Query
    {
        try {
            $cols = $this->buildConfigColumns();
            $statementName = $this->getContextName();
            $query = new Query();
            $query->select($cols);
            $query->from($statementName);
            return  $query;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * @param $batchSize
     * @return SqlIterator
     * @throws Exception
     */
    public function getImportExportQueryIterator($batchSize = 1000) : SqlIterator
    {
        try {
            return  new SqlIterator($this->sql, $batchSize);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Build column config list
     *
     * @param bool $isSource
     * @return array
     * @throws Exception
     */
    public function buildConfigColumns(bool $isSource = true) : array
    {
        try {
            $cols = [];
            $query = $this->getImportColumns();
            /** @var ImportConfigColumn $importColumn */
            foreach ($query->each() as $importColumn) {
                $col = $importColumn->source;
                if ($isSource === false) {
                    $col = $importColumn->target;
                }
                $cols[] = $col;
            }
            return $cols;
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

    /**
     * Tarfet enum
     *
     * @return string[]
     */
    public static function optsTargets()
    {
        return [
            self::TARGET_SQL => 'SQL',
            self::TARGET_VIEW => 'VIEW',
        ];
    }
    /**
     * @return string[]
     */
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
            $index = 0;
            foreach ($columns as $column) {
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
                $importColumn->attributes = $column;
                if (empty($importColumn->order) === true) {
                    $importColumn->order = ($index + 0.5);
                } else {
                    $index = $importColumn->order;
                }
                if ($importColumn->validate() === true) {
                    $importColumn->save();
                } else {
                    Yii::debug(Json::encode($importColumn->errors), __FUNCTION__);
                }
            }
            $this->reorderColumns();
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
            $targetModel = ImportConfig::findOne(['id' => $this->importConfigId]);
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
            $name = strtolower($this->name.'_v'.$this->version);
            if(empty($this->table) === false) {
                $name = $this->table;
                if ($this->parameter instanceof Parameter) {
                    $dbTables = $this->parameter->getActiveModelTableNames();
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
        return $this->hasMany(ImportConfigColumn::class, ['importConfigId' => 'id'])
            ->orderBy([ImportConfigColumn::tableName().'.[[order]]' => SORT_ASC]);
    }

    /**
     * Gets query for [[ImportColumns]] with search.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImportColumnsWithSearch(string $search = null)
    {
        $query = $this->getImportColumns();
        if (empty($search) === false) {
            $query->andWhere(['like', ImportConfigColumn::tableName().'.[[source]]', $search]);
        }
        return $query;
    }

    /**
     * Reorder columns
     *
     * @return void
     * @throws \yii\db\Exception
     */
    public function reorderColumns() : void
    {
        try {
            $query = $this->getImportColumns();
            /**
             * @var int $index
             * @var ImportConfigColumn $column
             */
            foreach ($query->each() as $index => $column) {
                $column->order = $index;
                $column->save(false, ['order']);
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
