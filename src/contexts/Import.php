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

final class Import extends AbstractContext
{

    /**
     * @var ImportErrorCollector
     */
    public readonly ImportErrorCollector $errors;

    /**
     * @param ImportConfig $config
     * @param ImportErrorCollector $errors
     * @param bool $stopOnError
     * @param bool $dryRun
     * @param int $rowNumber
     * @param array $params
     */
    public function __construct(
        ImportConfig $config,
        ImportErrorCollector $errors,
        bool $stopOnError,
        bool $dryRun,
        int $rowNumber,
        array $params = []
    ) {
        parent::__construct(
            config: $config,
            stopOnError: $stopOnError,
            dryRun: $dryRun,
            rowNumber: $rowNumber,
            params: $params
        );

        $this->errors = $errors;
    }
}
