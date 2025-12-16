<?php
/**
 * WriteTarget.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services\writers
 */
namespace fractalCms\importExport\writers;

class WriteTarget
{
    public function __construct(
        public readonly string $sheet,
        public readonly int $row = 1,
        public readonly int $col = 1,
        public readonly ?string $style = null
    ) {}
}
