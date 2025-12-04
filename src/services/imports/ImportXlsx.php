<?php

namespace fractalCms\importExport\services\imports;

use fractalCms\importExport\interfaces\ImportFile;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\models\ImportJobLog;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\db\Transaction;
use yii\helpers\Json;

class ImportXlsx implements ImportFile
{


    /**
     * @param ImportConfig $importConfig
     * @param string $filePath
     * @param bool $isTest
     * @return ImportJob
     * @throws NotSupportedException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function run(ImportConfig $importConfig, string $filePath, bool $isTest = false): ImportJob
    {
        try {
            $importJob = new ImportJob(['scenario' => ImportJob::SCENARIO_CREATE]);
            $importJob->importConfigId = $importConfig->id;
            $importJob->userId = Yii::$app->user->identity->getId();
            $importJob->type = ImportJob::TYPE_IMPORT;
            $importJob->filePath = $filePath;
            $importJob->successRows = 0;
            $importJob->errorRows = 0;
            $spreadsheet = static::prepareSpreadSheet($filePath);
            $mappingColumns = $importConfig->tmpColumns;
            if($spreadsheet instanceof Spreadsheet) {
                $sheet = $spreadsheet->getActiveSheet();
                $startRow = static::getStartRow();
                $endRow = static::getEndRow($sheet);
                $starCol = static::getStartColumn();
                $endColumn = static::getEndColumn($importConfig);
                $importJob->totalRows = $endRow;
                $importJob->save();
                $importJob->refresh();
                for($row = $startRow;  $row < ($endRow + 1); $row ++) {
                    $indexJsonSource = 0;
                    $attributes = [];
                    for($col = $starCol; $col < ($endColumn + 1); $col += 1) {
                        $mappingColumn = ($mappingColumns[$indexJsonSource]) ?? [];
                        if (isset($mappingColumn['source']) === true) {
                            $value = $sheet->getCell([$col, $row])->getValue();
                            $attributes[$mappingColumn['source']] = $value;
                        }
                        $indexJsonSource += 1;
                    }
                    if (empty($attributes) === false) {
                        $importJobLog = static::insert($importConfig, $attributes);
                        $importJobLog->importJogId = $importJob->id;
                        if ($importJobLog->message !== ImportJobLog::MESSAGE_SUCCESS) {
                            $importJob->errorRows += 1;
                        } else {
                            $importJob->successRows += 1;
                        }
                    }
                }
            }
            $importJob->save();
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * insert
     *
     * @param ImportConfig $importConfig
     * @param array $attributes
     * @param bool $isTest
     * @return ImportJobLog
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function insert(ImportConfig $importConfig, array $attributes, bool $isTest = false): ImportJobLog
    {
        try {
            $importJobLog = new ImportJobLog(['scenario' => ImportJobLog::SCENARIO_CREATE]);
            $importJobLog->data = Json::encode($attributes);
            $isSql = (empty($importConfig->sql) === false);
            $transaction = null;
            if ($isTest === true) {
                $transaction = Yii::$app->db->beginTransaction();
            }
            if ($isSql === false && empty($importConfig->table) === false) {
                try {
                    if (class_exists($importConfig->table) === true)  {
                        /** @var ActiveRecord $model */
                        $model = Yii::createObject($importConfig->table);
                        $model->attributes = $attributes;
                        if ($model->validate() === true) {
                            $model->save();
                            $importJobLog->message = ImportJobLog::MESSAGE_SUCCESS;
                        } else {
                            $importJobLog->message = ImportJobLog::MESSAGE_ERROR.' : '.Json::encode($model->errors);
                        }
                    }
                } catch (Exception $e) {
                    Yii::error($e->getMessage(), __METHOD__);
                    $importJobLog->message = ImportJobLog::MESSAGE_ERROR.' : '.$e->getMessage();
                }
            } else {
                try {
                    $viewName = $importConfig->getContextName();
                    Yii::$app->db->createCommand()->insert(
                        $viewName,
                        $attributes
                    )->execute();
                    $importJobLog->message = ImportJobLog::MESSAGE_SUCCESS;
                } catch (Exception $e) {
                    Yii::error($e->getMessage(), __METHOD__);
                    $importJobLog->message = ImportJobLog::MESSAGE_ERROR.' : '.$e->getMessage();
                }
            }
            if ($transaction instanceof Transaction) {
                $transaction->rollBack();
            }
            return $importJobLog;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Prepare
     *
     * @param string $filePath
     * @return Spreadsheet
     * @throws NotSupportedException
     */
    public static function prepareSpreadSheet(string $filePath): Spreadsheet
    {
        try {
            $spreadsheet = null;
            if (file_exists($filePath) === true) {
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                switch ($extension) {
                    case 'xlsx':
                    case 'xls':
                        $spreadsheet = IOFactory::load($filePath);
                        break;
                    case 'csv':
                    case 'txt':
                        $reader = new Csv();
                        $reader->setDelimiter(';');
                        $reader->setEnclosure('');
                        $reader->setInputEncoding('CP1252');
                        $reader->setSheetIndex(0);
                        $reader->setReadDataOnly(true);
                        $spreadsheet = $reader->load($filePath);
                        break;
                    default:
                        throw new NotSupportedException("Extension non supportée : " . $extension);
                }
            }
            return $spreadsheet;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Get start row
     *
     * @return int
     * @throws Exception
     */
    public static function getStartRow(): int
    {
        try {
            return 1;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * get end row
     *
     * @param Worksheet $worksheet
     * @return int
     * @throws Exception
     */
    public static function getEndRow(Worksheet $worksheet): int
    {
        try {
            return $worksheet->getHighestRow();
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Get start column
     *
     * @return int
     * @throws Exception
     */
    public static function getStartColumn(): int
    {
        try {
            return 1;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Get end column
     *
     * @param ImportConfig $importConfig
     * @return int
     * @throws Exception
     */
    public static function getEndColumn(ImportConfig $importConfig): int
    {
        try {
            return count($importConfig->tmpColumns);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
