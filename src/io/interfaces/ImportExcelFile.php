<?php
/**
 * ImportExcelFile.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\io\interfaces
 */
namespace fractalCms\importExport\io\interfaces;

use fractalCms\importExport\models\ImportConfig;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface ImportExcelFile extends Import
{

    /**
     * @param string $filePath
     * @return Spreadsheet
     */
    public static function prepareSpreadSheet(string $filePath): Spreadsheet;

    /**
     * @return int
     */
    public static function getStartRow() : int;

    /**
     * @param Worksheet $worksheet
     * @return int
     */
    public static function getEndRow(Worksheet $worksheet) : int;

    /**
     * @return int
     */
    public static function getStartColumn() : int;

    /**
     * @param ImportConfig $importConfig
     * @return int
     */
    public static function getEndColumn(ImportConfig $importConfig) : int;
}
