# Initialisation

## Init Import-export

### Mise à jour des rôles et permissions

``
php yii.php fractalCms:rbac/index
``

## Config application

### Ajouter Import-Export et paramétrage des RowTransformers

````php 
    'bootstrap' => [
        'fractal-cms',
        'fractal-cms-export',
    ],
    'modules' => [
        'fractal-cms' => [
            'class' =>  \fractalCms\core\Module::class,
            'identityClass' => \app\models\Agent::class // Ce paramètre permet de valoriser un model  utilisateur déjà présent dans l'application
        ],
        'fractal-cms-export' => [
            'class' =>  \fractalCms\importExport\Module::class,
            'pathsNamespacesModels' => [
                '@app/models' => 'app\\models\\', // path des models active record de votre application
            ],
            // Ajout de convertisseur métier (RowProcessor)
            'rowProcessors' => [
            // Pour les configurations import
                'import' => [
                    'nom' => [
                        'class' => 'Votre classe de conversion métier qui implémente l\'interface RowImportProcessor',
                        'label' => 'Nom',
                    ],
                ],
            // Pour les configurations export
                'export' => [
                    'nom-1' => [
                        'class' => 'Votre classe de transformation métier  qui implémente l\'interface RowExportProcessor',
                        'label' => 'Nom 1 (export)',
                    ],
                    'nom-2' => [
                        'class' => 'Votre classe de transformation métier  qui implémente l\'interface RowExportProcessor',
                        'label' => 'Nom 2 (export)'
                    ]
                ],

            ],
        ],
    ],
````

[<- Précédent](introduction.md) | [Suivant ->](configuration.md)