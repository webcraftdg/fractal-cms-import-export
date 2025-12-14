<?php
/**
 * ColumnModel.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\models
 */
namespace fractalCms\importExport\models;

use fractalCms\importExport\interfaces\ColumnTransformer;
use yii\base\Model;

class ColumnModel extends Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public string $id;
    public string $source;
    public string $target;
    public string $format;
    public mixed $default;
    public ColumnTransformer|null $transformer;

    public function scenarios() : array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'id', 'source', 'target', 'format', 'default', 'transformer'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'id', 'source', 'target', 'format', 'default', 'transformer'
        ];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'id', 'source', 'target', 'format', 'default', 'transformer'], 'default', 'value' => null],
            [['source', 'target', 'format'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
        ];
    }
}
