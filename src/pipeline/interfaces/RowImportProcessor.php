<?php
/**
 * RowImportProcessor.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\interfaces
 */
namespace fractalCms\importExport\pipeline\interfaces;

use fractalCms\importExport\exceptions\RowProcessorResult;
use fractalCms\importExport\runtime\contexts\Import as ImportContext;

interface RowImportProcessor
{

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * process
     *
     * @param  array              $row
     * @param  ImportContext      $context
     * @param  array              $params
     *
     * @return RowProcessorResult
     */
    public function process(array $row, ImportContext $context, array $params = []): RowProcessorResult;
}