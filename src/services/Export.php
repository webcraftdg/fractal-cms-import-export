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

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use yii\helpers\FileHelper;
use Exception;
use fractalCms\importExport\contexts\Export as ContextsExport;
use fractalCms\importExport\contexts\Writer as WriterContext;
use fractalCms\importExport\interfaces\DataReader;
use fractalCms\importExport\mappers\Column;
use fractalCms\importExport\services\exports\ExportProcessor;
use Yii;

class Export
{

    /**
     * @param ImportConfig $config
     * @param int $batchSize
     * @param array $params
     * @return ImportJob
     * @throws Exception
     */
    public static function run(ImportConfig $config, int $batchSize = 1000, array $params = []): ImportJob
    {
        try {
            return static::executeProcessor(
                config:$config,
                dataReader:null,
                batchSize:$batchSize,
                params:$params
            );
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * run with DataReder
     *
     * @param  ImportConfig $config
     * @param  DataReader   $dataReader
     * @param  array        $params
     *
     * @return ImportJob
     */
    public static function runWithDataReader(ImportConfig $config, DataReader $dataReader, array $params = []) : ImportJob
    {
        try {
            return static::executeProcessor(
                config: $config,
                dataReader: $dataReader,
                batchSize: 1000,
                params: $params
            );
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * execute Processor
     *
     * @param  ImportConfig $config
     * @param  DataReader   $dataReader
     * @param  int          $batchSize
     * @param  array        $params
     *
     * @return ImportJob
     */
    public static function executeProcessor(
        ImportConfig $config,
        ?DataReader $dataReader = null,
        int $batchSize = 1000,
        array $params = []
        ) : ImportJob
    {
          try {
            $filename = $config->getExportFileName();
            FileHelper::createDirectory(Yii::getAlias('@runtime'));
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            if($dataReader === null) {
                $dataReader = $config->getDataReader($batchSize);
            }
            $writer = $config->createWriter();
            $mapper = new Column();
            $processor = new ExportProcessor();
            $baseExportContext = new ContextsExport(
                config: $config,
                dryRun: false,
                hasPreamble:false,
                rowNumber: -1,
                writer: $writer,
                writerContext: new WriterContext(
                    absolutePath:$path,
                    relativePath:'@runtime/'.$filename,
                    preamble: $config->getExportPreamble()
                ),
                sectionName:'export',
                params: $params
            );
            return $processor->run(
                reader:$dataReader,
                mapper: $mapper,
                context: $baseExportContext,
                filePath: $filename,
                isTest: false,
                params: $params
            );
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
