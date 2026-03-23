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

use fractalCms\importExport\db\DbView;
use fractalCms\importExport\interfaces\DbView as DbViewInterface;
use fractalCms\importExport\interfaces\RowExportTransformer;
use fractalCms\importExport\estimations\ExportEstimator;
use fractalCms\importExport\estimations\ExportLimiter;
use fractalCms\importExport\exceptions\ImportError;
use fractalCms\importExport\interfaces\DataReader;
use fractalCms\importExport\interfaces\RowExportProcessor;
use fractalCms\importExport\interfaces\RowImportProcessor;
use fractalCms\importExport\interfaces\RowImportTransformer;
use fractalCms\importExport\Module;
use fractalCms\importExport\services\Export;
use fractalCms\importExport\services\exports\readers\QueryDataReader;
use fractalCms\importExport\services\exports\readers\SqlDataReader;
use fractalCms\importExport\services\Import;
use fractalCms\importExport\services\Parameter;
use fractalCms\importExport\services\RowProcessor;
use yii\web\Application;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Json;
use yii\web\UploadedFile;
use Exception;
use fractalCms\importExport\interfaces\WriterInterface;
use fractalCms\importExport\services\exports\writers\CsvWriter;
use fractalCms\importExport\services\exports\writers\JsonWriter;
use fractalCms\importExport\services\exports\writers\XlsxWriter;
use fractalCms\importExport\services\exports\writers\XmlWriter;
use InvalidArgumentException;
use Yii;

