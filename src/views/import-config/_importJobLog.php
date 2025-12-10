<?php
/**
 * _importJobLog.php
 *
 * PHP Version 8.2+
 *
 * @version XXX
 * @package webapp\views\dafault
 *
 * @var $this yii\web\View
 * @var ImportJob $importJob
 */

use fractalCms\importExport\models\ImportJob;
use fractalCms\core\helpers\Html;
?>
<div class="row mt-3 align-items-center">
    <div class="col-sm-8">
        <h2>Résultat de l'import</h2>
    </div>
</div>
<div class="row mt-3 border border-primary">
    <div class="col-sm-6 text-danger">
        <?php
        echo 'Lignes en erreurs : '.$importJob->errorRows;
        ?>
    </div>
    <div class="col-sm-6 text-success">
        <?php
        echo 'Lignes réussis : '.$importJob->successRows;
        ?>
    </div>
</div>
<?php
    if ($importJob->status === ImportJob::STATUS_FAILED && empty($importJob->logs) === false) {
        foreach ($importJob->logs as $importJobLog) {
            echo $this->render('_importJobLogLine',
                [
                    'importJobLog' => $importJobLog
                ]);
        }
    } elseif ($importJob->status === ImportJob::STATUS_SUCCESS) {
        echo Html::tag('div', 'Import/export réalisé avec succès', ['class' => 'col-sm-12 text-success']);
    }

?>
