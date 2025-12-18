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
     * @var array
     */
    private array $writtenHeaders = [];
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


    /**
     * @param string $sheet
     * @param array $row
     * @param int $startRow
     * @param int $startCol
     * @param string|null $style
     * @return void
     */
    public function writeRow(
        string $sheet,
        array $row,
        int $startRow = 1,
        int $startCol = 1,
        ?string $style = null
    ): void {
        $this->writer->write(
            new WriteTarget(
                sheet: $sheet,
                rowNumber: $startRow,
                colNumber: $startCol,
                style: $style
            ),
            $row
        );
    }

    /**
     * @param string $sheet
     * @param array $headers
     * @param int $rowNumber
     * @param int $colOffset
     * @param string|null $style
     * @return void
     * @throws Exception
     */
    public function writeHeaderOne(string $sheet, array $headers, int $rowNumber, int $colOffset, ?string $style = null): void
    {
        try {
            $key = sha1($sheet . ':' . $rowNumber . ':' . $colOffset);

            if (isset($this->writtenHeaders[$key])) {
                return;
            }
            $this->writeRow(
                sheet: $sheet,
                row: $headers,
                startRow: $rowNumber,
                startCol: $colOffset,
                style: $style
            );
            $this->writtenHeaders[$key] = true;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }


    /**
     * @param string $filePath
     * @return void*
     */
    public function finalize(string $filePath): void
    {
        $this->writer->save($filePath);
    }
}
