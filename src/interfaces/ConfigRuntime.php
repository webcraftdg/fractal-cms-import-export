<?php
/**
 * ConfigRuntime.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\interfaces
 */
namespace fractalCms\importExport\interfaces;

use fractalCms\importExport\models\ImportConfig;

interface ConfigRuntime
{
    /**
     * get Data reader
     *
     * @param  ImportConfig $config
     * @param  int          $batchSize
     *
     * @return DataReader | null
     */
    public function getDataReader(ImportConfig $config, int $batchSize = 1000) : DataReader | null;

    /**
     * get Export File Name
     *
     * @param  ImportConfig $config
     *
     * @return string
     */
    public function getExportFileName(ImportConfig $config) : string;

    /**
     * get Export Preamble
     *
     * @param  ImportConfig $config
     *
     * @return array
     */
    public function getExportPreamble(ImportConfig $config) : array;

    /**
     * get header columns
     *
     * @param  ImportConfig $config
     * @param  bool         $isSource
     *
     * @return array
     */
    public function getHeaderColumns(ImportConfig $config, bool $isSource = true) : array;

    /**
     * create writer
     *
     * @param  ImportConfig $config
     *
     * @return Writer
     */
    public function createWriter(ImportConfig $config) : Writer;

}