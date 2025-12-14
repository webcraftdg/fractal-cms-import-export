<?php
/**
 * ImportXlsx.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services\imports;

use fractalCms\importExport\exceptions\ImportError;
use fractalCms\importExport\exceptions\ImportErrorCollector;
use fractalCms\importExport\exceptions\InsertResult;
use fractalCms\importExport\exceptions\RowTransformException;
use fractalCms\importExport\interfaces\ImportFile;
use fractalCms\importExport\interfaces\RowTransformer as RowTransformerInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\contexts\Import as ImportContext;
use Exception;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\models\ImportJob;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use yii\helpers\Json;
use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\db\Transaction;

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
            $errorCollector = new ImportErrorCollector();
            $baseImportContext = new ImportContext(
                config: $importConfig,
                errors: $errorCollector,
                stopOnError: $importConfig->stopOnError,
                dryRun:$isTest,
                rowNumber: -1
            );
            $rowTransformer = $importConfig->getRowTransformer();
            $importJob = new ImportJob(['scenario' => ImportJob::SCENARIO_CREATE]);
            $importJob->importConfigId = $importConfig->id;
            $importJob->userId = Yii::$app->user->identity->getId();
            $importJob->type = ImportJob::TYPE_IMPORT;
            $importJob->filePath = $filePath;
            $importJob->status = ImportJob::STATUS_RUNNING;
            $importJob->successRows = 0;
            $importJob->errorRows = 0;
            $spreadsheet = static::prepareSpreadSheet($filePath);
            $mappingColumns = $importConfig->getImportColumns()->all();
            if($spreadsheet instanceof Spreadsheet) {
                $sheet = $spreadsheet->getActiveSheet();
                $startRow = static::getStartRow();
                $endRow = static::getEndRow($sheet);
                $starCol = static::getStartColumn();
                $endColumn = static::getEndColumn($importConfig);
                $importJob->totalRows = $endRow;
                $importJob->save();
                $importJob->refresh();
                $transaction = null;
                if ((boolean)$importConfig->stopOnError === true || $baseImportContext->dryRun === true) {
                    $transaction = Yii::$app->db->beginTransaction();
                }
                for($row = $startRow;  $row < ($endRow + 1); $row ++) {
                    $indexJsonSource = 0;
                    $attributes = [];
                    for($col = $starCol; $col < ($endColumn + 1); $col += 1) {
                        $mappingColumn = ($mappingColumns[$indexJsonSource]) ?? null;
                        if ($mappingColumn instanceof ImportConfigColumn) {
                            $value = $sheet->getCell([$col, $row])->getValue();
                            $attributes[$mappingColumn->source] = $value;
                        }
                        $indexJsonSource += 1;
                    }
                    if (empty($attributes) === false) {
                        if ($rowTransformer instanceof RowTransformerInterface) {
                            try {
                                $result = $rowTransformer->transformRow(
                                    $attributes,
                                    $baseImportContext->withRowNumber($row)
                                );
                                if ($result->handled === true) {
                                    $importJob->successRows++;
                                    continue;
                                }
                                $attributes = $result->attributes ?? $attributes;
                            } catch (RowTransformException $e) {
                                $error = new ImportError($row, '*', $e->getMessage());
                                $errorCollector->add($error);
                                $importJob->errorRows++;
                                if ($importConfig->stopOnError) {
                                    break;
                                }
                                continue;
                            }
                        }
                        if (empty($importConfig->table) === false) {
                            $importResult = static::insertActiveRecord($importConfig, $attributes, $row);
                        } else {
                            $importResult = static::insertSql($importConfig, $attributes, $row);
                        }
                        if ($importResult->success === false) {
                            $importJob->errorRows += 1;
                            /** @var ImportError $error */
                            foreach ($importResult->errors as $error) {
                                $errorCollector->add(
                                    $error
                                );
                            }
                        } else {
                            $importJob->successRows += 1;
                        }
                    }
                }
                if ($errorCollector->hasErrors() === true) {
                    $importJob->status = ImportJob::STATUS_FAILED;
                } else {
                    $importJob->status = ImportJob::STATUS_SUCCESS;
                }
                if ($transaction instanceof Transaction) {
                    if ($baseImportContext->dryRun === true || $errorCollector->hasErrors() === true) {
                        $transaction->rollBack();
                    } else {
                        $transaction->commit();
                    }
                }
            }
            $importJob->errorCollector = $errorCollector;
            if ($importJob->errorRows < 50) {
                $importJob->errors = Json::encode($errorCollector->toCsvRows());
            }
            $importJob->saveFileErrorCsv();
            $importJob->save(false);
            $importJob->refresh();
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ImportConfig $importConfig
     * @param array $attributes
     * @param int $rowNumber
     * @return InsertResult
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public static function insertActiveRecord(ImportConfig $importConfig, array $attributes, int $rowNumber): InsertResult
    {
        try {
            $success = true;
            $errors = [];
            if (class_exists($importConfig->table) === true)  {
                /** @var ActiveRecord $model */
                $model = Yii::createObject($importConfig->table);
                $model->attributes = $attributes;
                if ($model->validate() === true) {
                    $model->save();
                } else {
                    $success = false;
                    foreach ($model->errors as $field => $validateErrors) {
                        foreach ($validateErrors as $message) {
                            $errors[] = new ImportError(
                                rowNumber: $rowNumber,column: $field,message: $message,level: ImportError::LEVEL_VALIDATION_ERROR
                            );
                        }
                    }
                }
            }
            return new InsertResult($success, $errors);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ImportConfig $importConfig
     * @param array $attributes
     * @param int $rowNumber
     * @return InsertResult
     * @throws \yii\db\Exception
     */
    public static function insertSql(ImportConfig $importConfig, array $attributes, int $rowNumber): InsertResult
    {
        try {
            $success = true;
            $errors = [];
            try {
                $viewName = $importConfig->getContextName();
                Yii::$app->db->createCommand()->insert(
                    $viewName,
                    $attributes
                )->execute();
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $success = false;
                $errors[] = new ImportError(
                    rowNumber: $rowNumber,column: '*',message: $e->getMessage()
                );
            }
            return new InsertResult($success, $errors);
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
            return $importConfig->getImportColumns()->count();
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
