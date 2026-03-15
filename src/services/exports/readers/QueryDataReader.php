<?php
/**
 * QueryDataReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\db
 */
namespace fractalCms\importExport\services\exports\readers;

use Exception;
use fractalCms\importExport\interfaces\CountableDataReader;
use InvalidArgumentException;
use yii;

class QueryDataReader implements CountableDataReader
{

    private $query;
    private $batchSize = 1000;

    public function open(array $options): void
    {
    try {
            $this->query = ($options['query']) ?? null;
            if ($this->query === null) {
                throw new InvalidArgumentException('QueryExportData excepted params "query"');
            }

            $this->batchSize = ($options['batchSize']) ?? null;
            if ($this->batchSize === null) {
                throw new InvalidArgumentException('QueryExportData excepted params "batchSize"');
            }
            
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
            foreach ($this->query->each($this->batchSize) as $row) {
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
