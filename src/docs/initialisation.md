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
        ],
        'fractal-cms-export' => [
            'class' =>  \fractalCms\importExport\Module::class,
            'pathsNamespacesModels' => [
                '@app/models' => 'app\\models\\', /*path des models active record de votre application*/
            ],
            /*Ajout de transformer de ligne (RowTransformer)*/
            'rowTransformers' => [
            /* Pour les configurations import*/
                'import' => [
                    'nom' => [
                        'class' => Votre classe de transformation métier qui implémente l'interface RowTransformer,
                        'label' => 'Nom ',
                    ],
                ],
            /* Pour les configurations export*/
                'export' => [
                    'nom-1' => [
                        'class' => Votre classe de transformation métier  qui implémente l'interface RowTransformer,
                        'label' => 'Nom 1 (export)',
                    ],
                    'nom-2' => [
                        'class' => Votre classe de transformation métier  qui implémente l'interface RowTransformer,
                        'label' => 'Nom 2 (export)'
                    ]
                ],

            ],
        ],
    ],
````

[<- Précédent](introduction.md) | [Suivant ->](configuration.md)