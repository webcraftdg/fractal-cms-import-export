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
namespace fractalCms\importExport\writers;

use fractalCms\importExport\interfaces\ExportWriter;
use Exception;
use fractalCms\importExport\models\ImportConfig;
use XMLWriter as GlobalXMLWriter;
use Yii;

class XmlWriter implements ExportWriter
{
    /**
     * @var xmlWriter
     */
    private GlobalXMLWriter $xmlWriter;
    /**
     * @var resource | false
     */
    private  $f;


    /**
     * @param ImportConfig $importConfig
     * @param string $filename
     */
    public function __construct(ImportConfig $importConfig, string $fileName)
    {
        $xmlWriter = new GlobalXMLWriter();
        $xmlWriter->openUri($fileName);
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElement('export');
        $xmlWriter->writeAttribute('name', $importConfig->name);
        $xmlWriter->writeAttribute('dateCreate', date('c', strtotime($importConfig->dateCreate)));
        $xmlWriter->writeAttribute('generated_at', date('c'));
        $xmlWriter->startElement('rows');
        $xmlWriter->setIndent(true);          // Active l'indentation
        $xmlWriter->setIndentString('  '); 
        $this->xmlWriter = $xmlWriter;
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
            $this->xmlWriter->startElement('row');
            foreach ($row as $field => $value) {
              // $value1 = trim($value);
               $this->xmlWriter->writeElement($field, $value);
            }
            $this->xmlWriter->endElement(); // row
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $filePath
     * @return void
     */
    public function save(string $filePath): void
    {
        $this->xmlWriter->endElement(); // rows
        $this->xmlWriter->endElement(); // </export>
        $this->xmlWriter->endDocument();
        $this->xmlWriter->flush();
    }
}
