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

use fractalCms\importExport\services\ColumnTransformer;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\Json;
use Exception;
use Yii;

/**
 * This is the model class for table "importConfigColumns".
 *
 * @property int $id
 * @property int|null $importConfigId
 * @property string|null $source
 * @property string|null $target
 * @property string|null $format
 * @property string|null $defaultValue
 * @property resource|null $transformer
 * @property resource|null $transformerOptions
 * @property float|null $order
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
            'importConfigId', 'source', 'target', 'format', 'defaultValue', 'transformer', 'order', 'dateCreate', 'dateUpdate' , 'transformerOptions',
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'importConfigId', 'source', 'target', 'format', 'defaultValue', 'transformer', 'order', 'dateCreate', 'dateUpdate', 'transformerOptions',
        ];

        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importConfigId', 'source', 'target', 'format', 'defaultValue', 'transformer', 'transformerOptions', 'order', 'dateCreate', 'dateUpdate'], 'default', 'value' => null],
            [['importConfigId'], 'integer'],
            [['order'], 'number'],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['transformer', 'transformerOptions'], 'string'],
            [['source', 'target', 'defaultValue',], 'string', 'max' => 255],
            [['format'], 'string', 'max' => 50],
            [['importConfigId', 'source', 'target', 'format'], 'required', 'on' => [static::SCENARIO_CREATE, static::SCENARIO_UPDATE]],
            [['importConfigId'], 'exist', 'skipOnError' => true, 'targetClass' => ImportConfig::class, 'targetAttribute' => ['importConfigId' => 'id']],
        ];
    }

    /**
     * @inheritDoc
     */
    public function afterFind()
    {
        try {
            parent::afterFind();
            if (empty($this->transformer) === false && is_string($this->transformer) === true) {
                $transformer = Json::decode($this->transformer);
                $transformerOptions = [];
                if (empty($this->transformerOptions) === false && is_string($this->transformerOptions) === true) {
                    $transformerOptions = Json::decode($this->transformerOptions);
                }
                list($this->transformer, $this->transformerOptions) = $this->buildTransformer($transformer, $transformerOptions);
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }

    }

    /**
     * @param array $transformer
     * @param array $transformerOptions
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function buildTransformer(array $transformer, array $transformerOptions = []) : array
    {
        try {
            if (isset($transformer['name']) === true) {
                $transformService = Yii::$container->get(ColumnTransformer::class);
                if ($transformService instanceof ColumnTransformer) {
                    $transformers = $transformService->getTransformers();
                    if (isset($transformers[$transformer['name']]) === true && $transformers[$transformer['name']] instanceof \fractalCms\importExport\interfaces\ColumnTransformer) {
                        $transformerService = $transformers[$transformer['name']];
                        $transformer['optionsSchema'] = $transformerService->getOptionsSchema() ;
                        $transformer['description'] = ($transformer['description']) ?? $transformerService->getDescription();
                    }else {
                        $transformer = null;
                        $transformerOptions = null;
                    }
                    
                }
            }
            return [$transformer, $transformerOptions];
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
            'importConfigId' => 'Import Config ID',
            'source' => 'Source',
            'target' => 'Target',
            'format' => 'format',
            'defaultValue' => 'Default Value',
            'transformer' => 'transformer',
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
