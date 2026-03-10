<?php
/**
 * JsonWriter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services\writers
 */
namespace fractalCms\importExport\writers;

use fractalCms\importExport\interfaces\ExportWriter;
use yii\helpers\Json;
use Exception;
use Yii;

class JsonWriter implements ExportWriter
{
    /**
     * @var resource | false
     */
    private  $f;

    /**
     * @param resource $f
     */
    public function __construct($f)
    {
        $this->f = $f;
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
            fputs($this->f, Json::encode($row)."\n");
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
