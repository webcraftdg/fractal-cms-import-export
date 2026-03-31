# Concepts clés

## Data Reader

Un Dada reader est responsable de :
- la lecture (Import)
- ou l’écriture (Export)

Il ne transforme pas les données.
Il se contente de fournir ou consommer des lignes.

- Array Data reader
- Query Data reader
- SQL data reader

---

## RowImportProcessor

Le `RowImportProcessor` intervient **pendant un import**.

Rôle :
- recevoir une ligne brute
- la modifier, l'adapter , valider ou normaliser
- retourner une ligne exploitable par l’application

C’est le **point d’extension principal côté import**.

---

## RowExportProcessor

Le `RowExportProcessor` intervient **pendant un export**.

Rôle :
- recevoir une ligne issue de l’application
- l’adapter au format de sortie
- enrichir ou reformater les données

C’est le **point d’extension principal côté export**.

---

## Pipeline simplifié

### Export

Model de configuration
  → préparation de la source
  → DataReader
  → mapping des colonnes
  → transformateurs
  → RowProcessor métier (optionnel)
  → Writer
  → fichier exporté

Le moteur d’export lit les données depuis une table, une requête SQL, une vue ou une source externe, applique le mapping et les traitements configurés, puis génère un fichier dans le format demandé.

### Import

Model de configuration
  → Reader du fichier
  → lecture des enregistrements
  → mapping des colonnes
  → transformateurs
  → RowProcessor métier (optionnel)
  → Inserter
  → table cible

Le moteur d’import lit un fichier structuré, applique le mapping et les traitements configurés, puis insère les données transformées dans la table cible.

[<- Précédent](initialisation.md) | [Suivant ->](configuration.md)

