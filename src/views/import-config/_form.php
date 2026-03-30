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
 * @var array $rowProcessors
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
            echo Html::activeLabel($model, 'active', ['label' => 'Configuration active', 'class' => 'form-check-label']);
            ?>
        </div>
    </div>
    <div class="flex mb-4">
        <div class="flex items-center gap-2">
            <?php
            echo Html::activeCheckbox($model, 'stopOnError', ['label' =>  null, 'class' => 'form-check-input']);
            echo Html::activeLabel($model, 'stopOnError', ['label' => 'Arrêter le traitement à la première erreur', 'class' => 'form-check-label']);
            ?>
        </div>
    </div>
</div>
<div class="fc-row-inline">
    <div class="fc-form-group  sm:w-1/3">
            <?php
            echo Html::activeLabel($model, 'name', ['label' => 'Nom de la configuration', 'class' => 'fc-form-label']);
            echo Html::activeTextInput($model, 'name', ['placeholder' => 'Nom', 'class' => 'fc-form-input']);
            if ($model->hasErrors('name') === true) {
                echo Html::tag('div', $model->errors['name'][0], ['class' => 'error-message']);
            }
            ?>
    </div>
    <div class="fc-form-group  sm:w-1/3">
            <?php
            echo Html::activeLabel($model, 'version', ['label' => 'Version', 'class' => 'fc-form-label']);
            echo Html::activeTextInput($model, 'version', ['placeholder' => 'Version', 'class' => 'fc-form-input']);
            ?>
    </div>
    <div class="fc-form-group  sm:w-1/3">
            <?php
            echo Html::activeLabel($model, 'type', ['label' => 'Type (import ou export)', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'type', ImportConfig::optsTypes(), [
                'prompt' => 'Sélectionner un type', 'class' => 'fc-form-input',
            ]);
            ?>
        </div>
</div>
<div class="fc-row-inline">
    <div class="fc-form-group   sm:w-1/2">
            <?php
            echo Html::activeLabel($model, 'sourceType', ['label' => 'Source des données', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'sourceType', ImportConfig::optsSourceTypes(), [
                'prompt' => 'Sélectionner une source de données', 'class' => 'fc-form-input',
            ]);
               if ($model->hasErrors('sourceType') === true) {
                echo Html::tag('div', $model->errors['sourceType'][0], ['class' => 'error-message']);
            }
            ?>
            <div class="text-xs italic text-stone-700">
                <strong>Définit comment les données sont récupérées pour un export :</strong>
                <ul>
                    <li><strong>Table</strong> : via ActiveQuery sur une table</li>
                    <li><strong>SQL</strong> : via une requête SQL personnalisée</li>
                    <li><strong>Externe</strong> : via un tableau de données fourni par le code ou un fichier (import)</li>
                </ul>
            </div>
    </div>
    <div class="fc-form-group   sm:w-1/2">
            <?php
            echo Html::activeLabel($model, 'fileFormat', ['label' => 'Format du fichier', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'fileFormat', ImportConfig::optsFormats(), [
                'prompt' => 'Sélectionner un format', 'class' => 'fc-form-input',
            ]);
             echo Html::tag('div', 'Format du fichier lu en import ou généré en export.', 
            ['class' => 'text-stone-700 text-xs italic']);
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
            if ($model->hasErrors('table') === true) {
                echo Html::tag('div', $model->errors['table'][0], ['class' => 'error-message']);
            }
            ?>
            <div class="text-xs italic">
                <ul class="text-stone-700">
                    <li><strong>Import</strong> : table dans laquelle les données seront enregistrées.</li>
                    <li><strong>Export</strong> : table utilisée comme base de correspondance ou de contrôle des colonnes.</li>
                </ul>
            </div>
        </div>
        <div class="fc-form-group">
            <?php
            echo Html::activeLabel($model, 'exportTarget', ['label' => 'Mode de calcul des données à exporter', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'exportTarget', ImportConfig::optsTargets(), [
                'prompt' => 'Sélectionner un mode de calcul de données', 'class' => 'fc-form-input',
            ]);
                if ($model->hasErrors('exportTarget') === true) {
                echo Html::tag('div', $model->errors['exportTarget'][0], ['class' => 'error-message']);
            }
            ?>
            <div class="text-xs italic text-stone-700">
                <strong>Choisissez comment récupérer les données de l’export :</strong>
                <ul>
                    <li><strong>SQL :</strong> la requête SQL est exécutée à chaque export.</li>
                    <li><strong>VIEW :</strong> les données proviennent de la vue créée via la requête SQL.</li>
                </ul>
            </div>
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
                echo Html::tag('div', $model->errors['sql'][0], ['class' => 'error-message']);
            }
            ?>
             <div class="text-xs italic text-stone-700">
                <strong>Obligatoire pour un type <strong>export</strong> si la <strong>source des données</strong> est SQL</strong>
                <ul>
                    <li><strong>Export</strong> : utilisée si la source des données exportées est de type SQL.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php if (empty($rowProcessors) === false):?>
    <div class="fc-row">
        <div class="fc-form-group">
            <?php
            echo Html::activeLabel($model, 'rowProcessor', ['label' => 'Convertisseur métier', 'class' => 'fc-form-label']);
            echo Html::activeDropDownList($model, 'rowProcessor', $rowProcessors, [
                'prompt' => 'Sélectionner un convertisseur', 'class' => 'fc-form-input',
            ]);
            if ($model->hasErrors('rowProcessor') === true) {
                echo Html::tag('div', $model->errors['rowProcessor'][0], ['class' => 'error-message']);
            }
            ?>
            <div class="text-xs italic text-stone-700">
            <ul>
                <li>Permet d’appliquer une transformation métier à chaque ligne importée ou exportée.</li>
            </ul>
        </div>
        </div>
    </div>
<?php endif;?>
<?php if ($model->isNewRecord === false): ?>
<div class="fc-row mt-3">
    <div class="border rounded-md">
        <div class="px-3 py-2 border-b">
            <h3>Gestion des colonnes</h3>
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
