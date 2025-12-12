<?php
/**
 * BooleanTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\transformers;

use fractalCms\importExport\interfaces\Transformer;
use Exception;
use Yii;

class BooleanTransformer implements Transformer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'boolean';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Convertit un bool en libellé';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'true', 'type'=>'string','required'=>true,'label'=>'Valeur si vrai'],
            ['key' => 'false', 'type'=>'string','required'=>true,'label'=>'Valeur si faux'],
        ];
    }

    /**
     * @param mixed $value
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function transform(mixed $value, array $options = []): mixed
    {
        try {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN)
                ? $options['true']
                : $options['false'];
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
