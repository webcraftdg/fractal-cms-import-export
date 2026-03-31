<?php
/**
 * ImportReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\imports\factories
 */
namespace fractalCms\importExport\pipeline\imports\factories;

use fractalCms\importExport\io\interfaces\ImportReader as ImportReaderInterface;
use fractalCms\importExport\io\imports\readers\XmlReader;
use fractalCms\importExport\io\imports\readers\ExcelReader;
use fractalCms\importExport\io\imports\readers\JsonReader;
use fractalCms\importExport\io\imports\readers\NDJsonReader;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use InvalidArgumentException;
use Yii;

class ImportReader 
{

    /**
     * create
     *
     * @param  string                 $format
     *
     * @return ImportReaderInterface
     */
    public function create(string $format) : ImportReaderInterface
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