<?php
/**
 * RowExportProcessor.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\exceptions\RowProcessorResult;
use fractalCms\importExport\contexts\Export as ExportContext;

interface RowExportProcessor
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param array $row
     * @param ExportContext $context
     * @return RowProcessorResult
     */
    public function process(array $row, ExportContext $context): RowProcessorResult;
}