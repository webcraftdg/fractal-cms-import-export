<?php
/**
 * ArrayDataReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\io\exports\readers
 */
namespace fractalCms\importExport\io\exports\readers;

use fractalCms\importExport\io\interfaces\CountableDataReader;
use Exception;
use InvalidArgumentException;
use Yii;

class ArrayDataReader implements CountableDataReader
{

    private array $rows;
    private int $batchSize = 200;

    /**
     * open
     *
     * @param  array $options
     *
     * @return void
     */
   public function open(array $options): void
    {
        try {
            $this->rows = ($options['rows']) ?? null;
            if ($this->rows === null) {
                throw new InvalidArgumentException('ArrayExportData excepted params "rows"');
            }
            $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
            
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * @return iterable
     * @throws Exception
     */
    public function read(): iterable
    {
        try {
            $indexBatch = 0;
            $batch = [];
            foreach ($this->rows as $row) {
                $batch[] = $row;
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
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * @return int
     * @throws \yii\db\Exception
     */
    public function count() : int
    {
        try {
            return count($this->rows);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
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
            //Not used here
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }
}
