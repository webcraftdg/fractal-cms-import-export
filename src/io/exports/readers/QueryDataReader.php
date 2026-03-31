<?php
/**
 * QueryDataReader.php
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
use yii;

class QueryDataReader implements CountableDataReader
{

    private $query;
    private int $batchSize = 200;

    public function open(array $options): void
    {
    try {
            $this->query = ($options['query']) ?? null;
            if ($this->query === null) {
                throw new InvalidArgumentException('QueryExportData excepted params "query"');
            }

            $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
            
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * @return iterable
     */
    public function read(): iterable
    {
        try {
            foreach ($this->query->batch($this->batchSize) as $row) {
                yield $row;
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * count
     *
     * @return int
     */
    public function count() : int
    {
        try {
            return $this->query->count();
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
