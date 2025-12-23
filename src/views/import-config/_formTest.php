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
<?php echo Html::beginForm('', 'post', ['enctype' => 'multipart/form-data']); ?>
<div class="fc-row">
    <div class="fc-form-group">
        <?php
        echo Html::activeLabel($model, 'importFile', ['label' => 'Importer un fichier (uniquement config \'import\')', 'class' => 'fc-form-label']);
        ?>
        <?php
        echo Html::activeFileInput($model, 'importFile',
            [
                'placeholder' => 'Import',
                'accept' => '.xls, .xlsx, .csv',
                'class' => 'fc-form-input']);
        if ($model->hasErrors('importFile') === true) {
            echo Html::tag('p', $model->getFirstError('importFile'), ['class' => 'text-red-600 text-sm m-0']);
        }
        ?>
    </div>
    <div class="fc-form-group">
        <?php
        echo Html::activeLabel($model, 'importConfigId', ['label' => 'Configuration', 'class' => 'fc-form-label']);
        echo Html::activeDropDownList($model, 'importConfigId', $importConfigs, [
            'prompt' => 'Sélectionner une config', 'class' => 'fc-form-input',
        ]);
        if ($model->hasErrors('importConfigId') === true) {
            echo Html::tag('p', $model->getFirstError('importConfigId'), ['class' => 'fc-error']);
        }
        ?>
    </div>
</div>
<div class="fc-row">
    <div class="fc-form-button-container">
        <button type="submit" class="fc-form-button">Lancer le test</button>
    </div>
</div>
<?php  echo Html::endForm(); ?>
<?php
if ($importJob !== null && in_array($importJob->status, [ImportJob::STATUS_SUCCESS, ImportJob::STATUS_FAILED]) === true) {
    echo $this->render('_importJobLog', ['importJob' => $importJob,]);
}
?>
