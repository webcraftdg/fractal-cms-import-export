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
namespace fractalCms\importExport\providers;

use fractalCms\importExport\interfaces\ExportDataProvider;
use yii\db\Command;
use Traversable;
use Exception;
use Yii;

class SqlExportData implements ExportDataProvider
{

    public function __construct(
        private readonly Command $command
    ) {}

    /**
     * @return Traversable
     * @throws \yii\db\Exception
     */
    public function getIterator(): Traversable
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
}
