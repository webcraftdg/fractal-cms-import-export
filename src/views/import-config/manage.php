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
 * @var array $tables
 * @var array $rowTransformers
 */

use fractalCms\importExport\models\ImportConfig;
?>
<div class="mt-3 flex  justify-center">
    <div class="sm:w-3/5">
        <h2>Création d'une configuration d'imports/exports</h2>
    </div>
</div>
<div class="flex justify-center mt-4 ">
    <div class="sm:w-3/5">
        <?php
        echo $this->render('_form', [
            'model' => $model,
            'tables' => $tables,
            'rowTransformers' => $rowTransformers
        ]);
        ?>
    </div>
</div>
