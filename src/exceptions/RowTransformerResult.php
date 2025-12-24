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
    /**
     * Contructeur
     *
     * handled = true, le traitement est fini le service passera à la ligne suivante
     * handled = false, le service doit continuer
     *
     * @param array|null $attributes
     * @param bool $handled
     */
    public function __construct(
        public readonly ?array $attributes = null,
        public readonly bool $handled = false
    ) {}
}
