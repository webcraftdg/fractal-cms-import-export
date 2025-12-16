<?php
/**
 * RowImportTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\exceptions\RowTransformerResult;
use fractalCms\importExport\contexts\Export as ExportContext;

interface RowExportTransformer
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param array $row
     * @param ExportContext $context
     * @return RowTransformerResult
     */
    public function transformRow(array $row, ExportContext $context): RowTransformerResult;
}