/**
 * This is the model class for table "importConfigs".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $version
 * @property int|null $active
 * @property int $stopOnError
 * @property string $sourceType
 * @property string $type
 * @property string $fileFormat
 * @property int|null $truncateTable
 * @property string $table
 * @property resource|null $sql
 * @property resource|null $rowProcessor
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
    const FORMAT_XML = 'xml';
    const FORMAT_JSON = 'json';

    const SOURCE_TYPE_SQL = 'sql';
    const SOURCE_TYPE_TABLE = 'table';
    const SOURCE_TYPE_EXTERN = 'extern';
    
    
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

    private array $columnsByNames = [];

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
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'stopOnError', 'importFile', 'truncateTable', 'table', 'tmpColumns',
            'sql', 'rowProcessor', 'fileFormat', 'exportTarget', 'type', 'sourceType'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'stopOnError', 'importFile', 'truncateTable', 'table', 'tmpColumns',
            'sql', 'rowProcessor', 'fileFormat', 'exportTarget', 'type', 'sourceType'
        ];

        $scenarios[self::SCENARIO_IMPORT_JSON_FILE] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'stopOnError', 'importFile', 'truncateTable', 'table', 'tmpColumns',
            'sql', 'rowProcessor', 'fileFormat', 'exportTarget', 'type', 'sourceType'
        ];

        $scenarios[self::SCENARIO_MANAGE_COLUMN] = [
            'name', 'version', 'dateCreate', 'dateUpdate', 'active', 'stopOnError', 'importFile', 'truncateTable', 'table', 'tmpColumns',
            'sql', 'rowProcessor', 'fileFormat', 'exportTarget', 'type', 'sourceType'
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
            [['name', 'version', 'dateCreate', 'dateUpdate', 'table', 'sql', 'rowProcessor', 'fileFormat', 'exportTarget', 'type', 'sourceType'], 'default', 'value' => null],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['active', 'stopOnError', 'truncateTable'], 'default', 'value' => 0],
            [['active', 'stopOnError', 'version', 'truncateTable'], 'integer'],
            [['importConfigId'], 'required', 'on' => [self::SCENARIO_IMPORT_EXPORT]],
            [['sourceType', 'fileFormat'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
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
                'extensions' => ['xlsx', 'xls', 'csv', 'xml', 'json'],
                'checkExtensionByMimeType' => false,
                'maxFiles' => 1,
                'message' => 'Le fichier doit être au format Xlsx, Xls, CSV, XML, JSON',
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
            [['type', 'table', 'sql', 'rowProcessor',], 'string'],
            [['table'] , 'required', 'message' => 'La table est obligatoire lorsque la source des données est TABLE ou EXTERNE pour une configuration en IMPORT', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return $this->sourceType === self::SOURCE_TYPE_TABLE || ($this->type ===self::TYPE_IMPORT && $this->sourceType === self::SOURCE_TYPE_EXTERN);
            }],
            [['sql'] , 'required', 'message' => 'Le SQL est obligatoire lorsque la source des données est SQL', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return $this->sourceType === self::SOURCE_TYPE_SQL;
            }],
            [['sql'], 'validateSql','message' => 'Le SQL doit être conforme (uniquement verb "SELECT")', 'on' => [self::SCENARIO_IMPORT_JSON_FILE, self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['table'], 'validateTable','message' => 'La table doit être présente dans votre base de données', 'on' => [self::SCENARIO_IMPORT_JSON_FILE, self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name'], 'string', 'max' => 150],
            [['fileFormat'], 'string', 'max' => 10],
            [['rowProcessor'], 'string', 'max' => 15],
            [['rowProcessor'] , 'required', 'message' => 'Le convertisseur métier est obligatoire lorsque la source des données est EXTERNE', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return $this->sourceType === self::SOURCE_TYPE_EXTERN && $this->type === self::TYPE_EXPORT;
            }],
            ['fileFormat', 'in', 'range' => array_keys(self::optsFormats())],
            ['exportTarget', 'in', 'range' => array_keys(self::optsTargets())],
            ['sourceType', 'in', 'range' => array_keys(self::optsSourceTypes()), 'message' => 'Une configuration EXPORT peut avoir : '.implode(',', self::optsSourceTypes()), 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return $this->type === self::TYPE_EXPORT;
            }],
            ['sourceType', 'in', 'range' => [self::SOURCE_TYPE_EXTERN], 'message' => 'Une configuration IMPORT doit avoir une source des données EXTERNE', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return $this->type === self::TYPE_IMPORT;
            }],
            [['exportTarget'] , 'required', 'message' => 'La cible de l\'export doit-être valorisé avec un valeur SQL', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE], 'when' => function () {
                return $this->sourceType === self::SOURCE_TYPE_SQL;
            }],
            ['exportTarget', 'validateExportTarget', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name', 'version'], 'unique', 'targetAttribute' => ['name', 'version'],'message' => 'name-version doit être unique'],
        ];
    }


    /**
     * @inheritDoc
     */
    public function afterFind()
    {
         try {
                foreach($this->importColumns as $dbColumn) {
                    $this->columnsByNames[$dbColumn->source] = $dbColumn;
                    $this->columnsByNames[$dbColumn->target] = $dbColumn;
                }
              return parent::afterFind();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }


    /**
     * findColumnByName
     *
     * @param  string                  $search
     *
     * @return ImportConfigColumn|null
     */
    public function findColumnByName(string $search) : ?ImportConfigColumn
    {
        return ($this->columnsByNames[$search]) ?? null;
    }

    

   
    /**
     * validate Export Target
     *
     * @param  [type] $attribute
     * @param  [type] $params
     *
     * @return bool
     */
    public function validateExportTarget($attribute, $params) : bool
    {
        try {
            $success = true;
            $exportTarget = $this->exportTarget;
            if (empty($this->sql) === true) {
                $this->addError('sql', 'La requête SQL doit-être valorisé si le mode de calcul des données à exporter est SQL ou VIEW');
                $success = false;
            }
            if ($this->sourceType === self::SOURCE_TYPE_EXTERN) {
                $this->addError('exportTarget', 'Le mode de calcul des données doit-être vide si la source des données est EXTERNE');
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
                if ($this->type === ImportConfig::TYPE_IMPORT && $this->importFile === null) {
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
            $limiter->format = ($this->fileFormat) ?? self::FORMAT_CSV;
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
            if (empty($this->sql) === false && $this->exportTarget === self::TARGET_VIEW) {
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
                $value = $params->toArray();
                if ($this->type === self::TYPE_IMPORT) {
                    $value['target'] = ucfirst($value['target']);
                } else {
                    $value['source'] = ucfirst($value['source']);
                }
                $values[] = $value;
            }
            return $values;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }


    /**
     * @param int $batchSize
     * @return DataReader|null
     * @throws Exception
     */
    public function getDataReader(int $batchSize = 1000) : DataReader | null
    {
        try {
            $dataReader = null;
            if ($this->sourceType === self::SOURCE_TYPE_TABLE) {
                $cols = $this->buildConfigColumns();
                $statementName = $this->getContextName();
                $query = new Query();
                $query->select($cols);
                $query->from($statementName);
                $dataReader = new QueryDataReader();
                $dataReader->open(['query' => $query, 'batchSize' => $batchSize]);
            } elseif($this->sourceType === self::SOURCE_TYPE_SQL) {
                $dataReader = new SqlDataReader();
                $dataReader->open(['command' => Yii::$app->db->createCommand($this->sql)]);
            }
            return  $dataReader;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * createWriter
     *
     * @return WriterInterface
     */
    public function createWriter() : WriterInterface
    {
        try {
            switch ($this->fileFormat) {
                case ImportConfig::FORMAT_CSV: 
                    $writer = new CsvWriter();
                    break;
                case ImportConfig::FORMAT_EXCEL:
                case ImportConfig::FORMAT_EXCEL_X: 
                    $writer = new XlsxWriter();
                    break;
                case ImportConfig::FORMAT_JSON:
                    $writer = new JsonWriter($this);
                    break;
                case ImportConfig::FORMAT_XML:
                    $writer = new XmlWriter($this);
                    break;
                default: 
                    throw new InvalidArgumentException('ImportConfig : createWriter, format not found');    
            }
            return $writer;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * getExportFileName
     *
     * @return string
     */
    public function getExportFileName() : string
    {
        try {
            $fileName = 'export_' . date('Ymd_His');
            switch ($this->fileFormat) {
                case ImportConfig::FORMAT_CSV: 
                    $fileName =  $fileName . '.csv';
                    break;
                case ImportConfig::FORMAT_EXCEL:
                case ImportConfig::FORMAT_EXCEL_X: 
                    $fileName =  $fileName . '.xlsx';
                    break;
                case ImportConfig::FORMAT_JSON:
                    $fileName =  $fileName . '.json';
                    break;
                case ImportConfig::FORMAT_XML:
                    $fileName =  $fileName . '.xml';
                    break;
                default: 
                    throw new InvalidArgumentException('ImportConfig : getExportFileName, format not found');    
            }
            return $fileName;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * getExportPreamble
     *
     * @return array
     */
      public function getExportPreamble() : array
    {
        try {
            $preamble = [];
            switch ($this->fileFormat) {
                case ImportConfig::FORMAT_CSV: 
                case ImportConfig::FORMAT_EXCEL:
                case ImportConfig::FORMAT_EXCEL_X:
                    $preamble = $this->buildConfigColumns(false);
                    break;
            }
            return $preamble;
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
            self::FORMAT_CSV => 'Csv',
            self::FORMAT_JSON => 'Json',
            self::FORMAT_EXCEL_X => 'Xlsx',
            self::FORMAT_XML => 'Xml',
        ];
    }

    /**
     * @return string[]
     */
    public static function optsSourceTypes()
    {
        return [
            self::SOURCE_TYPE_EXTERN => 'Externe',
            self::SOURCE_TYPE_TABLE => 'Table',
            self::SOURCE_TYPE_SQL => 'SQL',
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
            $prevIndex= -1;
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
                    $importColumn->transformer = null;
                    $importColumn->transformerOptions = null;
                }
                $importColumn->tmpTransformer = ($column['transformer']) ?? null;
                $importColumn->tmpTransformerOptions = ($column['transformerOptions']) ?? null;
                unset($column['transformer']);
                unset($column['transformerOptions']);
                $importColumn->attributes = $column;
                if (empty($importColumn->order) === true) {
                    $order = ($prevIndex > -1) ? ($prevIndex + 0.5) : $index;
                    $importColumn->order = $order;
                } else {
                    $prevIndex = $importColumn->order;
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
                    $destData = ($this->type === self::TYPE_IMPORT) ? $importColumn->target : $importColumn->source;
                    $columnExist = $this->dbView->columnExists($tableName, $destData);
                    if ($columnExist === false && empty($this->rowProcessor) === true) {
                        $errors[] = new ImportError(
                            rowNumber: $orderColumn,
                            column: $destData,
                            message: 'Colonne : '.$destData.': Pour une Colonne le convertisseur métier est obligatoire',
                            level: ImportError::LEVEL_ERROR
                        );
                    }
                }
                $index++;
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
            if(($this->sourceType === self::SOURCE_TYPE_TABLE || $this->sourceType === self::SOURCE_TYPE_EXTERN && $this->type === self::TYPE_IMPORT) && empty($this->table) === false) {
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
     * @param int $rowsCount
     * @return ImportJob
     * @throws Exception
     */
    public function createImportJob(int $rowsCount = 0) : ImportJob
    {
        try {
            $importJob = new ImportJob(['scenario' => ImportJob::SCENARIO_CREATE]);
            $importJob->importConfigId = $this->id;
            $importJob->userId = (Yii::$app instanceof Application && Yii::$app->user->identity !== null) ? Yii::$app->user->identity->getId() : null;
            $importJob->type = ImportJob::TYPE_EXPORT;
            $importJob->status = ImportJob::STATUS_RUNNING;
            $importJob->totalRows = $rowsCount;
            return $importJob;
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
    public function getImportColumnsWithSearch(string $search)
    {
        $query = $this->getImportColumns();
        if (empty($search) === false) {
            $query->andWhere(['like', ImportConfigColumn::tableName().'.[[source]]', $search]);
        }
        return $query;
    }

    /**
     * getRowProcessor
     * 
     * @return RowImportTransformer|RowExportTransformer|null
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getRowProcessor() : RowImportProcessor | RowExportProcessor | null
    {
        try {
            $rowProcessor = null;
            $rowProcessorService = (Yii::$container->has(RowProcessor::class)
            ) ? Yii::$container->get(RowProcessor::class) : null;
            if ($rowProcessorService !== null && $this->rowProcessor !== null) {
                $rowProcessor = $rowProcessorService->create($this->type, $this->rowProcessor);
            }
            return $rowProcessor;
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

    /**
     * 
     * @param array $row
     * @param boolean $targetToSource
     * 
     * @return array
     * @throws \yii\db\Exception 
     */
    public function revertColumnNames($row, $targetToSource = true) : array
    {
          try {
            $result = [];
            /** @var ImportConfigColumn $column */
            foreach($this->getImportColumns()->each() as $column) {
                $from = ($targetToSource === true) ? $column->target : $column->source;
                $to = ($targetToSource === true) ? $column->source : $column->target;
                $value = $row[$from] ?? null;
                $result[$to] = $value;
            }
            return $result;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
