<?php
/**
 * JsonFileConfigReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\configuration\readers
 */
namespace fractalCms\importExport\configuration\readers;

use fractalCms\importExport\io\interfaces\FileConfigImportReader;
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
     * @param  string       $filePath
     */
    public function __construct(
        private string $filePath
    )
    {
    }

    /**
     * open
     *
     * @return bool
     */
    public function open(): bool
    {
        try {
            try {
                $success = true;
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
                    $success = false;
                }
            } catch(Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $success = false;
            }
            return $success;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * read
     *
     * @return array
     */
    public function read(): array
    {
        try {
            return [
                'attributes' => $this->attributes,
                'columns' => $this->tmpColumns
            ];
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
