<?php
/**
 * ConfigFileImportService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\configuration\services
 */

namespace fractalCms\importExport\configuration\services;

use fractalCms\importExport\configuration\readers\JsonFileConfigReader;
use fractalCms\importExport\models\ImportConfig;
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
                    $fileConfigReader = new JsonFileConfigReader($this->filePath);
                    break;
                default: 
                    throw new InvalidArgumentException('ConfigFileImportService : Error Import Format : '.$extention);
                    break;   
            }

            $success = $fileConfigReader->open();
            if ($success === true) {
                $data = $fileConfigReader->read();
                $this->config->attributes = ($data['attributes']) ?? [];
                $this->config->tmpColumns = ($data['columns']) ?? [];
            } else {
                $this->config->addError('importFile', 'Le fichier n\'est un JSON Valide');

            }
            $fileConfigReader->delete();
            return $this->config;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
