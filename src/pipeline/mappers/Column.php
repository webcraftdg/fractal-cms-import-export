<?php
/**
 * Column.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\mappers
 */
namespace fractalCms\importExport\pipeline\mappers;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\ColumnTransformerService;
use fractalCms\importExport\interfaces\DataMapper;
use Exception;
use fractalCms\importExport\models\ImportConfigColumn;
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
            $transformerService = Yii::$container->get(ColumnTransformerService::class);
            foreach ($rawRecord  as $field => $value) {
                /**@var ImportConfigColumn $column */
                $column = $config->findColumnByName($field);
                if (
                    $value !== null
                    && $column !== null
                    && $transformerService instanceof ColumnTransformerService
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