<?php
/**
 * ImportError.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\exceptions
 */
namespace fractalCms\importExport\exceptions;

final class ImportError
{
    const LEVEL_ERROR = 'error';
    const LEVEL_VALIDATION_ERROR = 'validationError';

    public function __construct(
        public readonly int $rowNumber,
        public readonly string $column,
        public readonly string $message,
        public readonly string $level = self::LEVEL_ERROR
    ) {}
}
