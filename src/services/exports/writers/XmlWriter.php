<?php
/**
 * XmlWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services\writers
 */
namespace fractalCms\importExport\services\exports\writers;

use fractalCms\importExport\contexts\Writer as WriterContext;
use fractalCms\importExport\interfaces\WriterInterface;
use fractalCms\importExport\models\ImportConfig;
use XMLWriter as GlobalXMLWriter;
use Exception;
use InvalidArgumentException;
use Yii;

class XmlWriter implements WriterInterface
{
    /**
     * @var xmlWriter
     */
    private GlobalXMLWriter $xmlWriter;
    private ImportConfig $config;
    /**
     * @var resource | false
     */
    private  $f;

     /**
     * open
     *
     * @param  array $params
     *
     * @return void
     */
    public function open(WriterContext $writerContext): void
    {
        try {
            $path = $writerContext->absolutePath ?? null;
            if ($path === null) {
                throw new InvalidArgumentException('XmlWriter params "path" not found');
            }
            $this->xmlWriter->openUri($path);
            $this->xmlWriter->startDocument('1.0', 'UTF-8');
            $this->xmlWriter->startElement('export');
            $this->xmlWriter->writeAttribute('name', $this->config->name);
            $this->xmlWriter->writeAttribute('dateCreate', date('c', strtotime($this->config->dateCreate)));
            $this->xmlWriter->writeAttribute('generated_at', date('c'));
            $this->xmlWriter->startElement('rows');
            $this->xmlWriter->setIndent(true);          // Active l'indentation
            $this->xmlWriter->setIndentString('  ');
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * __construct
     *
     * @param  ImportConfig $importConfig
     */
    public function __construct(ImportConfig $importConfig)
    {
        $xmlWriter = new GlobalXMLWriter();
        $this->xmlWriter = $xmlWriter;
        $this->config = $importConfig;
    }


    /**
     * @param WriteTarget $target
     * @param array $row
     * @return void
     * @throws Exception
     */
    public function write(WriteTarget $target, array $row): void
    {
        try {
            if (empty($row) === false) {
                $this->xmlWriter->startElement('row');
                foreach ($row as $field => $value) {
                // $value1 = trim($value);
                    $this->xmlWriter->writeElement($field, $value);
                }
                $this->xmlWriter->endElement();
            }
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

      /**
     * close
     *
     * @return void
     */
    public function close(WriterContext $writerContext): void
    {
        try {
            $this->xmlWriter->endElement(); // rows
            $this->xmlWriter->endElement(); // </export>
            $this->xmlWriter->endDocument();
            $this->xmlWriter->flush();
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
