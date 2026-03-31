<?php


namespace Tests\Unit;

use fractalCms\importExport\configuration\factories\ImportConfigColumn;
use fractalCms\importExport\configuration\services\ConfigColumnsGeneratorService;
use fractalCms\importExport\configuration\services\ConfigColumnsPersistenceService;
use fractalCms\importExport\database\services\ConfigDataBaseService;
use fractalCms\importExport\database\services\DbView;
use fractalCms\importExport\database\services\SourceColumnsResolver;
use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
use fractalCms\importExport\pipeline\services\ImportExportExecutionService;
use fractalCms\importExport\services\Export;
use Tests\Support\UnitTester;
use Yii;

class ModelTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    protected function _before()
    {
        Yii::setAlias('@test', dirname(__DIR__, 1).'/');
    }

    // tests
    public function testImportConfig()
    {
        $importConfigColumnFactory = Yii::$container->get(ImportConfigColumn::class);
        $sourceColumnsResolver = Yii::$container->get(SourceColumnsResolver::class);
        /**@var ConfigColumnsPersistenceService $configColumnsPersistentService */
        $configColumnsPersistentService = Yii::$container->get(ConfigColumnsPersistenceService::class);

        $dbView = Yii::$container->get(DbView::class);

        $configColumnGenerator = new ConfigColumnsGeneratorService($sourceColumnsResolver, $importConfigColumnFactory);
        $configDatabase = new ConfigDataBaseService($dbView, $configColumnGenerator);
        /**
         * Tests basés sur Fractal-cms
         */
        $columns = [
            [
                'source' => 'reg',
                'target' => 'reg',
                'format' => 'string'
            ],
            [
                'source' => 'name',
                'target' => 'Name',
                'format' => 'string'
            ],
        ];
        $model = new ImportConfig(['scenario' => ImportConfig::SCENARIO_CREATE]);
        $this->assertFalse($model->validate());
        $model->attributes = [
            'name' => 'import_agents_v1',
            'version'=> 1,
            'table'=> 'app\\models\\Agent',
            'type' => 'export',
            'sourceType' =>'table',
            'active' => 1,
            'stopOnError' => 1,
        ];
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors());
        $this->assertContains('table', array_keys($model->errors));
        $this->assertContains('fileFormat', array_keys($model->errors));
        $model->fileFormat = ImportConfig::FORMAT_CSV;
        $model->table = 'fractalCms\\importExport\\models\\ImportConfig';
        $validation = $model->validate();
        $this->assertTrue($validation);
        $this->assertFalse($model->hasErrors());
        $model->save(false);
        $model->refresh();


        $transaction = Yii::$app->db->beginTransaction();

        $errorsColumns = $configColumnsPersistentService->process($model, $columns);
        if (empty($errorsColumns) === true) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
        $this->assertNotEmpty($errorsColumns);
        $columns = [
            [
                'source' => 'name',
                'target' => 'Name',
                'format' => 'string'
            ],
            [
                'source' => 'version',
                'target' => 'Version',
                'format' => 'string'
            ],
            [
                'source' => 'type',
                'target' => 'Type',
                'format' => 'string',
                'transformer' => [
                    'name' => 'truc'
                ],
            ],
            [
                'source' => 'dateCreate',
                'target' => 'Date de création',
                'format' => 'datetime',
                'transformer' => [
                    'name' => 'date'
                ],
                'transformerOptions' => [
                    'from' => 'Y-m-d H:s:i',
                    'to' => 'd/m/Y'
                ]
            ]
        ];

        $transaction = Yii::$app->db->beginTransaction();
        $errorsColumns = $configColumnsPersistentService->process($model, $columns);
        $this->assertNotEmpty($errorsColumns);
        if (empty($errorsColumns) === true) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }


        $columns = [
            [
                'source' => 'name',
                'target' => 'Name',
                'format' => 'string'
            ],
            [
                'source' => 'version',
                'target' => 'Version',
                'format' => 'string'
            ],
            [
                'source' => 'type',
                'target' => 'Type',
                'format' => 'string',
                'transformer' => [
                    'name' => 'upper'
                ]
            ],
            [
                'source' => 'table',
                'target' => 'Table',
                'format' => 'string',
                'transformer' => [
                    'name' => 'lower'
                ]
            ],
            [
                'source' => 'exportTarget',
                'target' => 'Export Target',
                'format' => 'string',
                'transformer' => [
                    'name' => 'replace'
                ],
                'transformerOptions' => [
                    'search' => 'v',
                    'replace' => 'V'
                ]
            ],
            [
                'source' => 'active',
                'target' => 'Actif',
                'format' => 'integer',
                'transformer' => [
                    'name' => 'boolean'
                ],
                'transformerOptions' => [
                    'true' => 'OK',
                    'false' => 'KO'
                ]
            ],
            [
                'source' => 'dateCreate',
                'target' => 'Date de création',
                'format' => 'datetime',
                'transformer' => [
                    'name' => 'date'
                ],
                'transformerOptions' => [
                    'from' => 'Y-m-d H:s:i',
                    'to' => 'd/m/Y'
                ]
            ]
        ];
        $transaction = Yii::$app->db->beginTransaction();
        $errorsColumns = $configColumnsPersistentService->process($model, $columns);
        $this->assertEmpty($errorsColumns);
        if (empty($errorsColumns) === true) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
        $importConfig = ImportConfig::find()->where(['name' => 'import_agents_v1'])->one();
        $this->assertNotNull($importConfig);
        $count = $importConfig->getImportColumns()->count();
        $this->assertEquals($count, 7);
        $modelId = $model->id;
        $executionService = new ImportExportExecutionService();
        if ($importConfig->isExport() === true) {
            $importJob = $executionService->executeExport($importConfig);
        } 
        $this->assertEquals(ImportJob::STATUS_SUCCESS, $importJob->status);
        $this->assertEquals(1, $importJob->successRows);
        $this->assertEquals(0, $importJob->errorRows);
        $this->assertNotEmpty($importJob->filePath);
        /** EXPORT XLSX*/
        $model = new ImportConfig(['scenario' => ImportConfig::SCENARIO_CREATE]);
        $this->assertFalse($model->validate());
        $model->attributes = [
            'name' => 'import_agents_v1',
            'version'=> 1,
            'table'=> 'fractalCms\\importExport\\models\\ImportJob',
            'type' => 'export',
            'fileFormat' => ImportConfig::FORMAT_EXCEL_X,
            'sourceType' =>'table',
            'active' => 1,
            'stopOnError' => 1,
        ];
        $validation = $model->validate();
        $this->assertFalse($validation);
        $this->assertTrue($model->hasErrors());
        $model->name = 'import_job_v1';
        $validation = $model->validate();
        $this->assertTrue($validation);
        $this->assertFalse($model->hasErrors());
        $model->save(false);
        $model->refresh();


        $columns = [
            [
                'source' => 'id',
                'target' => 'Identifiant',
                'format' => 'string',
                'transformer' => [
                    'name' => 'str-pad'
                ],
                'transformerOptions' => [
                    'length' => 8,
                    'string' => '0',
                    'type' => 0
                ]
            ],
            [
                'source' => 'status',
                'target' => 'Statut',
                'format' => 'string',
                'transformer' => [
                    'name' => 'trim'
                ],
            ],
            [
                'source' => 'successRows',
                'target' => 'Type',
                'format' => 'string',
                'transformer' => [
                    'name' => 'decimals'
                ],
                'transformerOptions' => [
                    'decimals' => 3,
                ]
            ],
        ];
        $transaction = Yii::$app->db->beginTransaction();
        $errorsColumns = $configColumnsPersistentService->process($model, $columns);
        $this->assertEmpty($errorsColumns);
        if (empty($errorsColumns) === true) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
        $importConfig = ImportConfig::find()->where(['name' => 'import_job_v1'])->one();
        $this->assertNotNull($importConfig);
        $count = $importConfig->getImportColumns()->count();
        $this->assertEquals($count, 3);
        $executionService = new ImportExportExecutionService();
        if ($importConfig->isExport() === true) {
            $importJob = $executionService->executeExport($importConfig);
        } 
        $this->assertEquals(ImportJob::STATUS_SUCCESS, $importJob->status);
        $this->assertEquals(1, $importJob->successRows);
        $this->assertEquals(0, $importJob->errorRows);
        $this->assertNotEmpty($importJob->filePath);

        /** Error SQl*/
        $model = new ImportConfig(['scenario' => ImportConfig::SCENARIO_CREATE]);
        $this->assertFalse($model->validate());
        $model->attributes = [
            'name' => 'export_sql_v1',
            'version'=> 1,
            'type' => 'export',
            'fileFormat' => ImportConfig::FORMAT_EXCEL_X,
            'sourceType' =>'sql',
            'active' => 1,
            'stopOnError' => 1,
        ];
        $validation = $model->validate();
        $this->assertFalse($validation);
        $this->assertTrue($model->hasErrors('sql'));
        $model->sql = 'Delete from importConfigColumns where  importConfigId=1 ';
        $validation = $model->validate();
        $this->assertFalse($validation);
        $this->assertTrue($model->hasErrors('sql'));
        $model->sql = 'select *  from importConfigColumns where importConfigId='.$modelId;
        $validation = $model->validate();
        $this->assertFalse($validation);
        $model->exportTarget = ImportConfig::TARGET_SQL;
        $validation = $model->validate();
        $this->assertTrue($validation);
        $transaction = Yii::$app->db->beginTransaction();
        $model->save();
        $model->refresh();
        $hasDbView = Yii::$container->has(DbView::class);
        $this->assertTrue($hasDbView);
        $dbView = Yii::$container->get(DbView::class);
        $configDatabase->generateDbView($model);
        $rawColumns = $configDatabase->generateColumns($model);
        $errorsColumns = $configColumnsPersistentService->process($model, $rawColumns);
        $this->assertEmpty($errorsColumns);
        if (empty($errorsColumns) === true) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }
        $importConfig = ImportConfig::find()->where(['name' => 'export_sql_v1'])->one();
        $this->assertNotNull($importConfig);
        $count = $importConfig->getImportColumns()->count();
        $this->assertEquals($count, 11);
          $executionService = new ImportExportExecutionService();
        if ($importConfig->isExport() === true) {
            $importJob = $executionService->executeExport($importConfig);
        } 
        $this->assertEquals(ImportJob::STATUS_SUCCESS, $importJob->status);
        $this->assertEquals(7, $importJob->successRows);
        $this->assertEquals(0, $importJob->errorRows);
        $this->assertNotEmpty($importJob->filePath);

    }
}
