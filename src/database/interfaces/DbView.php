<?php
/**
 * DbView.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\database\interfaces
 */
namespace fractalCms\importExport\database\interfaces;


interface DbView
{

    /**
     * @param string $name
     * @param string $sql
     * @return int
     */
    public function create(string $name, string $sql) : int;

    /**
     * @param string $name
     * @param string $sql
     * @return bool
     */
    public function replace(string $name, string $sql) : int;

    /**
     * @param string $name
     * @return bool
     */
    public function drop(string $name) : int;

    /**
     * @param string $name
     * @return bool
     */
    public function exists(string $name) : bool;
}
