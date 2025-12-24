# Introduction

Le module Import / Export a pour objectif de fournir une base générique pour gérer
des flux de données entrants et sortants dans vos applications.

Il ne cherche pas à imposer :
- un format de fichier
- une structure de données
- une logique métier

Il fournit uniquement **un pipeline stable**, extensible par configuration et par code.

## Philosophie

- Lire des données
- Les transformer si nécessaire
- Les écrire ailleurs
- Sans charger l’ensemble en mémoire

Chaque ligne est traitée indépendamment, ce qui rend le module adapté
aux imports / exports volumineux.

[Suivant ->](installation.md)