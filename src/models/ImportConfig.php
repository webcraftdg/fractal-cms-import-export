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
use fractalCms\importExport\interfaces\DbView as DbViewInterface;
use fractalCms\importExport\db\SqlIterator;
use fractalCms\importExport\estimations\ExportEstimator;
use fractalCms\importExport\estimations\ExportLimiter;
use fractalCms\importExport\exceptions\ImportError;
use fractalCms\importExport\interfaces\RowImportTransformer;
use fractalCms\importExport\services\RowTransformer as RowTransformerService;
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
 * @property int $stopOnError
 * @property string $type
 * @property string $exportFormat
 * @property int|null $truncateTable
 * @property string $table
 * @property resource|null $sql
 * @property resource|null $rowTransformer
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
    const SCENARIO_IMPORT_JSON_FILE = 'importJsonFile';
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
    public $importConfigId;

    public $tmpColumns = [];

    protected Parameter $parameter;
    protected ExportLimiter $exportLimiter;
    protected ?DbViewInterface $dbView = null;

    public function init()
    {
        parent::init();
        if (Yii::$app->has('importDbParameters') === true) {
            $this->parameter = Yii::$app->importDbParameters;
        }
        if (Yii::$app->has('exportLimiter') === true) {
            $this->exportLimiter = Yii::$app->exportLimiter;
        }
        if (Yii::$container->has(DbView::class) === true) {
            $this->dbView = Yii::$container->get(DbView::class);
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
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'stopOnError', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql', 'rowTransformer', 'exportFormat', 'exportTarget', 'type'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'stopOnError', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql', 'rowTransformer', 'exportFormat', 'exportTarget', 'type'
        ];

        $scenarios[self::SCENARIO_IMPORT_JSON_FILE] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'stopOnError', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql', 'rowTransformer', 'exportFormat', 'exportTarget', 'type'
        ];

        $scenarios[self::SCENARIO_MANAGE_COLUMN] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'stopOnError', 'importFile', 'truncateTable', 'table', 'tmpColumns', 'sql', 'rowTransformer', 'exportFormat', 'exportTarget', 'type'
        ];
        $scenarios[self::SCENARIO_IMPORT_EXPORT] = [
            'importFile', 'importConfigId'
        ];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'version', 'dateCreate', 'dateUpdate', 'table', 'sql', 'rowTransformer', 'exportFormat', 'exportTarget', 'type'], 'default', 'value' => null],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['active', 'stopOnError', 'truncateTable'], 'default', 'value' => 0],
            [['active', 'stopOnError', 'version', 'truncateTable'], 'integer'],
            [['importConfigId'], 'required', 'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['importFile'],
                'required',
                'message' => 'Veuillez télécharger un fichier',
                'on' => [self::SCENARIO_IMPORT_JSON_FILE],
            ],
            [['importFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => ['json'],
                'checkExtensionByMimeType' => false,
                'maxFiles' => 1,
                'message' => 'Le fichier doit être au format JSON',
                'on' => [self::SCENARIO_IMPORT_JSON_FILE],
            ],
            [['importFile'] ,
                'valideTypeImportTest',
                'on' => [self::SCENARIO_IMPORT_EXPORT]
            ],
            [['importFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => ['xlsx', 'xls', 'csv'],
                'checkExtensionByMimeType' => false,
                'maxFiles' => 1,
                'message' => 'Le fichier doit être au format Xlsx, Xls, CSV',
                'on' => [self::SCENARIO_IMPORT_EXPORT],
                'when' => function() {
                    return $this->type === static::TYPE_IMPORT;
                }
            ],
            [['version', 'type'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['importConfigId'], 'required', 'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['importConfigId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ImportConfig::class,
                'targetAttribute' => ['importConfigId' => 'id'],
                'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['importConfigId'], 'validateLimit', 'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['name'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name'], 'match', 'pattern' => '/^[a-z][a-z0-9_]{0,63}$/i', 'message' => 'Le nom n\'accepte pas les caractères spéciaux (éè-#@!àç&).'
                , 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE, self::SCENARIO_IMPORT_JSON_FILE]],
            [['type', 'table', 'sql', 'rowTransformer',], 'string'],
            [['table'] , 'required', 'message' => 'La table ou le SQL doit-être valorisé', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return empty($this->sql) === true;
            }],
            [['sql'] , 'required', 'message' => 'La table ou le SQL doit-être valorisé', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return empty($this->table) === true;
            }],
            [['sql'], 'validateSql','message' => 'Le SQL doit être conforme (uniquement verb "SELECT")', 'on' => [self::SCENARIO_IMPORT_JSON_FILE, self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['table'], 'validateTable','message' => 'La table doit être présente dans votre base de données', 'on' => [self::SCENARIO_IMPORT_JSON_FILE, self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name'], 'string', 'max' => 150],
            [['exportFormat'], 'string', 'max' => 10],
            [['rowTransformer'], 'string', 'max' => 15],
            ['exportFormat', 'in', 'range' => array_keys(self::optsFormats())],
            ['exportTarget', 'in', 'range' => array_keys(self::optsTargets())],
            [['exportTarget'] , 'required', 'message' => 'La cible de l\'export doit-être valorisé avec un valeur SQL', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return empty($this->sql) === false;
            }],
            [['name', 'version'], 'unique', 'targetAttribute' => ['name', 'version'],'message' => 'name-version doit être unique'],
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool
     * @throws Exception
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
                $this->addError('importConfigId', $message);
                $success = false;
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
     * @throws Exception
     */
    public function valideTypeImportTest($attribute, $params) : bool
    {
        try {
            $importConfig = ImportConfig::findOne($this->importConfigId);
            $success = true;
            if ($importConfig !== null) {
                $this->type = $importConfig->type;
                if ($importConfig->type === ImportConfig::TYPE_IMPORT && $importConfig->importFile === null) {
                    $this->addError('importFile', 'Le fichier est obligatoire en mode "import"');
                    $success = false;
                }
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
     * @param DbViewInterface $dbView
     * @return void
     * @throws Exception
     */
    public function buildDbView(DbViewInterface $dbView) : void
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

    /**
     * @param DbViewInterface $dbView
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\Exception
     * @throws \yii\di\NotInstantiableException
     */
    public function buildInitColumns(DbViewInterface $dbView) : array
    {
        try {
            $columns = $this->getContextColumns($dbView);
            return $this->manageColumns($columns);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * Get context columns
     *
     * @param DbViewInterface $dbView
     * @return array
     * @throws \yii\base\NotSupportedException
     */
    public function getContextColumns(DbViewInterface $dbView) : array
    {
        try {
            $values = [];
            $name = $this->getContextName();
            $columns =  $dbView->getTableColumns($name);
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
            'stopOnError' => 'stopOnError',
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
                        $errorsColumns = $this->manageColumns($columns);
                        if (empty($errorsColumns) === true) {
                            $transaction->commit();
                        } else {
                            $transaction->rollBack();
                            /** @var ImportError $errorsColumn */
                            foreach ($errorsColumns as $errorsColumn) {
                                $this->addError('tmpColumns', $errorsColumn->message);
                                break;
                            }
                            $valid = false;
                        }
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
     * @param array $columns
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\di\NotInstantiableException
     */
    public function manageColumns(array $columns) : array
    {
        try {
            $index = 0;
            $errors = [];
            foreach ($columns as $orderColumn => $column) {
                $importColumn = null;
                if (isset($column['id']) === true) {
                    /** @var ImportConfigColumn $importColumn */
                    $importColumn = ImportConfigColumn::findOne($column['id']);
                }
                if ($importColumn === null) {
                    $importColumn = Yii::createObject(ImportConfigColumn::class);
                    $importColumn->scenario = ImportConfigColumn::SCENARIO_CREATE;
                    $importColumn->importConfigId = $this->id;
                } else {
                    $importColumn->scenario = ImportConfigColumn::SCENARIO_UPDATE;
                }
                $transformer = null;
                $transformerOptions = null;
                if (isset($column['transformer']) === true && empty($column['transformer']['name']) === false) {
                    $transformerOptions = ($column['transformerOptions']) ?? [];
                    list($transformer, $transformerOptions) = $importColumn->buildTransformer($column['transformer'], $transformerOptions);
                    if ($transformer !== null) {
                        $transformer = Json::encode($transformer);
                    }
                    if ($transformerOptions !== null) {
                        $transformerOptions = Json::encode($transformerOptions);
                    }
                }
                unset($column['transformer']);
                unset($column['transformerOptions']);
                $importColumn->attributes = $column;
                $importColumn->transformer = $transformer;
                $importColumn->transformerOptions = $transformerOptions;
                if (empty($importColumn->order) === true) {
                    $importColumn->order = ($index + 0.5);
                } else {
                    $index = $importColumn->order;
                }
                if ($importColumn->validate() === true) {
                    $importColumn->save();
                } else {
                    foreach ($importColumn->errors as $field => $error) {
                        $errors[] = new ImportError(
                            rowNumber: $orderColumn,
                            column: $field,
                            message: 'Colonne : '.$importColumn->source.':'.$importColumn->getFirstError($field),
                            level: ImportError::LEVEL_VALIDATION_ERROR
                        );
                    }
                }

                if ($this->dbView instanceof DbViewInterface) {
                    $tableName = $this->getContextName();
                    $columnExist = $this->dbView->columnExists($tableName, $importColumn->source);
                    if ($columnExist === false && empty($this->rowTransformer) === true) {
                        $errors[] = new ImportError(
                            rowNumber: $orderColumn,
                            column: $importColumn->source,
                            message: 'Colonne : '.$importColumn->source.': Colonne externe RowTransfomer obligatoire',
                            level: ImportError::LEVEL_ERROR
                        );
                    }
                }


            }
            if (empty($errors) === true) {
                $this->reorderColumns();
            }
            return $errors;
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
                if ($this->importFile instanceof UploadedFile && $targetModel->type === static::TYPE_IMPORT) {
                    $finalPathFile = $modulePath.'/'. $this->importFile->baseName . '.' . $this->importFile->extension;
                    $this->importFile->saveAs($finalPathFile);
                    $importJob = Import::run($targetModel, $finalPathFile, true);
                    unlink($finalPathFile);
                } elseif ($targetModel->type === static::TYPE_EXPORT) {
                    $importJob = Export::run($targetModel);
                }
            } else {
                $this->addError('importFile', 'Merci de télécharger un fichier');
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
     * @return RowImportTransformer|null
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getRowTransformer() : RowImportTransformer | null
    {
        try {
            $rowTransformer = null;
            $rowTransformerService = (
            Yii::$container->has(RowTransformerService::class)
            ) ? Yii::$container->get(RowTransformerService::class) : null;
            if ($rowTransformerService !== null) {
                $rowTransformer = $rowTransformerService->get($this->rowTransformer);
            }
            return $rowTransformer;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
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
