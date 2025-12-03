<?php
/**
 * Transform.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

interface Transform
{

    /**
     * @param mixed $data
     * @return mixed
     */
    public static function apply(mixed $data) : mixed;
}
