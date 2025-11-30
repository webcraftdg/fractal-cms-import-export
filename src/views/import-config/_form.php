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
 */

use fractalCms\importExport\models\ImportConfig;
use fractalCms\core\helpers\Html;
?>
<div class="row">
    <div class="col-sm-12">
        <?php echo Html::beginForm('', 'post', []); ?>
        <div class="row  justify-content-center">
            <div class="col form-check p-0">
                <?php
                echo Html::activeCheckbox($model, 'active', ['label' =>  null, 'class' => 'form-check-input']);
                echo Html::activeLabel($model, 'active', ['label' => 'Actif', 'class' => 'form-check-label']);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'name', ['label' => 'Nom', 'class' => 'form-label']);
                    echo Html::activeTextInput($model, 'name', ['placeholder' => 'Nom', 'class' => 'form-control']);
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'version', ['label' => 'Version', 'class' => 'form-label']);
                    echo Html::activeTextInput($model, 'version', ['placeholder' => 'Version', 'class' => 'form-control']);
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'table', ['label' => 'Table cible', 'class' => 'form-label']);
                    echo Html::activeDropDownList($model, 'table', $tables, [
                        'prompt' => 'Sélectionner une table', 'class' => 'form-control',
                    ]);
                    ?>
                </div>
            </div>
        </div>
        <?php if ($model->isNewRecord === false): ?>
        <div class="row mt-3">
            <div class="card">
                <div class="card-header">
                    Gestion des columns
                </div>
                <div class="card-body">
                    <?php
                    echo Html::tag('fractal-cms-import-columns', '' ,
                        [
                            'id.bind' => $model->id,
                        ]
                    );
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="row  justify-content-center mt-3">
            <div  class="col-sm-6 text-center form-group">
                <button type="submit" class="btn btn-primary">Enregister</button>
            </div>
        </div>
        <?php  echo Html::endForm(); ?>
    </div>
</div>
