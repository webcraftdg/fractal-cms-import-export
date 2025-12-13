<?php
/**
 * ImportJob.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\models
 */
namespace fractalCms\importExport\models;

use fractalCms\importExport\exceptions\ImportErrorCollector;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use Exception;
use Yii;

/**
 * This is the model class for table "importJobs".
 *
 * @property int $id
 * @property int|null $importConfigId
 * @property int|null $userId
 * @property string|null $type
 * @property string|null $filePath
 * @property string|null $sql
 * @property int|null $totalRows
 * @property int|null $successRows
 * @property int|null $errorRows
 * @property string $status
 * @property resource|null $errors
 * @property string|null $errorFilePath
 * @property string|null $dateCreate
 * @property string|null $dateUpdate
 *
 * @property ImportConfig $importConfig
 */
class ImportJob extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    /**
     * ENUM field values
     */
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    /**
     * ENUM field values
     */
    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';

    /**
     * @var ImportErrorCollector
     */
    public $errorCollector;

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
        return 'importJobs';
    }

    public function scenarios() : array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'importConfigId', 'userId', 'filePath', 'errors', 'errorFilePath', 'dateCreate', 'dateUpdate', 'totalRows', 'successRows', 'errorRows', 'status', 'sql', 'type'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'importConfigId', 'userId', 'filePath', 'errors', 'errorFilePath', 'dateCreate', 'dateUpdate', 'totalRows', 'successRows', 'errorRows', 'status', 'sql', 'type'
        ];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importConfigId', 'userId', 'filePath', 'errors', 'errorFilePath', 'dateCreate', 'dateUpdate'], 'default', 'value' => null],
            [['errorRows'], 'default', 'value' => 0],
            [['importConfigId', 'userId', 'totalRows', 'successRows', 'errorRows'], 'integer', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['status'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['status', 'errors', 'errorFilePath',], 'string'],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['filePath'], 'string', 'max' => 255],
            [['type'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['type', 'in', 'range' => array_keys(self::optsType()), 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            ['status', 'in', 'range' => array_keys(self::optsStatus()), 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['importConfigId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ImportConfig::class,
                'targetAttribute' => ['importConfigId' => 'id'],
                'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'importConfigId' => 'Import Config ID',
            'userId' => 'User ID',
            'filePath' => 'File Path',
            'totalRows' => 'Total Rows',
            'successRows' => 'Success Rows',
            'errorRows' => 'Error Rows',
            'status' => 'Status',
            'errors' => 'Json errors',
            'dateCreate' => 'Date Create',
            'dateUpdate' => 'Date Update',
        ];
    }

    /**
     * Gets query for [[ImportConfig]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImportConfig()
    {
        return $this->hasOne(ImportConfig::class, ['id' => 'importConfigId']);
    }

    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_PENDING => 'pending',
            self::STATUS_RUNNING => 'running',
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAILED => 'failed',
        ];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function saveFileErrorCsv()
    {
        try {
            $filename = 'import_rapport_error_importLogId_'.$this->id. date('Ymd_His') . '.csv';
            $path = Yii::getAlias('@runtime') . '/import_file_errors';
            if (file_exists($path) === false) {
                mkdir($path);
            }
            $pathFile =  Yii::getAlias('@runtime') . '/import_file_errors/'.$filename;
            if($this->errorCollector instanceof ImportErrorCollector) {
                $csvRows = $this->errorCollector->toCsvRows();
                if (empty($csvRows) === false) {
                    $headers = array_keys($csvRows[0]);
                    $f = fopen($pathFile, 'w');
                    fputcsv($f, $headers, ';');
                    foreach ($csvRows as $row) {
                        fputcsv($f, $row, ';');
                    }
                    fclose($f);
                    $this->errorFilePath = '@runtime/import_file_errors/'.$filename;
                }
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }
    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function setStatusToPending()
    {
        $this->status = self::STATUS_PENDING;
    }

    /**
     * @return bool
     */
    public function isStatusRunning()
    {
        return $this->status === self::STATUS_RUNNING;
    }

    public function setStatusToRunning()
    {
        $this->status = self::STATUS_RUNNING;
    }

    /**
     * @return bool
     */
    public function isStatusSuccess()
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function setStatusToSuccess()
    {
        $this->status = self::STATUS_SUCCESS;
    }

    /**
     * @return bool
     */
    public function isStatusFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function setStatusToFailed()
    {
        $this->status = self::STATUS_FAILED;
    }

    /**
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType()
    {
        return [
            self::TYPE_IMPORT => 'import',
            self::TYPE_EXPORT => 'export',
        ];
    }

    /**
     * @return string
     */
    public function displayType()
    {
        return self::optsType()[$this->type];
    }

    /**
     * @return bool
     */
    public function isTypeImport()
    {
        return $this->type === self::TYPE_IMPORT;
    }

    public function setTypeToImport()
    {
        $this->type = self::TYPE_IMPORT;
    }

    /**
     * @return bool
     */
    public function isTypeExport()
    {
        return $this->type === self::TYPE_EXPORT;
    }

    public function setTypeToExport()
    {
        $this->type = self::TYPE_EXPORT;
    }
}
