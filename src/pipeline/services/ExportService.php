<?php
/**
 * ExportService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\services
 */

namespace fractalCms\importExport\pipeline\services;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\contexts\Export as ContextsExport;
use fractalCms\importExport\contexts\Writer as WriterContext;
use fractalCms\importExport\interfaces\DataReader;
use fractalCms\importExport\mappers\Column;
use fractalCms\importExport\services\exports\ExportProcessorService;
use fractalCms\importExport\services\runtimes\ConfigRuntimeService;
use yii\helpers\FileHelper;
use Exception;
use Yii;

class ExportService
{

    public function __construct(
        private ConfigRuntimeService $configRuntimeService
    )
    {
    }

    /**
     * @param ImportConfig $config
     * @param int $batchSize
     * @param array $params
     * @return ImportJob
     * @throws Exception
     */
    public function run(ImportConfig $config, int $batchSize = 1000, array $params = []): ImportJob
    {
        try {
            return $this->executeProcessor(
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
    public function runWithDataReader(ImportConfig $config, DataReader $dataReader, array $params = []) : ImportJob
    {
        try {
            return $this->executeProcessor(
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
    public function executeProcessor(
        ImportConfig $config,
        ?DataReader $dataReader = null,
        int $batchSize = 1000,
        array $params = []
        ) : ImportJob
    {
          try {
            $filename = $this->configRuntimeService->getExportFileName($config);
            FileHelper::createDirectory(Yii::getAlias('@runtime'));
            $path = Yii::getAlias('@runtime') . '/' . $filename;
            if($dataReader === null) {
                $dataReader = $this->configRuntimeService->getDataReader($config, $batchSize);
            }
            $writer =  $this->configRuntimeService->createWriter($config);
            $mapper = new Column();
            $processor = new ExportProcessorService();
            $baseExportContext = new ContextsExport(
                config: $config,
                dryRun: false,
                hasPreamble:false,
                rowNumber: -1,
                writer: $writer,
                writerContext: new WriterContext(
                    absolutePath:$path,
                    relativePath:'@runtime/'.$filename,
                    preamble: $this->configRuntimeService->getExportPreamble($config)
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
