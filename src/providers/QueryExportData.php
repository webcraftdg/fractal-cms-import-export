<?php
/**
 * QueryExportData.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\db
 */
namespace fractalCms\importExport\providers;

use fractalCms\importExport\interfaces\ExportDataProvider;
use yii\db\Query;
use Traversable;
use Exception;
use yii;

class QueryExportData implements ExportDataProvider
{
    public function __construct(
        private readonly Query $query,
        private readonly int $batchSize = 1000
    ) {}

    /**
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        foreach ($this->query->each($this->batchSize) as $row) {
            yield $row;
        }
    }

    /**
     * @return int
     * @throws Exception
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
}
