<?php
/**
 * ImportErrorCollector.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\exceptions
 */
namespace fractalCms\importExport\exceptions;

final class ImportErrorCollector
{
    private array $errors = [];

    /**
     * @param ImportError $error
     * @return void
     */
    public function add(ImportError $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return bool
     */
    public function hasErrors(): bool
    {
        return empty($this->errors) === false;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->errors;
    }

    /**
     * @return array
     */
    public function toCsvRows(): array
    {
        return array_map(fn($e) => [
            'row'    => $e->rowNumber,
            'column' => $e->column,
            'error'  => $e->message,
        ], $this->errors);
    }
}
