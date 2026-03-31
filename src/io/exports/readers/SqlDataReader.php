<?php
/**
 * SqlDataReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\io\exports\readers
 */
namespace fractalCms\importExport\io\exports\readers;

use fractalCms\importExport\io\interfaces\CountableDataReader;
use yii\db\Command;
use InvalidArgumentException;
use Exception;
use Yii;

class SqlDataReader implements CountableDataReader
{

    private Command $command;
    private int $batchSize = 200;


    /**
     * read
     *
     * @param  array $options
     *
     * @return void
     */
    public function open(array $options): void
    {
        try {
            $this->command = ($options['command']) ?? null;
            if ($this->command === null) {
                throw new InvalidArgumentException('SqlDataReader excepted params "command"');
            }
            $this->batchSize = ($options['batchSize']) ?? $this->batchSize;
            
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * @return iterable
     * @throws \yii\db\Exception
     */
    public function read(): iterable
    {
        try {
            $reader = $this->command->query();
            $batch = [];
             foreach ($reader as $row) {
                $batch[] = $row;
                if (count($batch) >= $this->batchSize) {
                    yield $batch;
                    $batch = [];
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
            return $this->command->query()->count();
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
