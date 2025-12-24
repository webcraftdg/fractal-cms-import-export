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
<div class="mt-3 flex  justify-center">
    <div class="sm:w-3/5">
        <h2>Tester les imports/exports</h2>
    </div>
</div>
<div class="flex justify-center mt-4 ">
    <div class="sm:w-3/5">
        <?php
        echo $this->render('_formTest', [
            'model' => $model,
            'importConfigs' => $importConfigs,
            'importJob' => $importJob,
        ]);
        ?>
    </div>
</div>
