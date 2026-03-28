<?php
/**
 * TrimTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\transformers
 */
namespace fractalCms\importExport\pipeline\transformers;

use fractalCms\importExport\pipeline\interfaces\ColumnTransformer;
use Exception;
use Yii;

class TrimColumnTransformer implements ColumnTransformer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'trim';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Supprime les espaces avant/après';
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
            return is_string($value) ? trim($value) : $value;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
