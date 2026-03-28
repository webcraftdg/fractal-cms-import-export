<?php
/**
 * Writer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\io\interfaces
 */
namespace fractalCms\importExport\io\interfaces;

use fractalCms\importExport\runtime\contexts\Writer as WriterContext;
use fractalCms\importExport\io\exports\writers\WriteTarget;

interface Writer
{
    /**
     * open
     *
     * @param  array] $params
     *
     * @return void
     */
    public function open(WriterContext $writerContext): void;

    /**
     * @param WriteTarget $target
     * @param array $row
     * @return void
     */
    public function write(WriteTarget $target, array $row): void;

    /**
     * close
     *
     * @return void
     */
    public function close(WriterContext $writerContext): void;
}
