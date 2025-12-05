<?php
/**
 * test-import.php
 *
 * PHP Version 8.2+
 *
 * @version XXX
 * @package webapp\views\dafault
 *
 * @var $this yii\web\View
 * @var ImportConfig $model
 * @var array $importConfigs
 * @var ImportJob | null $importJob
 */

use fractalCms\importExport\models\ImportConfig;
use fractalCms\importExport\models\ImportJob;
?>
<div class="row mt-3 align-items-center">
    <div class="col-sm-8">
        <h2>Tester les imports/exports</h2>
    </div>
</div>
<div class="row m-3">
    <?php
    echo $this->render('_formTest', [
        'model' => $model,
        'importConfigs' => $importConfigs,
        'importJob' => $importJob,
    ]);
    ?>
</div>
