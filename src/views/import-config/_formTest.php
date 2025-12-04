<?php
/**
 * manage.php
 *
 * PHP Version 8.2+
 *
 * @version XXX
 * @package webapp\views\dafault
 *
 * @var $this yii\web\View
 * @var ImportConfig $model
 * @var array $importConfigs
 * @var ImportJob $importJob
 */

use fractalCms\importExport\models\ImportConfig;
use fractalCms\core\helpers\Html;
use fractalCms\importExport\assets\StaticAsset;
use fractalCms\importExport\models\ImportJob;
$baseUrl = StaticAsset::register($this)->baseUrl;

?>
<div class="row">
    <div class="col-sm-12">
        <?php echo Html::beginForm('', 'post', ['enctype' => 'multipart/form-data']); ?>
        <div class="row">
            <div class="col-sm-6">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'type', ['label' => 'Type du test', 'class' => 'form-label']);
                    echo Html::activeDropDownList($model, 'type', ImportConfig::optsTypes(), [
                        'prompt' => 'Sélectionner un type', 'class' => 'form-control',
                    ]);
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'name', ['label' => 'Format du fichier d\'export', 'class' => 'form-label']);
                    echo Html::activeDropDownList($model, 'name', $importConfigs, [
                        'prompt' => 'Sélectionner une config', 'class' => 'form-control',
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 mt-3">
        <div class="row justify-content-between">
            <div class="col flex items-center gap-1 align-self-start">
                <?php
                echo Html::activeFileInput($model, 'importFile',
                    [
                        'placeholder' => 'Import',
                        'accept' => '.xls, .xlsx, .csv',
                        'class' => 'rounded-l-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500']);
                if ($model->hasErrors('importFile') === true) {
                    echo Html::tag('p', $model->getFirstError('importFile'), ['class' => 'text-red-600 text-sm m-0']);
                }
                ?>

            </div>
        </div>
        <div class="row  justify-content-center mt-3">
            <div  class="col-sm-6 text-center form-group">
                <button type="submit" class="btn btn-primary">Lancer le test</button>
            </div>
        </div>
        <?php  echo Html::endForm(); ?>
    </div>
</div>
