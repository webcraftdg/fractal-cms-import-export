<?php
/**
 * Import.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\runtime\contexts
 */
namespace fractalCms\importExport\runtime\contexts;

final class Writer
{


    /**
     * constructor
     *
     * @param  string $absolutePath
     * @param  string $relativePath
     * @param  array  $preamble
     * @param  array  $params
     */
    public function __construct(
        public string $absolutePath,
        public string $relativePath,
        public array $preamble = [],
        public array $params = []
    ) {

    }
}
