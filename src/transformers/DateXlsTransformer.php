<?php
/**
 * DateXlsTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\transformers;

use fractalCms\importExport\interfaces\Transformer;
use DateTime;
use Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yii;

class DateXlsTransformer implements Transformer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'date-xls';
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
            $to = $options['to'] ?? 'Y-m-d';

            // Cas 1 : date Excel (numérique)
            if (is_numeric($value)) {
                $date =  Date::excelToDateTimeObject($value)->format($to);
            } elseif (is_string($value)) {
                $timestamp = strtotime($value);
                if ($timestamp !== false) {
                    $date =  date($to, $timestamp);
                }
            }
            return  $date;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
