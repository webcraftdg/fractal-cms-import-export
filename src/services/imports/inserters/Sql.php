<?php
/**
 * Sql.php
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

class Sql implements ImportInserter {
    
    

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
            return $this->insertSql($config, $attributes, $rowNumber);
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
     * @throws \yii\db\Exception
     */
    protected function insertSql(ImportConfig $importConfig, array $attributes, int $rowNumber): InsertResult
    {
        try {
            $success = true;
            $errors = [];
            try {
                $viewName = $importConfig->getContextName();
                Yii::$app->db->createCommand()->insert(
                    $viewName,
                    $attributes
                )->execute();
            } catch (Exception $e) {
                Yii::error($e->getMessage(), __METHOD__);
                $success = false;
                $errors[] = new ImportError(
                    rowNumber: $rowNumber,column: '*',message: $e->getMessage()
                );
            }
            return new InsertResult($success, $errors);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}