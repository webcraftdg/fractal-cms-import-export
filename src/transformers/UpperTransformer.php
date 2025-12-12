<?php
/**
 * UpperTransformer.php
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

class UpperTransformer implements Transformer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'upper';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Convertit en majuscules';
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
            return is_string($value) ? mb_strtoupper($value) : $value;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
