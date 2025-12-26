# Transformers

Les Transformers sont le cœur de la personnalisation du module.

Ils permettent d’adapter le comportement sans toucher
au pipeline global.

---

## ColumnTransformer

### Rôle

- Transformer la valeur de la colonne avant import ou export

![Formulaire de création transformation](images/form-creer-list-transformer-colonne.png)

Les transformers de colonne sont appliqués avant le RowTransformer.

## RowImportTransformer

Les RowTransformer doivent réspecter une Interface RowImportTransformer 

```php

final class AgentRowTransformer implements RowImportTransformerInterface
{


    /**
     * @return string
     */
    public function getName(): string
    {
        return 'nom-1';
    }

    /**
     * @param array $row
     * @param ImportContext $context
     * @return RowTransformerResult
     */
    public function transformRow(array $row, ImportContext $context): RowTransformerResult
    {
        try {
            /**
            * Ici le code de traitement de la ligne avant l'import
            * 
            **/
            return new RowTransformerResult($row, true);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
```



### Rôle

- Transformer une ligne issue d’un Provider
- Appliquer la logique métier minimale nécessaire à l’import

### Exemples d’usage

- Renommer des champs
- Convertir des types
- Valider des valeurs
- Ignorer certaines lignes

### Bonne pratique

Un `RowImportTransformer` doit :
- rester simple
- ne pas accéder à des dépendances lourdes
- ne pas gérer de persistance directe

---

## RowExportTransformer

Les RowTransformer doivent réspecter une Interface RowExportTransformer

```php
final class AgentExportRowTransformer implements RowExportTransformerInterface
{


    /**
     * @return string
     */
    public function getName(): string
    {
        return 'nom-2';
    }

    /**
     * @param array $row
     * @param ExportContext $context
     * @return RowTransformerResult
     */
    public function transformRow(array $row, ExportContext $context): RowTransformerResult
    {
        try {

            /**
            * Icic le code de traitement de la ligne avant de l'écrire dans le fichier
            *
            **/
            $context->writeRow(sheet: 'structure',row: $row,startRow: $context->rowNumber);
            return new RowTransformerResult($row, true);
        } catch (Exception $e)  {
            Yii::error($e->getMessage(), __METHOD__);
            throw  $e;
        }
    }
}
```

### Rôle

- Adapter une ligne issue de l’application
- Préparer les données pour la sortie

### Exemples d’usage

- Formatage de dates
- Ajout de colonnes calculées
- Normalisation de données
- Conversion de structures

### Bonne pratique

Un `RowExportTransformer` ne doit pas :
- modifier l’état de l’application
- effectuer de logique métier complexe

[<- Précédent](configuration.md) | [Suivant ->](import.md)