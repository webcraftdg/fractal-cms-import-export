<?php
/**
 * DataReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

interface DataReader 
{

    /**
     * open
     *
     * @param  array        $options
     *
     * @return void
     */
    public function open(array $options): void;
    /**
     *  read
     *
     * @return iterable
     */
    public function read(): iterable;
    /**
     * close
     *
     * @return void
     */
    public function close(): void;
}