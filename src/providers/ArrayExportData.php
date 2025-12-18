<?php
/**
 * ArrayExportData.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\db
 */
namespace fractalCms\importExport\providers;

use fractalCms\importExport\interfaces\ExportDataProvider;
use Traversable;
use Exception;
use Yii;

class ArrayExportData implements ExportDataProvider
{

    public function __construct(
        private readonly array $rows
    ) {}

    /**
     * @return Traversable
     * @throws Exception
     */
    public function getIterator(): Traversable
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
}
