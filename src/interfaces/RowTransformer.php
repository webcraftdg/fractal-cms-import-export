<?php
/**
 * RowTransformer.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\exceptions\RowTransformerResult;
use fractalCms\importExport\contexts\Import as ImportContext;

interface RowTransformer
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param array $row
     * @param ImportContext $context
     * @return RowTransformerResult
     */
    public function transformRow(array $row, ImportContext $context): RowTransformerResult;
}