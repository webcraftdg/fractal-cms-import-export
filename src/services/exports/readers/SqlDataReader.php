<?php
/**
 * SqlDataReader.php
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
use yii\db\Command;

class SqlDataReader implements CountableDataReader
{

    private Command $command;


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
            foreach ($reader as $row) {
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
