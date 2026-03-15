<?php
/**
 * RowImportProcessor.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\exceptions\RowProcessorResult;
use fractalCms\importExport\contexts\Import as ImportContext;

interface RowImportProcessor
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param array $row
     * @param ImportContext $context
     * @return RowProcessorResult
     */
    public function process(array $row, ImportContext $context): RowProcessorResult;
}