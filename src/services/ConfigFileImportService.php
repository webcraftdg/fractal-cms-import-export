<?php
/**
 * ConfigFileImport.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */

namespace fractalCms\importExport\services;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\configReaders\JsonFileConfigReader;
use InvalidArgumentException;
use Exception;
use Yii;

class ConfigFileImportService
{


    public function __construct(private ImportConfig $config, private string $filePath)
    {
    }
    /**
     * run
     *
     * @return ImportConfig
     */
    public function run() : ImportConfig
    {
        try {
            $info = pathinfo($this->filePath);
            $extention = ($info['extension']) ?? 'json';
            switch($extention) {
                case 'json':
                    /**@var JsonFileConfigReader $fileConfigReader */
                    $fileConfigReader = new JsonFileConfigReader($this->config, $this->filePath);
                    break;
                default: 
                    throw new InvalidArgumentException('ConfigFileImportService : Error Import Format : '.$extention);
                    break;   
            }

            $fileConfigReader->open();
            $importConfig = $fileConfigReader->hydrate();
            $fileConfigReader->delete();
            return $importConfig;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
