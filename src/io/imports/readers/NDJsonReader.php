<?php
/**
 * NDJsonReader.php
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
use InvalidArgumentException;
use Exception;
use Yii;

class NDJsonReader implements ImportReader
{

    private $handle;
    private int $batchSize = 250;
    private $mapColumns = [];

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
            $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
            $this->handle = fopen($filePath, 'rb');
            $metaLine = fgets($this->handle);
            if ($metaLine !== false) {
                try {
                    $metas = Json::decode($metaLine);
                    if (isset($metas['_type']) === true && $metas['_type'] == 'metas') {
                        $this->mapColumns = ($metas['columns']) ?? [];
                    }
                } catch(Exception $e) {
                    throw new InvalidArgumentException('NDJsonReader Meta of file not found: check your file is NDJSON ! ');
                }
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
            while (($line = fgets($this->handle)) !== false) {
                $row = Json::decode($line);
                if (isset($row['_type']) === true && $row['_type'] == 'data') {
                    unset($row['_type']);
                    $batch[] = $row;
                    $indexBatch++;
                     if ($indexBatch >= $this->batchSize) {
                        yield $batch;
                        $batch = [];
                        $indexBatch = 0;
                    }
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
     * close
     *
     * @return void
     */
    public function close(): void
    {
        try {
            fclose($this->handle);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
