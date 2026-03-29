<?php
/**
 * ConfigColumnsPersistenceService.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\configuration\services
 */

namespace fractalCms\importExport\configuration\services;

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportConfigColumn;
use fractalCms\importExport\database\services\SourceColumnsResolver;
use fractalCms\importExport\exceptions\ImportError;
use Exception;
use Yii;

class ConfigColumnsPersistenceService
{


    /**
     * process
     *
     * @param  \fractalCms\importExport\models\ImportConfig $config
     * @param  array                                        $columns
     *
     * @return array
     */
    public function process(ImportConfig $config, array $columns) : array
    {
        try {
            $columnResolver = null;
            if (Yii::$container->has(SourceColumnsResolver::class) === true) {
                $columnResolver = Yii::$container->get(SourceColumnsResolver::class);
            }
            $index = 0;
            $prevIndex= -1;
            $errors = [];
            foreach ($columns as $orderColumn => $column) {
                $importColumn = null;
                if (isset($column['id']) === true) {
                    /** @var ImportConfigColumn $importColumn */
                    $importColumn = ImportConfigColumn::findOne($column['id']);
                }
                if ($importColumn === null) {
                    $importColumn = Yii::createObject(ImportConfigColumn::class);
                    $importColumn->scenario = ImportConfigColumn::SCENARIO_CREATE;
                    $importColumn->importConfigId = $config->id;
                } else {
                    $importColumn->scenario = ImportConfigColumn::SCENARIO_UPDATE;
                    $importColumn->transformer = null;
                    $importColumn->transformerOptions = null;
                }
                $importColumn->tmpTransformer = ($column['transformer']) ?? null;
                $importColumn->tmpTransformerOptions = ($column['transformerOptions']) ?? null;
                unset($column['transformer']);
                if ($importColumn->tmpTransformerOptions !== null) {
                    unset($column['transformerOptions']);
                }
                $importColumn->attributes = $column;
                if (empty($importColumn->order) === true) {
                    $order = ($prevIndex > -1) ? ($prevIndex + 0.5) : $index;
                    $importColumn->order = $order;
                } else {
                    $prevIndex = $importColumn->order;
                }
                if ($importColumn->validate() === true) {
                    $importColumn->save();
                } else {
                    foreach ($importColumn->errors as $field => $error) {
                        $errors[] = new ImportError(
                            rowNumber: $orderColumn,
                            column: $field,
                            message: 'Colonne : '.$importColumn->source.':'.$importColumn->getFirstError($field),
                            level: ImportError::LEVEL_VALIDATION_ERROR
                        );
                    }
                }

                if ($columnResolver instanceof SourceColumnsResolver) {
                    $columnName = ($config->isImport() === true) ? $importColumn->target : $importColumn->source;
                    $columnExist = $columnResolver->columnExistsForConfig($config, $columnName);
                    if ($columnExist === false && empty($config->rowProcessor) === true) {
                        $errors[] = new ImportError(
                            rowNumber: $orderColumn,
                            column: $columnName,
                            message: 'Colonne : '.$columnName.': Pour une Colonne le convertisseur métier est obligatoire',
                            level: ImportError::LEVEL_ERROR
                        );
                    }
                }
                $index++;
            }
            if (empty($errors) === true) {
                $config->reorderColumns();
            }
            return $errors;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
