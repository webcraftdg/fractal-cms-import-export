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
namespace fractalCms\importExport\services\exports\writers;

use fractalCms\importExport\contexts\Writer as WriterContext;
use fractalCms\importExport\interfaces\WriterInterface;
use yii\helpers\Json;
use InvalidArgumentException;
use Exception;
use Yii;

class JsonWriter implements WriterInterface
{
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
                throw new InvalidArgumentException('JsonWriter params "path" not found');
            }
            $this->f = fopen($path, 'w');
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
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

    /**
     * close
     *
     * @return void
     */
    public function close(WriterContext $writerContext): void
    {
        try {
            fclose($this->f);
        } catch (Exception $e) {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
