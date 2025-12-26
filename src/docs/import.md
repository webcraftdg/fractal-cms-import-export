# Import

Un import consiste à lire une source de données et à injecter les lignes
dans l’application.

## Étapes

1. Définir une source (Provider)
2. Associer un `RowImportTransformer`
3. Lancer le traitement ligne par ligne

## Quand utiliser un RowImportTransformer ?

- Mapping de colonnes
- Nettoyage de données
- Validation
- Rejet conditionnel de lignes
- Conversion de formats

Le transformer permet de **garder le Provider générique**.

[<- Précédent](transformer.md) | [Suivant ->](export.md)
