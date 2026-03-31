<?php
/**
 * JsonReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\io\imports\readers
 */
namespace fractalCms\importExport\io\imports\readers;


use fractalCms\importExport\io\interfaces\ImportReader;
use yii\helpers\Json;
use Exception;
use Yii;

class JsonReader implements ImportReader
{

    private int $batchSize = 250;
    private array $records = [];

    /**
     * open
     *
     * @param  string $filePath
     * @param  array  $options
     *
     * @return void
     */
    public function open(string $filePath, array $options = []): void
    {
        try {
            try {
                $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
                $data = Json::decode(file_get_contents($filePath), true);
                $this->records = ($data['records']) ?? [];
            } catch (Exception $e)  {
                Yii::error($e->getMessage(), __METHOD__);
                $this->records = [];
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * read
     *
     * @return iterable
     */
    public function read(): iterable
    {
         try {
            $batch = [];
            $indexBatch = 0;

            foreach($this->records as $record) {
                $batch[] = $this->getRowValues(($record['fields']) ?? []);
                $indexBatch ++;
                if ($indexBatch >= $this->batchSize) {
                    yield $batch;
                    $batch = [];
                    $indexBatch = 0;
                }
            }
            if (empty($batch) === false) {
                yield $batch;
            }

        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Undocumented function
     *
     * @param  array $fields
     *
     * @return array
     */
    public function getRowValues(array $fields) : array
    {
        try {
            $row = [];
            foreach($fields as $field) {
                $name = ($field['label']) ?? null;
                $value = ($field['value']) ?? '';
                if ($name === null) {
                    continue;
                }
                $row[$name] = $value;
            }
            return $row;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        try {
            $this->records = [];
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
