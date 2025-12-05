<?php
/**
 * m251121_133115_initDatabase.php
 *
 * PHP Version 8.2+
 *
 * @author David Ghyse <davidg@webcraftdg.fr>
 * @version XXX
 * @package fractalCms\importExport\migrations
 */
namespace fractalCms\importExport\migrations;

use yii\db\Migration;

class m251121_133115_initDatabase extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {

        $this->createTable(
            '{{%importConfigs}}',
            [
                'id'=> $this->bigPrimaryKey(20),
                'name'=> $this->string(150)->null()->defaultValue(null),
                'version' => $this->integer(),
                'active'=> $this->boolean()->defaultValue(false),
                'exportFormat'=> $this->string(10)->null()->defaultValue(null),
                'truncateTable' => $this->boolean()->defaultValue(false),
                'table'=> $this->string()->null()->defaultValue(null),
                'sql' => $this->binary(),
                'jsonConfig' => $this->binary(),
                'dateCreate'=> $this->datetime()->null()->defaultValue(null),
                'dateUpdate'=> $this->datetime()->null()->defaultValue(null),
            ]
        );

        $this->createIndex(
            'importConfigs_name_version_idx',
            '{{%importConfigs}}',
            ['name', 'version'],
            true
        );

        $this->createTable(
            '{{%importJobs}}',
            [
                'id'=> $this->bigPrimaryKey(20),
                'importConfigId' => $this->bigInteger(),
                'userId' => $this->bigInteger()->defaultValue(null),
                'type' => 'ENUM(\'import\', \'export\') NOT NULL',
                'filePath' => $this->string()->defaultValue(null),
                'sql' => $this->text(),
                'totalRows' => $this->integer()->defaultValue(0),
                'successRows' => $this->integer()->defaultValue(0),
                'errorRows' => $this->integer()->defaultValue(0),
                'status' => 'ENUM(\'pending\', \'running\',\'success\',\'failed\') NOT NULL',
                'jsonConfig' => $this->binary(),
                'dateCreate'=> $this->datetime()->null()->defaultValue(null),
                'dateUpdate'=> $this->datetime()->null()->defaultValue(null),
            ]
        );


        $this->addForeignKey('importJobs_importConfigs_fk',
            '{{%importJobs}}',
            'importConfigId',
            '{{%importConfigs}}',
            'id',
            'NO ACTION',
            'NO ACTION'
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {

        $this->dropForeignKey('importJobs_importConfigs_fk','{{%importJobs}}');
        $this->dropTable('{{%importJobs}}');
        $this->dropIndex('importConfigs_name_version_idx','{{%importConfigs}}');
        $this->dropTable('{{%importConfigs}}');
        return true;
    }

}
