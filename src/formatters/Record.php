<?php
/**
 * Record.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\formatters
 */
namespace fractalCms\importExport\formatters;

use fractalCms\importExport\models\ImportConfig;
use Exception;
use fractalCms\importExport\interfaces\RecordFormatter;
use Yii;

class Record implements RecordFormatter 
{


    /**
     * map
     *
     * @param  array        $rawRecord
     * @param  ImportConfig $config
     * @param  int|string   $rowNumber
     *
     * @return array
     */
    public function format(array $rawRecord, ImportConfig $config): array
    {
         try {
            $attributes = [];
            foreach ($rawRecord  as $field => $value) {
                $rawColumn = [];
                $column = $config->findColumnByName($field);

                if ($column !== null) {
                    $rawColumn['name']  = $column->source;
                    $rawColumn['columnId']  = $column->id;
                }
                $rawColumn['label'] = $field;
                $rawColumn['value']  = $value;
                $attributes[$field] = $rawColumn;
            }
            return $attributes;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}