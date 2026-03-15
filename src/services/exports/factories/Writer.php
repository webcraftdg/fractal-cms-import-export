<?php
/**
 * ImportReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services\exports\factories;

use Exception;
use fractalCms\importExport\interfaces\ExportWriter;
use fractalCms\importExport\interfaces\importReader as InterfacesImportReader;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\exports\writers\XlsxWriter;
use fractalCms\importExport\services\imports\readers\ExcelReader;
use InvalidArgumentException;
use Yii;

class Writer {

  
    public function create(string $format) : ExportWriter
    {
        try {
            switch($format) {
                case ImportConfig::FORMAT_EXCEL: 
                    return new ();
                    break;
                case ImportConfig::FORMAT_EXCEL_X: 
                    return new XlsxWriter();
                    break;
                case ImportConfig::FORMAT_CSV: 
                return new ExcelReader();
                    break;
                case ImportConfig::FORMAT_CSV: 
                    return new ExcelReader();
                    break;
                case ImportConfig::FORMAT_CSV: 
                    return new ExcelReader();
                    break;
                default: 
                    throw new InvalidArgumentException('Error Import Format : '.$format);
                    break;
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}