<?php
/**
 * Import.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\runtime\contexts
 */
namespace fractalCms\importExport\runtime\contexts;

use fractalCms\importExport\exceptions\ImportErrorCollector;
use fractalCms\importExport\interfaces\WriterInterface;
use fractalCms\importExport\models\ImportConfig;

final class Writer
{


    /**
     * @param ImportConfig $config
     * @param ImportErrorCollector $errors
     * @param bool $stopOnError
     * @param bool $dryRun
     * @param int $rowNumber
     * @param array $params
     */
    public function __construct(
        public string $absolutePath,
        public string $relativePath,
        public array $preamble = [],
        public array $params = []
    ) {

    }
}
