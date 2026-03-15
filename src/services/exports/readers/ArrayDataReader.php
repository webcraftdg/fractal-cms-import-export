<?php
/**
 * ArrayDataReader.php
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
use Yii;

class ArrayDataReader implements CountableDataReader
{

    private $rows;

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
            foreach ($this->rows as $row) {
                yield $row;
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
