<?php
/**
 * DbView.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

interface DbView
{

    /**
     * @param string $name
     * @param string $sql
     * @return bool
     */
    public function create(string $name, string $sql) : bool;

    /**
     * @param string $name
     * @param string $sql
     * @return bool
     */
    public function replace(string $name, string $sql) : bool;

    /**
     * @param string $name
     * @return bool
     */
    public function drop(string $name) : bool;

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name) : bool;

    /**
     * @param string $name
     * @return array
     */
    public function getColumns(string $name) : array;

}