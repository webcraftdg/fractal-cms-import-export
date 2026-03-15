<?php
/**
 * Export.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */

namespace fractalCms\importExport\services;

use fractalCms\importExport\interfaces\ExportDataProvider as ExportDataProviderInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\services\exports\ExportCsv;
use fractalCms\importExport\services\exports\ExportXlsx;
use yii\helpers\FileHelper;
use Exception;
use fractalCms\importExport\services\exports\ExportProcessor;
use fractalCms\importExport\services\exports\writers\CsvWriter;
use fractalCms\importExport\services\imports\mappers\ConfigImport;
use Yii;

class Export
{

    /**
     * @param ImportConfig $importConfig
     * @param int $batchSize
     * @param array $params
     * @return ImportJob
     * @throws Exception
     */
    public static function run(ImportConfig $importConfig, int $batchSize = 1000, array $params = []): ImportJob
    {
        try {
            $filename = 'export_' . date('Ymd_His') . '.csv';
            FileHelper::createDirectory(Yii::getAlias('@runtime'));
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            $dataReader = $importConfig->getDataReader($batchSize);
            $mapper = new ConfigImport();
            $processor = new ExportProcessor();
            switch ($importConfig->fileFormat) {
                case ImportConfig::FORMAT_CSV: 
                    $writer = new CsvWriter();
                    $writer->open(['path' => $path]);
                    break;
            }
            return $processor->run($dataReader, $mapper, $writer, $importConfig, $filename, false, $params);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param ImportConfig $importConfig
     * @param ExportDataProviderInterface $provider
     * @param array $params
     * @return ImportJob
     * @throws Exception
     */
    public static function runWithProvider(ImportConfig $importConfig, ExportDataProviderInterface $provider, array $params = [])
    {
        try {
            switch ($importConfig->fileFormat) {
                case ImportConfig::FORMAT_EXCEL_X:
                case ImportConfig::FORMAT_EXCEL:
                    $importJob = ExportXlsx::run($importConfig, $provider, $params);
                    break;
                default:
                    $importJob = ExportCsv::run($importConfig, $provider, $params);
            }
            return $importJob;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
