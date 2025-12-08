<?php
/**
 * ImportConfigColumn.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\models
 */
namespace fractalCms\importExport\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "importConfigColumns".
 *
 * @property int $id
 * @property int|null $importConfigId
 * @property string|null $source
 * @property string|null $target
 * @property string|null $type
 * @property string|null $defaultValue
 * @property string|null $transform
 * @property int|null $order
 * @property string|null $dateCreate
 * @property string|null $dateUpdate
 *
 * @property ImportConfig $importConfig
 */
class ImportConfigColumn extends \yii\db\ActiveRecord
{

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

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
        return 'importConfigColumns';
    }

    public function scenarios() : array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'importConfigId', 'source', 'target', 'type', 'defaultValue', 'transform', 'order', 'dateCreate', 'dateUpdate'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'importConfigId', 'source', 'target', 'type', 'defaultValue', 'transform', 'order', 'dateCreate', 'dateUpdate'
        ];

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importConfigId', 'source', 'target', 'type', 'defaultValue', 'transform', 'order', 'dateCreate', 'dateUpdate'], 'default', 'value' => null],
            [['importConfigId', 'order'], 'integer'],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['source', 'target', 'defaultValue', 'transform'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
            [['importConfigId', 'source', 'target', 'type'], 'required', 'on' => [static::SCENARIO_CREATE, static::SCENARIO_UPDATE]],
            [['importConfigId'], 'exist', 'skipOnError' => true, 'targetClass' => ImportConfig::class, 'targetAttribute' => ['importConfigId' => 'id']],
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
            'source' => 'Source',
            'target' => 'Target',
            'type' => 'Type',
            'defaultValue' => 'Default Value',
            'transform' => 'Transform',
            'order' => 'Order',
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

}
