<?php
/**
 * DateTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\transformers;

use fractalCms\importExport\interfaces\ColumnTransformer;
use DateTime;
use Exception;
use Yii;

class DateColumnTransformer implements ColumnTransformer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'date';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Convertit un format de date';
    }

    /**
     * @return array[]
     */
    public function getOptionsSchema(): array
    {
        return [
            ['key' => 'from', 'type'=>'text','required'=>true,'label'=>'Format source'],
            ['key' => 'to', 'type'=>'text','required'=>true,'label'=>'Format cible'],
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
            $date = $value;
            if(empty($value) === false) {
                $dateTime = DateTime::createFromFormat($options['from'], (string)$value);
                if ($dateTime !== false) {
                    $date = $dateTime->format($options['to']);
                }
            }
            return $date;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
