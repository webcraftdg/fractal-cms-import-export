<?php
/**
 * RowExportProcessor.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\interfaces
 */
namespace fractalCms\importExport\pipeline\interfaces;

use fractalCms\importExport\exceptions\RowProcessorResult;
use fractalCms\importExport\contexts\Export as ExportContext;

interface RowExportProcessor
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * process
     *
     * @param  array              $row
     * @param  ExportContext      $context
     * @param  array              $params
     *
     * @return RowProcessorResult
     */
    public function process(array $row, ExportContext $context, array $params = []): RowProcessorResult;
}