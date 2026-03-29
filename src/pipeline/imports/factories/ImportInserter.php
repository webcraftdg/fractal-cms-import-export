<?php
/**
 * ImportInserter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\pipeline\imports\factories
 */
namespace fractalCms\importExport\pipeline\imports\factories;


use fractalCms\importExport\pipeline\imports\inserters\ActiveRecord;
use fractalCms\importExport\pipeline\imports\inserters\Sql;
use fractalCms\importExport\pipeline\interfaces\ImportInserter as InterfacesImportInserter;
use fractalCms\importExport\models\ImportConfig;
use Exception;
use Yii;

class ImportInserter 
{

    /**
     * create
     *
     * @param  string $source
     *
     * @return ImportInserterInterface
     */
    public function create(string $source) : InterfacesImportInserter
    {
        try {
            switch($source) {
                case ImportConfig::SOURCE_TYPE_SQL: 
                    return new Sql();
                    break;
                default: 
                    return new ActiveRecord();
                    break;
            }
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}