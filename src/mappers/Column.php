<?php
/**
 * Column.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\mappers
 */
namespace fractalCms\importExport\mappers;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\ColumnTransformer;
use fractalCms\importExport\interfaces\DataMapper;
use Exception;
use Yii;

class Column implements DataMapper {


    /**
     * map
     *
     * @param  array        $rawRecord
     * @param  ImportConfig $config
     * @param  int|string   $rowNumber
     *
     * @return array
     */
    public function map(array $rawRecord, ImportConfig $config, int|string $rowNumber): array
    {
        try {
            $attributes = [];
            $transformerService = Yii::$container->get(ColumnTransformer::class);
            foreach ($rawRecord  as $field => $value) {
                $column = $config->findColumnByName($field);
                if (
                    $value !== null
                    && $column !== null
                    && $transformerService instanceof ColumnTransformer
                    && $column->transformer !== null
                    && empty($column->transformer['name']) === false
                ) {
                    $value = $transformerService->apply(
                        $column->transformer['name'],
                        $value,
                        $column->transformerOptions
                    );
                }
                if ($column !== null) {
                    $attributes[$column->target]  = $value;
                } else {
                    $attributes[$field]  = $value;
                }
            }
            return $attributes;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}