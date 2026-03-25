<?php
/**
 * JsonFileConfigReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\configReaders
 */
namespace fractalCms\importExport\configReaders;

use fractalCms\importExport\interfaces\FileConfigImportReader;
use fractalCms\importExport\models\ImportConfig;
use yii\helpers\Json;
use Exception;
use Yii;

class JsonFileConfigReader implements FileConfigImportReader
{
    

    private array $attributes = [];
    private array $tmpColumns = [];

    /**
     * constructor
     *
     * @param  ImportConfig $config
     * @param  string       $filePath
     */
    public function __construct(
        private ImportConfig $config,
        private string $filePath
    )
    {
    }

    /**
     * open
     *
     * @return void
     */
    public function open(): void
    {
        try {
            try {
                $json = file_get_contents($this->filePath);
                $valid = json_validate($json);
                if ($valid === true) {
                    $data = Json::decode($json);
                    $this->attributes = ($data['metas']) ?? [];
                    $records = ($data['records']) ?? [];
                    if (empty($records) === false) {
                        $record = $records[0];
                        $this->tmpColumns = ($record['fields']) ?? [];
                    }
                } else {
                    $this->config->addError('importFile', 'Le fichier n\'est un JSON Valide');
                }
            } catch(Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $this->config->addError('importFile', 'Le fichier n\'est un JSON Valide');
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * hydrate
     *
     * @return ImportConfig
     */
    public function hydrate(): ImportConfig
    {
        try {
            if ($this->config->hasErrors() === false) {
                $this->config->setAttributes($this->attributes);
                $this->config->tmpColumns = $this->tmpColumns;
            }
            return $this->config;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * delete
     *
     * @return void
     */
    public function delete(): void
    {
       try {
            unlink($this->filePath);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }
}
