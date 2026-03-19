<?php
/**
 * ActiveRecord.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services\imports\inserters
 */
namespace fractalCms\importExport\services\imports\inserters;

use fractalCms\importExport\exceptions\InsertResult;
use fractalCms\importExport\interfaces\ImportInserter;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\exceptions\ImportError;
use Exception;
use Yii;

class ActiveRecord implements ImportInserter 
{
    

    /**
     * insert
     *
     * @param  ImportConfig                                     $config
     * @param  array                                            $attributes
     * @param  int|string                                       $rowNumber
     *
     * @return \fractalCms\importExport\exceptions\InsertResult
     */
    public function insert(ImportConfig $config, array $attributes, int|string $rowNumber): InsertResult
    {
         try {
            return $this->insertActiveRecord($config, $attributes, $rowNumber);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

      /**
     * @param ImportConfig $importConfig
     * @param array $attributes
     * @param int $rowNumber
     * @return InsertResult
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    protected function insertActiveRecord(ImportConfig $importConfig, array $attributes, int $rowNumber): InsertResult
    {
        try {
            $success = true;
            $errors = [];
            if (class_exists($importConfig->table) === true)  {
                /** @var ActiveRecord $model */
                $model = Yii::createObject($importConfig->table);
                foreach($attributes as $attribute => $value){
                    if ($model->hasAttribue($attribute) === true) {
                        $model->$attribute = $value;
                    }
                }
                if ($model->validate() === true) {
                    try {
                        $model->save();
                    } catch(Exception $e) {
                        Yii::error($e->getMessage(), __METHOD__);
                        $errors[] = new ImportError(
                            rowNumber: $rowNumber,column: 'Save model',message: $e->getMessage(),level: ImportError::LEVEL_VALIDATION_ERROR
                        );
                    }
                } else {
                    $success = false;
                    foreach ($model->errors as $field => $validateErrors) {
                        foreach ($validateErrors as $message) {
                            $errors[] = new ImportError(
                                rowNumber: $rowNumber,column: $field,message: $message,level: ImportError::LEVEL_VALIDATION_ERROR
                            );
                        }
                    }
                }
            }
            return new InsertResult($success, $errors);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}