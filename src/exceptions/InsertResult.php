<?php
/**
 * InsertResult.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\exceptions
 */
namespace fractalCms\importExport\exceptions;

final class InsertResult
{
    public function __construct(
        public readonly bool $success,
        public readonly array $errors = []
    ) {}
}
