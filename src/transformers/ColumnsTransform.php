<?php
/**
 * ColumnsTransform.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\transformers;

use fractalCms\importExport\services\ColumnTransformer as ColumnTransformerService;
use Exception;
use Yii;

class ColumnsTransform
{
    /**
     * @param ColumnTransformerService $transformerService
     * @param array $configColumns
     * @param $row
     * @return array
     * @throws Exception
     */
    protected static function prepareRow(ColumnTransformerService $transformerService, array $configColumns, $row) : array
    {
        try {
            /** @var  ImportConfigColumn $column */
            foreach ($configColumns as $column) {
                $value = $row[$column->source] ?? null;
                if (
                    $value !== null
                    && $transformerService instanceof ColumnTransformerService
                    && $column->transformer !== null
                    && isset($column->transformer['name']) === true
                ) {
                    $value = $transformerService->apply(
                        $column->transformer['name'],
                        $value,
                        $column->transformerOptions
                    );
                }
                $row[$column->source] = $value;
            }
            return  $row;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
