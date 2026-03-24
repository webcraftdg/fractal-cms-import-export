<?php
/**
 * ImportInserter.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\services
 */
namespace fractalCms\importExport\services\imports\factories;

use Exception;
use fractalCms\importExport\interfaces\ImportInserter as ImportInserterInterface;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\services\imports\inserters\ActiveRecord;
use fractalCms\importExport\services\imports\inserters\Sql;
use InvalidArgumentException;
use Yii;

class ImportInserter {

    /**
     * create
     *
     * @param  string $source
     *
     * @return InterfacesImportReader
     */
    public function create(string $source) : ImportInserterInterface
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