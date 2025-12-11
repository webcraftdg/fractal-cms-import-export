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
                'name'=> $this->string(150)->defaultValue(null),
                'version' => $this->integer(),
                'active'=> $this->boolean()->defaultValue(false),
                'exportFormat'=> $this->string(10)->defaultValue(null),
                'truncateTable' => $this->boolean()->defaultValue(false),
                'table'=> $this->string()->defaultValue(null),
                'sql' => $this->binary()->defaultValue(null),
                'exportTarget' => 'ENUM(\'sql\', \'view\') DEFAULT NULL',
                'dateCreate'=> $this->datetime()->defaultValue(null),
                'dateUpdate'=> $this->datetime()->defaultValue(null),
            ]
        );

        $this->createIndex(
            'importConfigs_name_version_idx',
            '{{%importConfigs}}',
            ['name', 'version'],
            true
        );

        $this->createTable(
            '{{%importConfigColumns}}',
            [
                'id'=> $this->bigPrimaryKey(20),
                'importConfigId'=> $this->bigInteger(),
                'source'=> $this->string()->defaultValue(null),
                'target'=> $this->string()->defaultValue(null),
                'type'=> $this->string(50)->defaultValue(null),
                'defaultValue'=> $this->string()->defaultValue(null),
                'transformer'=> $this->binary()->defaultValue(null),
                'transformerOptions'=> $this->binary()->defaultValue(null),
                'order' => $this->float(),
                'dateCreate'=> $this->datetime()->defaultValue(null),
                'dateUpdate'=> $this->datetime()->defaultValue(null),
            ]
        );

        $this->addForeignKey('importConfigColumns_importConfigs_fk',
            '{{%importConfigColumns}}',
            'importConfigId',
            '{{%importConfigs}}',
            'id',
            'NO ACTION',
            'NO ACTION'
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
                'dateCreate'=> $this->datetime()->defaultValue(null),
                'dateUpdate'=> $this->datetime()->defaultValue(null),
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
        $this->dropForeignKey('importConfigColumns_importConfigs_fk', '{{%importConfigColumns}}');
        $this->dropTable('{{%importConfigColumns}}');
        $this->dropIndex('importConfigs_name_version_idx','{{%importConfigs}}');
        $this->dropTable('{{%importConfigs}}');
        return true;
    }

}
