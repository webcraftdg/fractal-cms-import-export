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
<?php echo Html::beginForm('', 'post', []); ?>
<div class="fc-row">
    <div class="flex">
        <div class="flex items-center gap-2">
            <?php
            echo Html::activeCheckbox($model, 'active', ['label' =>  null, 'class' => 'form-check-input']);
            echo Html::activeLabel($model, 'active', ['label' => 'Actif', 'class' => 'form-check-label']);
            ?>
        </div>
    </div>
    <div class="flex mb-4">
        <div class="flex items-center gap-2">
            <?php
            echo Html::activeCheckbox($model, 'stopOnError', ['label' =>  null, 'class' => 'form-check-input']);
            echo Html::activeLabel($model, 'stopOnError', ['label' => 'Arrêter l\'import si une ligne est en erreur', 'class' => 'form-check-label']);
            ?>
        </div>
    </div>
</div>
<div class="fc-row-inline">
    <div class="fc-form-group  sm:w-1/2">
            <?php
            echo Html::activeLabel($model, 'name', ['label' => 'Nom', 'class' => 'fc-form-label']);
            echo Html::activeTextInput($model, 'name', ['placeholder' => 'Nom', 'class' => 'fc-form-input']);
            if ($model->hasErrors('name') === true) {
                echo Html::tag('div', $model->errors['name'][0], ['class' => 'text-danger']);
            }
            ?>
    </div>
    <div class="fc-form-group  sm:w-1/2">
            <?php
            echo Html::activeLabel($model, 'version', ['label' => 'Version', 'class' => 'fc-form-label']);
            echo Html::activeTextInput($model, 'version', ['placeholder' => 'Version', 'class' => 'fc-form-input']);
            ?>
    </div>
</div>
<div class="fc-row-inline">
    <div class="fc-form-group   sm:w-1/2">
            <?php
            echo Html::activeLabel($model, 'sourceType', ['label' => 'Type de la source', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'sourceType', ImportConfig::optsSourceTypes(), [
                'prompt' => 'Sélectionner un type de source', 'class' => 'fc-form-input',
            ]);
            ?>
    </div>
    <div class="fc-form-group   sm:w-1/2">
            <?php
            echo Html::activeLabel($model, 'fileFormat', ['label' => 'Format du fichier', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'fileFormat', ImportConfig::optsFormats(), [
                'prompt' => 'Sélectionner un format', 'class' => 'fc-form-input',
            ]);
            ?>
    </div>
</div>
<div class="fc-row-inline">
    <div class=" sm:w-1/2">
        <div class="fc-form-group ">
            <?php
            echo Html::activeLabel($model, 'table', ['label' => 'Table cible', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'table', $tables, [
                'prompt' => 'Sélectionner une table', 'class' => 'fc-form-input',
            ]);
            ?>
        </div>
        <div class="fc-form-group">
            <?php
            echo Html::activeLabel($model, 'type', ['label' => 'Type (import ou export)', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'type', ImportConfig::optsTypes(), [
                'prompt' => 'Sélectionner un type', 'class' => 'fc-form-input',
            ]);
            ?>
        </div>
        <div class="fc-form-group">
            <?php
            echo Html::activeLabel($model, 'exportTarget', ['label' => 'Cible de l\'export (pour une requête SQL)', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'exportTarget', ImportConfig::optsTargets(), [
                'prompt' => 'Sélectionner une cible (pour une requête SQL)', 'class' => 'fc-form-input',
            ]);
            ?>
        </div>
    </div>
    <div class=" sm:w-1/2">
        <div class="fc-form-group">
            <?php
            echo Html::activeLabel($model, 'sql', ['label' => 'Requête SQL', 'class' => 'fc-form-label']);
            echo Html::activeTextarea($model, 'sql', [
                'class' => 'fc-form-input',
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
    <?php if (empty($rowTransformers) === false):?>
        <div class="fc-row">
            <div class="fc-form-group">
                <?php
                echo Html::activeLabel($model, 'rowTransformer', ['label' => 'Transformeur de données de la ligne', 'class' => 'fc-form-label']);
                echo Html::activeDropDownList($model, 'rowTransformer', $rowTransformers, [
                    'prompt' => 'Sélectionner un transformer', 'class' => 'fc-form-input',
                ]);
                ?>
            </div>
        </div>
    <?php endif;?>
<div class="fc-row mt-3">
    <div class="border rounded-md">
        <div class="px-3 py-2 border-b">
            <h3>Gestion des columns</h3>
        </div>
        <div class="p-3 space-y-2">
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
<div class="fc-form-button-container">
    <button type="submit" class="fc-form-button">Valider</button>
</div>
<?php  echo Html::endForm(); ?>
