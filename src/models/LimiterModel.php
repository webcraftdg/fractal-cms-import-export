<?php
/**
 * LimiterModel.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\models
 */
namespace fractalCms\importExport\models;

use yii\base\Model;

class LimiterModel extends Model
{
    const SCENARIO_CREATE = 'create';

    public int $rows;
    public int $columns;
    public string $format;
    public int $estimatedMb;
    public string $name;

    public function scenarios() : array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'rows', 'columns', 'format', 'estimatedMb', 'name'
        ];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[  'rows', 'columns', 'estimatedMb'], 'default', 'value' => 0],
            [[  'format'], 'default', 'value' => 'csv'],
            [[  'name'], 'default', 'value' => ''],
        ];
    }
}
