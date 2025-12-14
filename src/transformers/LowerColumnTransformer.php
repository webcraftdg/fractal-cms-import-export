<?php
/**
 * LowerTransformer.php
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

class LowerColumnTransformer implements ColumnTransformer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'lower';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Convertit en minuscules';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [];
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
            return is_string($value) ? mb_strtolower($value) : $value;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
