<?php
/**
 * JsonColumnModel.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\models
 */
namespace fractalCms\importExport\models;

use yii\base\Model;

class JsonColumnModel extends Model
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public string $id;
    public string $source;
    public string $target;
    public string $type;
    public bool $nullable;
    public mixed $default;
    public mixed $transform;

    public function scenarios() : array
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = [
            'id', 'source', 'target', 'type', 'nullable', 'default', 'transform'
        ];

        $scenarios[self::SCENARIO_UPDATE] = [
            'id', 'source', 'target', 'type', 'nullable', 'default', 'transform'
        ];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'id', 'source', 'target', 'type', 'nullable', 'default', 'transform'], 'default', 'value' => null],
            [['id', 'source', 'target', 'type'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
        ];
    }
}
