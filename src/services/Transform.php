<?php
/**
 * ImportXlsx.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services;

use Exception;
use Yii;

class Transform implements \fractalCms\importExport\interfaces\Transform
{
    const FORMAT_DATE = 'date';

    public static function apply(mixed $data): mixed
    {
        try {
            return $data;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
