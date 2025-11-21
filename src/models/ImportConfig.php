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

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "importConfigs".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $active
 * @property int|null $version
 * @property string $type
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

    /**
     * ENUM field values
     */
    const TYPE_IMPORT = 'import';
    const TYPE_EXPORT = 'export';

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
            'name', 'version', 'jsonConfig', 'dateCreate', 'dateUpdate', 'type', 'active'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'name', 'version', 'jsonConfig', 'dateCreate', 'dateUpdate', 'type', 'active'
        ];
        return $scenarios;
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'version', 'jsonConfig', 'dateCreate', 'dateUpdate'], 'default', 'value' => null],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['active'], 'default', 'value' => 0],
            [['active', 'version'], 'integer'],
            [['type'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['type', 'jsonConfig'], 'string'],
            [['name'], 'string', 'max' => 150],
            ['type', 'in', 'range' => array_keys(self::optsType()), 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['name'], 'unique', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
        ];
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
            'jsonConfig' => 'Json Config',
            'dateCreate' => 'Date Create',
            'dateUpdate' => 'Date Update',
        ];
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
