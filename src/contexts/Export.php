<?php
/**
 * Export.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\contexts
 */
namespace fractalCms\importExport\contexts;

use fractalCms\importExport\interfaces\ExportWriter;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\writers\WriteTarget;
use Yii;

final class Export extends AbstractContext
{

    /**
     * @param ImportConfig $config
     * @param bool $dryRun
     * @param int $rowNumber
     * @param array $params
     */
    public function __construct(
        ImportConfig $config,
        bool $dryRun,
        int $rowNumber,
        private ExportWriter $writer,
        array $params = []
    ) {
        parent::__construct(
            config: $config,
            stopOnError: false, // inutile à l’export
            dryRun: $dryRun,
            rowNumber: $rowNumber,
            params: $params
        );
    }


    public function writeRow(
        string $sheet,
        array $row,
        ?int $startRow = null,
        int $startCol = 1,
        ?string $style = null
    ): void {
        $this->writer->write(
            new WriteTarget(
                sheet: $sheet,
                row: $startRow,
                col: $startCol,
                style: $style
            ),
            $row
        );
    }

    public function finalize(string $filePath): void
    {
        $this->writer->save($filePath);
    }
}
