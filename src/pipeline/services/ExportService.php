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

use fractalCms\importExport\pipeline\exports\services\ExportProcessorService;
use fractalCms\importExport\runtime\services\ConfigRuntimeService;
use fractalCms\importExport\runtime\contexts\Export as ExportContext;
use fractalCms\importExport\runtime\contexts\Writer as WriterContext;
use fractalCms\importExport\io\interfaces\DataReader;
use fractalCms\importExport\pipeline\mappers\Column;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\models\ImportConfig;
use yii\helpers\FileHelper;
use Exception;
use fractalCms\importExport\pipeline\interfaces\Export as ExportInterface;
use Yii;

class ExportService implements ExportInterface
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
    protected function executeProcessor(
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
            $baseExportContext = new ExportContext(
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
