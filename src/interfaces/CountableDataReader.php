<?php
/**
 * CountableDataReader.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

interface CountableDataReader extends DataReader
{

    /**
     * @return int
     */
    public function count() : int;
}
