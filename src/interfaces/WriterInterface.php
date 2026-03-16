<?php
/**
 * WriterInterface.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\contexts\Writer as WriterContext;
use fractalCms\importExport\services\exports\writers\WriteTarget;

interface WriterInterface
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
