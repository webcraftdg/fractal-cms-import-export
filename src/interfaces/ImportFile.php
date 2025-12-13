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

use fractalCms\importExport\exceptions\InsertResult;
use fractalCms\importExport\models\ImportConfig;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface ImportFile extends Import
{

    /**
     * @param ImportConfig $importConfig
     * @param array $attributes
     * @return InsertResult
     */
    public static function insertActiveRecord(ImportConfig $importConfig, array $attributes) : InsertResult;

    /**
     * @param ImportConfig $importConfig
     * @param array $attributes
     * @return InsertResult
     */
    public static function insertSql(ImportConfig $importConfig, array $attributes) : InsertResult;


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
