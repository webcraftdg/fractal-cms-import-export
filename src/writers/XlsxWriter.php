<?php
/**
 * XlsxWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services\writers
 */
namespace fractalCms\importExport\writers;

use fractalCms\importExport\interfaces\ExportWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yii;

class XlsxWriter implements ExportWriter
{
    /**
     * @var array
     */
    private array $sheetCursors = [];
    /**
     * @var Spreadsheet
     */
    private Spreadsheet $spreadsheet;

    /** @var array<string, Worksheet> */
    private array $sheetsByTitle = [];

    /**
     * @param Spreadsheet $spreadsheet
     */
    public function __construct(Spreadsheet $spreadsheet)
    {
        $this->spreadsheet = $spreadsheet;
    }

    /**
     * @param string $sheet
     * @return int
     */
    public function nextRow(string $sheet): int
    {
        return $this->sheetCursors[$sheet] ??= 1;
    }

    /**
     * @param WriteTarget $target
     * @param array $row
     * @return void
     * @throws Exception
     */
    public function write(WriteTarget $target, array $row): void
    {
        try {
            /** @var Worksheet $sheet */
            $sheet = $this->getOrCreateSheet($target->sheet);

            $rowIndex = $target->rowNumber;
            $colIndex = $target->colNumber;

            foreach ($row as $value) {
                $sheet->setCellValue([$colIndex, $rowIndex], $value);
                $colIndex++;
            }

            $this->sheetCursors[$target->sheet] = $rowIndex + 1;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $title
     * @return Worksheet
     * @throws Exception
     */
    private function getOrCreateSheet(string $title): Worksheet
    {

        try {
            if (isset($this->sheetsByTitle[$title])) {
                $sheet =  $this->sheetsByTitle[$title];
            } else {
                // Si une feuille existe déjà avec ce titre via PhpSpreadsheet (par sécurité)
                $sheet = $this->spreadsheet->getSheetByName($title);
                if ($sheet instanceof Worksheet) {
                    $this->sheetsByTitle[$title] = $sheet;
                } else {
                    $sheetNumber = count($this->spreadsheet->getAllSheets());
                    if ($sheetNumber === 1 && empty($this->sheetsByTitle) === true) {
                        $sheet = $this->spreadsheet->getActiveSheet();
                    } else {
                        // Créer une nouvelle feuille
                        $sheet = $this->spreadsheet->createSheet($sheetNumber);
                    }
                    $sheet->setTitle($title);
                    $this->sheetsByTitle[$title] = $sheet;
                }
            }
            return $sheet;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $filePath
     * @return void
     */
    public function save(string $filePath): void
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($filePath);
    }
}
