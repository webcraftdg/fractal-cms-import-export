<?php

namespace fractalCms\importExport\services;

use fractalCms\importExport\interfaces\Parameter as ParameterInterface;
use fractalCms\importExport\Module;
use Exception;
use Yii;
use yii\helpers\Json;

class Parameter implements ParameterInterface
{

    protected $parameterTables = [];

    /**
     * @return array
     * @throws Exception
     */
    public function getTables(): array
    {
        try {
            $tables = [];
            $pathsNamespacesModels = Module::getInstance()->pathsNamespacesModels;
            $paramsKeys = sha1(Json::encode($pathsNamespacesModels));
            if (isset($this->parameterTables[$paramsKeys]) === false) {
                $this->parameterTables = [];
                $dbTables = Yii::$app->db->getSchema()->tableNames;
                foreach ($pathsNamespacesModels as $pathModel => $namespaceModel) {
                    $path = Yii::getAlias($pathModel);
                    if(file_exists($path) === true && is_dir($path) === true) {
                        foreach (scandir($path) as $modelFile) {
                            $pathFile = $path.'/'.$modelFile;
                            $baseName = pathinfo($pathFile, PATHINFO_FILENAME);
                            if (is_file($pathFile) === true && preg_match('/\.php$/', $modelFile) == 1) {
                                try {
                                    $class = $namespaceModel.$baseName;
                                    if(class_exists($class) === true && is_subclass_of($class, \yii\db\ActiveRecord::class)) {
                                        $tableName = $class::tableName();
                                        $tableName = static::normalizeTableName($tableName);
                                        $find = static::findTable($dbTables, $tableName);
                                        if ($find !== false) {
                                            $tables[$class] = $find;
                                        }
                                    }
                                } catch (Exception $e) {
                                    Yii::error($e->getMessage(), __METHOD__);
                                }
                            }
                        }
                    }

                }
                $this->parameterTables[$paramsKeys] = $tables;
            }
            return $this->parameterTables[$paramsKeys];
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * Find in tables
     *
     * @param array $tables
     * @param string $tableName
     * @return string|false
     * @throws Exception
     */
    public function findTable(array $tables, string $tableName): string|false
    {
        try {
            return in_array($tableName, $tables) === true ? $tableName : false;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }

    /**
     * @param string $tableName
     * @return string
     */
    private static function normalizeTableName(string $tableName): string
    {
        return str_replace(['{', '}', '%'], '', $tableName);
    }

}
