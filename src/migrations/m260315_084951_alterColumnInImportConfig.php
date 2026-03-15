<?php
/**
 * m260315_084951_alterColumnInImportConfig.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\migrations
 */

namespace fractalCms\importExport\migrations;

use yii\db\Migration;

class m260315_084951_alterColumnInImportConfig extends Migration
{
     /**
     * @inheritDoc
     */
    public function up()
    {
        $this->renameColumn('{{%importConfigs}}', 'rowTransformer', 'rowProcessor');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->renameColumn('{{%importConfigs}}', 'rowProcessor', 'rowTransformer');
    }
}
