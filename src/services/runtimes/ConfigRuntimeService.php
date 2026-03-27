<?php
/**
 * ConfigRuntimeService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */

namespace fractalCms\importExport\services\runtimes;

use fractalCms\importExport\interfaces\ConfigRuntime as ConfigRuntimeInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\interfaces\DataReader;
use fractalCms\importExport\services\exports\readers\QueryDataReader;
use fractalCms\importExport\services\exports\readers\SqlDataReader;
use fractalCms\importExport\interfaces\Writer;
use fractalCms\importExport\services\exports\writers\CsvWriter;
use fractalCms\importExport\services\exports\writers\JsonWriter;
use fractalCms\importExport\services\exports\writers\NDJsonWriter;
use fractalCms\importExport\services\exports\writers\XlsxWriter;
use fractalCms\importExport\services\exports\writers\XmlWriter;
use fractalCms\importExport\interfaces\CountableDataReader;
use fractalCms\importExport\models\LimiterModel;
use InvalidArgumentException;
use yii\db\Query;
use Exception;
use Yii;

class ConfigRuntimeService implements ConfigRuntimeInterface
{

     /**
      * get Data Reader
      *
      * @param  ImportConfig $config
      * @param  int          $batchSize
      *
      * @return DataReader | null
      */
    public function getDataReader(ImportConfig $config, int $batchSize = 1000) : DataReader | null
    {
        try {
            $dataReader = null;
            if ($config->sourceType === ImportConfig::SOURCE_TYPE_TABLE) {
                $cols = $this->getHeaderColumns($config);
                $statementName = $config->getContextName();
                $query = new Query();
                $query->select($cols);
                $query->from($statementName);
                $dataReader = new QueryDataReader();
                $dataReader->open(['query' => $query, 'batchSize' => $batchSize]);
            } elseif($config->sourceType === ImportConfig::SOURCE_TYPE_SQL) {
                $dataReader = new SqlDataReader();
                $dataReader->open(['command' => Yii::$app->db->createCommand($config->sql)]);
            }
            return  $dataReader;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * get export Name
     *
     * @param  ImportConfig $config
     *
     * @return string
     */
    public function getExportFileName(ImportConfig $config): string
    {
        try {
            $fileName = 'export_' . date('Ymd_His');
            switch ($config->fileFormat) {
                case ImportConfig::FORMAT_CSV: 
                    $fileName =  $fileName . '.csv';
                    break;
                case ImportConfig::FORMAT_EXCEL:
                case ImportConfig::FORMAT_EXCEL_X: 
                    $fileName =  $fileName . '.xlsx';
                    break;
                case ImportConfig::FORMAT_JSON:
                case ImportConfig::FORMAT_NDJSON:
                    $fileName =  $fileName . '.json';
                    break;
                case ImportConfig::FORMAT_XML:
                    $fileName =  $fileName . '.xml';
                    break;
                default: 
                    throw new InvalidArgumentException('ImportConfig : getExportFileName, format not found');    
            }
            return $fileName;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * get Export Preamble
     *
     * @param  ImportConfig $config
     *
     * @return array
     */
    public function getExportPreamble(ImportConfig $config): array
    {
         try {
            $preamble = [];
            switch ($config->fileFormat) {
                case ImportConfig::FORMAT_CSV: 
                case ImportConfig::FORMAT_EXCEL:
                case ImportConfig::FORMAT_EXCEL_X:
                    $preamble = $this->getHeaderColumns($config, false);
                    break;
            }
            return $preamble;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }
    
    /**
     * get Header Columns
     *
     * @param  ImportConfig $config
     * @param  bool         $isSource
     *
     * @return array
     */
    public function getHeaderColumns(ImportConfig $config, bool $isSource = true): array
    {
        try {
            $cols = [];
            $query = $config->getImportColumns();
            /** @var ImportConfigColumn $importColumn */
            foreach ($query->each() as $importColumn) {
                $col = $importColumn->source;
                if ($isSource === false) {
                    $col = $importColumn->target;
                }
                $cols[] = $col;
            }
            return $cols;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * create writer
     *
     * @param  ImportConfig $config
     *
     * @return Writer
     */
    public function createWriter(ImportConfig $config): Writer
    {
        try {
            switch ($config->fileFormat) {
                case ImportConfig::FORMAT_CSV: 
                    $writer = new CsvWriter();
                    break;
                case ImportConfig::FORMAT_EXCEL:
                case ImportConfig::FORMAT_EXCEL_X: 
                    $writer = new XlsxWriter();
                    break;
                case ImportConfig::FORMAT_JSON:
                    $writer = new JsonWriter($config);
                    break;
                case ImportConfig::FORMAT_NDJSON: 
                    $writer = new NDJsonWriter($config);
                    break;    
                case ImportConfig::FORMAT_XML:
                    $writer = new XmlWriter($config);
                    break;
                default: 
                    throw new InvalidArgumentException('ImportConfig : createWriter, format not found');    
            }
            return $writer;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * get Limits
     *
     * @param  ImportConfig $config
     *
     * @return LimiterModel
     */
    public function getLimits(ImportConfig $config) : LimiterModel
    {
        try {
            $limiter = Yii::createObject(LimiterModel::class);
            $limiter->scenario = LimiterModel::SCENARIO_CREATE;
            $limiter->rows = $this->estimateRows($config);
            $limiter->format = ($config->fileFormat) ?? ImportConfig::FORMAT_CSV;
            $limiter->columns = $this->estimateColumns($config);
            $limiter->estimatedMb = $this->estimateSizeMb($limiter->rows, $limiter->columns);
            return $limiter;
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw $e;
        }
    }

    /**
     * @param ImportConfig $config
     * @return int
     * @throws Exception
     */
    public function estimateRows(ImportConfig $config): int
    {
        try {
            $dataReader = $this->getDataReader($config);
            $count = -1;
            if ($dataReader instanceof CountableDataReader) {
                $count = $dataReader->count();
            }
            return $count;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ImportConfig $config
     * @return int
     * @throws Exception
     */
    public function estimateColumns(ImportConfig $config): int
    {
        try {
            return $config->getImportColumns()->count();
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param int $rows
     * @param int $columns
     * @param int $cellSize
     * @return int
     */
    public function estimateSizeMb(int $rows, int $columns, int $cellSize = 20): int
    {
        $bytes = $rows * $columns * $cellSize;
        return (int) round($bytes / 1024 / 1024);
    }
}
