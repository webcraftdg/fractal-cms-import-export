<?php
/**
 * m251219_101020_alterColumn.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\migrations
 */

namespace fractalCms\importExport\migrations;

use yii\db\Migration;

class m251219_101020_alterColumn extends Migration
{

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->renameColumn('{{%importConfigs}}', 'exportFormat', 'fileFormat');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->renameColumn('{{%importConfigs}}', 'fileFormat', 'exportFormat');
    }
}
