<?php
/**
 * XmlReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services\imports\readers;


use fractalCms\importExport\interfaces\ImportReader;
use XMLReader as GlobalXMLReader;
use Exception;
use Yii;

class XmlReader implements ImportReader
{


    
    private GlobalXMLReader $xmlReader;
    private $batchSize = 500;

    /**
     * open
     *
     * @param  string $filePath
     * @param  array  $options
     *
     * @return void
     */
    public function open(string $filePath, array $options = []): void
    {
        try {
            $this->xmlReader = new GlobalXMLReader();
            $this->xmlReader->open($filePath);
            $this->batchSize = ($options['batchSize']) ?? $this->batchSize;

        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * read
     *
     * @return iterable
     */
    public function read(): iterable
    {
         try {
    
            $batch = [];
            $indexBatch = 0;
    
            while ($this->xmlReader->read() === true) {
                    if (
                        $this->xmlReader->nodeType === GlobalXMLReader::ELEMENT
                        && $this->xmlReader->name === 'fields'
                    ) {
                        $batch[] = $this->getRowValues();
                        $indexBatch ++;
                        if ($indexBatch >= $this->batchSize) {
                            yield $batch;
                            $batch = [];
                            $indexBatch = 0;
                        }
                    }
              
            }
            
            if (empty($batch) === false) {
                yield $batch;
            }

        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * get row values
     *
     * @return void
     */
    public function getRowValues()
    {
        try {
            $row = [];
            $depth = $this->xmlReader->depth;

            while ($this->xmlReader->read()) {
                // fin du record
                if ($this->xmlReader->nodeType === GlobalXMLReader::END_ELEMENT 
                    && $this->xmlReader->name === 'fields' 
                    && $this->xmlReader->depth === $depth) {
                    break;
                }

                if ($this->xmlReader->nodeType === GlobalXMLReader::ELEMENT && $this->xmlReader->name === 'field') {
                    $name = $this->xmlReader->getAttribute('label');
                    if ($name === null || $name === '') {
                        continue;
                    }

                    $value = $this->readFieldValue();
                    $row[$name] = $value;
                }
            }
            return $row;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * read field value
     *
     * @return string
     */
    private function readFieldValue(): string
    {

     try {
            $value = '';

            while ($this->xmlReader->read()) {
                if ($this->xmlReader->nodeType === GlobalXMLReader::TEXT || $this->xmlReader->nodeType === GlobalXMLReader::CDATA) {
                    $value .= $this->xmlReader->value;
                }
                if (
                    $this->xmlReader->nodeType === GlobalXMLReader::END_ELEMENT
                    && $this->xmlReader->name === 'field'
                ) {
                    break;
                }
            }

            return $value; 
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * close
     *
     * @return void
     */
    public function close(): void
    {
        try {
            $this->xmlReader->close();
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
