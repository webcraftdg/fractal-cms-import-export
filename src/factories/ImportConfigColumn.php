<?php
/**
 * ImportConfigColumn.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\factories
 */
namespace fractalCms\importExport\factories;

use Exception;
use Yii;

class ImportConfigColumn
{
    /**
     * create from source columns
     *
     * @param  array  $columns
     *
     * @return array
     */
    public function createFromSourceColumns(array $columns): array
    {
       try {
            $modelColumns = array_map(function($column) {
                    $newColumn = [];
                    $newColumn['source'] = $column['name'];
                    $newColumn['target'] = $column['name'];
                    $newColumn['format'] = ($column['format']) ?? 'string';
                    return $newColumn;
                }, $columns);
            return $modelColumns;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}