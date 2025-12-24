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
<?php
if ($importJob->status === ImportJob::STATUS_FAILED && empty($importJob->logs) === false) {
    foreach ($importJob->logs as $importJobLog) {
        echo $this->render('_importJobLogLine',
            [
                'importJobLog' => $importJobLog
            ]);
    }
} elseif ($importJob->status === ImportJob::STATUS_SUCCESS) {
    echo Html::tag('div', 'Import/export réalisé avec succès', ['class' => 'fc-success mt-3']);
}

?>
<div class="border rounded-md mt-3">
    <div class="px-3 py-2 border-b">
        <h2>Résultat de l'import</h2>
    </div>
    <div class="p-3 space-y-2">
        <div class="fc-danger">
            <?php
            echo 'Lignes en erreurs : '.$importJob->errorRows;
            ?>
        </div>
        <div class="fc-success">
            <?php
            echo 'Lignes réussis : '.$importJob->successRows;
            ?>
        </div>
    </div>
</div>
