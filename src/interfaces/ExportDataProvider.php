<?php
/**
 * ExportDataProvider.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\contexts\Export as ExportContext;
use Traversable;

interface ExportDataProvider
{
    /**
     * Retourne un iterable de lignes prêtes à être exportées
     * Chaque élément représente une "row logique"
     *
     * @return Traversable
     */
    public function getIterator() : Traversable;

    /**
     * @return int
     */
    public function count() : int;
}
