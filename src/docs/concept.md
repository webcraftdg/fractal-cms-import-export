# Concepts clés

## Provider

Un Provider est responsable de :
- la lecture (Import)
- ou l’écriture (Export)

Il ne transforme pas les données.
Il se contente de fournir ou consommer des lignes.

- Array Provider
- Query Provider
- SQL Provider

---

## RowImportTransformer

Le `RowImportTransformer` intervient **pendant un import**.

Rôle :
- recevoir une ligne brute
- la transformer, valider ou normaliser
- retourner une ligne exploitable par l’application

C’est le **point d’extension principal côté import**.

---

## RowExportTransformer

Le `RowExportTransformer` intervient **pendant un export**.

Rôle :
- recevoir une ligne issue de l’application
- l’adapter au format de sortie
- enrichir ou reformater les données

C’est le **point d’extension principal côté export**.

---

## Pipeline simplifié

Import :
Source → Provider → RowImportTransformer → Application

Export :
Application → RowExportTransformer → Provider → Destination

[<- Précédent](initialisation.md) | [Suivant ->](configuration.md)

