<?php
/**
 * SqlIterator.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\db
 */
namespace fractalCms\importExport\db;

use IteratorAggregate;
use Generator;
use Yii;
use Exception;

class SqlIterator implements IteratorAggregate
{
    private string $sql;
    private int $batchSize;

    /**
     * @param string $sql
     * @param int $batchSize
     */
    public function __construct(string $sql, int $batchSize = 1000)
    {
        $this->sql = $sql;
        $this->batchSize = $batchSize;
    }

    /**
     * @return Generator
     * @throws \yii\db\Exception
     */
    public function getIterator(): Generator
    {
        try {
            $reader = Yii::$app->db->createCommand($this->sql)->query();
            $batch = [];
            foreach ($reader as $row) {
                $batch[] = $row;
                if (count($batch) === $this->batchSize) {
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
    public function getCount() : int
    {
        try {
            return Yii::$app->db->createCommand($this->sql)->query()->count();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }
}
