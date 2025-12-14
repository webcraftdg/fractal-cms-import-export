<?php
/**
 * NumberTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\transformers;

use fractalCms\importExport\interfaces\ColumnTransformer;
use Exception;
use Yii;

class NumberColumnTransformer implements ColumnTransformer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'decimals';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Force une valeur numérique';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'decimals', 'type'=>'number','required'=>false,'label'=>'Décimales'],
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
            if (is_numeric($value) === false) {
                $value = (float)$value;
            }
            $decimals = $options['decimals'] ?? 0;
            return $decimals !== null ? number_format((float)$value, $decimals, '.', '')
                : $value;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
