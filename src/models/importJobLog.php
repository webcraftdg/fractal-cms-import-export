<?php

namespace fractalCms\importExport\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "importJobLogs".
 *
 * @property int $id
 * @property int|null $importJogId
 * @property int|null $row
 * @property resource|null $data
 * @property string|null $message
 * @property string|null $dateCreate
 * @property string|null $dateUpdate
 *
 * @property ImportJob $importJog
 */
class importJobLog extends \yii\db\ActiveRecord
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
        return 'importJobLogs';
    }

    public function scenarios() : array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'importJogId', 'data', 'message', 'dateCreate', 'dateUpdate', 'row', 'message'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'importJogId', 'data', 'message', 'dateCreate', 'dateUpdate', 'row', 'message'
        ];
        return $scenarios;
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['importJogId', 'data', 'message', 'dateCreate', 'dateUpdate'], 'default', 'value' => null],
            [['row'], 'default', 'value' => 0],
            [['importJogId', 'row'], 'integer', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['data', 'message'], 'string', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['dateCreate', 'dateUpdate'], 'safe'],
            [['importJogId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ImportJob::class,
                'targetAttribute' => ['importJogId' => 'id'],
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
            'importJogId' => 'Import Jog ID',
            'row' => 'Row',
            'data' => 'Data',
            'message' => 'Message',
            'dateCreate' => 'Date Create',
            'dateUpdate' => 'Date Update',
        ];
    }

    /**
     * Gets query for [[ImportJog]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImportJog()
    {
        return $this->hasOne(ImportJob::class, ['id' => 'importJogId']);
    }

}
