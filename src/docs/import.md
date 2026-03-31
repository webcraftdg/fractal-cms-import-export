# Import

Un import consiste à lire une source de données et à injecter les lignes
dans l’application.

## Étapes

1. Définir une source (Reader)
2. Associer un `RowImportProcessor` si besoin
3. Lancer le traitement ligne par ligne

## Format des fichiers accepté

- Export CSV
- Export Xlsx
- Export JSON
- Export NDJson
- Export XML

### La structure des fichier JSON, NDJSON, XML 

L'import reprend la structure générée en export sur ces mêmes formats

## Quand utiliser un RowImportProcessor ?

- Mapping de colonnes
- Nettoyage de données
- Validation
- Rejet conditionnel de lignes
- Conversion de formats
- Créer une logique métier complexe

Le processor permet de **garder le Provider générique**.

[<- Précédent](transformer.md) | [Suivant ->](export.md)
