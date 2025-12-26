# FractalCMS – Import / Export Module

Le module Import / Export de FractalCMS fournit une architecture générique et extensible
pour importer et exporter des données, quel que soit leur format ou leur origine
(fichier, base de données, SQL).

Il repose sur une approche **row-based**, pensée pour :
- la performance
- la faible consommation mémoire
- l’extensibilité via des points d’entrée clairs

Ce module s’adresse principalement aux **développeurs et intégrateurs** souhaitant
brancher des flux de données sans modifier le cœur du système.

## Principes clés

- Traitement ligne par ligne (streaming)
- Séparation stricte des responsabilités
- Transformations personnalisables via Transformers
- Compatible avec de gros volumes de données

## Documentation

- [Introduction](./src/docs/introduction.md)
- [Concepts clés](./src/docs/concept.md)
- [Import](./src/docs/import.md)
- [Export](./src/docs/export.md)
- [Transformers](./src/docs/transformer.md)
- [Commande](./src/docs/command.md)
- [Qualité & limites](./src/docs/quality.md)
