<?php
/**
 * ConfigImport.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services\imports\mappers;

use fractalCms\importExport\interfaces\ImportMapper;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\ColumnTransformer;
use Exception;
use Yii;

class ConfigImport implements ImportMapper {


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
                $column = $config->getImportColumns()->andWhere(['source' => $field])->one();

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