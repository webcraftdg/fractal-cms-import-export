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
<div class="row mt-3 align-items-center">
    <div class="col-sm-8">
        <h2>Création d'une configuration d'imports/exports</h2>
    </div>
</div>
<div class="row m-3">
    <?php
    echo $this->render('_form', [
        'model' => $model,
        'tables' => $tables,
        'rowTransformers' => $rowTransformers
    ]);
    ?>
</div>
