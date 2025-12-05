<?php
/**
 * Import.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJobLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface ImportFile extends Import
{

    /**
     * @param ImportConfig $importConfig
     * @param array $attributes
     * @param bool $isTest
     * @return array
     */
    public static function insert(ImportConfig $importConfig, array $attributes, bool $isTest = false) : array;

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
