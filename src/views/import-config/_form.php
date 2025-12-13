<?php
/**
 * _form.php
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
use fractalCms\core\helpers\Html;
?>
<div class="row">
    <div class="col-sm-12">
        <?php echo Html::beginForm('', 'post', []); ?>
        <div class="row  justify-content-center">
            <div class="row">
                <div class="col form-check p-0">
                    <?php
                    echo Html::activeCheckbox($model, 'active', ['label' =>  null, 'class' => 'form-check-input']);
                    echo Html::activeLabel($model, 'active', ['label' => 'Actif', 'class' => 'form-check-label']);
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col form-check p-0">
                    <?php
                    echo Html::activeCheckbox($model, 'stopOnError', ['label' =>  null, 'class' => 'form-check-input']);
                    echo Html::activeLabel($model, 'stopOnError', ['label' => 'Arrêter l\'import si une ligne est en erreur', 'class' => 'form-check-label']);
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'name', ['label' => 'Nom', 'class' => 'form-label']);
                    echo Html::activeTextInput($model, 'name', ['placeholder' => 'Nom', 'class' => 'form-control']);
                    if ($model->hasErrors('name') === true) {
                        echo Html::tag('div', $model->errors['name'][0], ['class' => 'text-danger']);
                    }
                    ?>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'version', ['label' => 'Version', 'class' => 'form-label']);
                    echo Html::activeTextInput($model, 'version', ['placeholder' => 'Version', 'class' => 'form-control']);
                    ?>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'exportFormat', ['label' => 'Format du fichier d\'export', 'class' => 'form-label']);
                    echo Html::activeDropDownList($model, 'exportFormat', ImportConfig::optsFormats(), [
                        'prompt' => 'Sélectionner un format', 'class' => 'form-control',
                    ]);
                    ?>
                </div>
            </div>
            <?php if (empty($rowTransformers) === false):?>
            <div class="col-sm-3">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'rowTransformer', ['label' => 'Transformer de ligne', 'class' => 'form-label']);
                    echo Html::activeDropDownList($model, 'rowTransformer', $rowTransformers, [
                        'prompt' => 'Sélectionner un transformer', 'class' => 'form-control',
                    ]);
                    ?>
                </div>
            </div>
            <?php endif;?>
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
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'type', ['label' => 'Type (import ou export)', 'class' => 'form-label']);
                    echo Html::activeDropDownList($model, 'type', ImportConfig::optsTypes(), [
                        'prompt' => 'Sélectionner un type', 'class' => 'form-control',
                    ]);
                    ?>
                </div>
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'exportTarget', ['label' => 'Cible de l\'export', 'class' => 'form-label']);
                    echo Html::activeDropDownList($model, 'exportTarget', ImportConfig::optsTargets(), [
                        'prompt' => 'Sélectionner une cible (pour la requête SQL)', 'class' => 'form-control',
                    ]);
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col form-group p-0">
                    <?php
                    echo Html::activeLabel($model, 'sql', ['label' => 'Requête SQL', 'class' => 'form-label']);
                    echo Html::activeTextarea($model, 'sql', [
                        'class' => 'form-control',
                        'cols' => 10,
                        'rows' => 7,
                    ]);
                    if ($model->hasErrors('sql') === true) {
                        echo Html::tag('div', $model->errors['sql'][0], ['class' => 'text-danger']);
                    }
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
