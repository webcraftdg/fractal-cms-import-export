<?php
/**
 * Import.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\contexts
 */
namespace fractalCms\importExport\contexts;

use fractalCms\importExport\exceptions\ImportErrorCollector;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use Yii;

final class Import
{
    public function __construct(
        public readonly ImportConfig $config,
        public readonly ImportErrorCollector $errors,
        public readonly bool $stopOnError,
        public readonly bool $dryRun,
        public int $rowNumber
    ) {}

    /**
     * @param int $row
     * @return $this
     * @throws Exception
     */
    public function withRowNumber(int $row) : Import
    {
        try {
            $clone = clone $this;
            $clone->rowNumber = $row;
            return $clone;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
