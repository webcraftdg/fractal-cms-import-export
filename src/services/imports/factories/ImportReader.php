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
namespace fractalCms\importExport\services\imports\factories;

use Exception;
use fractalCms\importExport\interfaces\importReader as InterfacesImportReader;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\imports\readers\ExcelReader;
use fractalCms\importExport\services\imports\readers\JsonReader;
use fractalCms\importExport\services\imports\readers\NDJsonReader;
use fractalCms\importExport\services\imports\readers\XmlReader;
use InvalidArgumentException;
use Yii;

class ImportReader {

    /**
     * create
     *
     * @param  string                 $format
     *
     * @return InterfacesImportReader
     */
    public function create(string $format) : InterfacesImportReader
    {
        try {
            switch($format) {
                case ImportConfig::FORMAT_EXCEL: 
                case ImportConfig::FORMAT_EXCEL_X: 
                case ImportConfig::FORMAT_CSV: 
                    return new ExcelReader();
                    break;
                case ImportConfig::FORMAT_XML: 
                    return new XmlReader();
                    break;
                case ImportConfig::FORMAT_JSON: 
                    return new JsonReader() ;
                    break;
                case ImportConfig::FORMAT_NDJSON: 
                    return new NDJsonReader();           
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