<?php
/**
 * RowTransformerResult.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\exceptions
 */
namespace fractalCms\importExport\exceptions;

final class RowTransformerResult
{
    public function __construct(
        public readonly ?array $attributes = null,
        public readonly bool $handled = false
    ) {}
}
