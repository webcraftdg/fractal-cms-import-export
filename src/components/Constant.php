<?php
/**
 * Constant.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\content\components
 */

namespace fractalCms\importExport\components;

use Yii;
use Exception;

class Constant
{

    const PERMISSION_MAIN_UPLOAD = 'UPLOAD:';
    const PERMISSION_MAIN_EXPORT = 'IMPORT:EXPORT:';

    const TRACE_DEBUG = 'debug';

    /**
     * Get db tables
     *
     * @return string[]
     * @throws \yii\base\NotSupportedException
     */
    public static function getDbTable()
    {
        try {
            $tables = [];
            foreach (Yii::$app->db->getSchema()->tableNames as $table) {
                $tables[$table] = ucfirst($table);
            }
            return $tables;
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
