<?php
/**
 * ReplaceTransformer.php
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

class ReplaceColumnTransformer implements ColumnTransformer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'replace';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Remplace du texte';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'search', 'type'=>'text','required'=>true,'label'=>'Rechercher'],
            ['key' => 'replace', 'type'=>'text','required'=>true,'label'=>'Remplacer'],
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
            return is_string($value)
                ? str_replace($options['search'], $options['replace'], $value)
                : $value;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
