# Installation

## prérequis

### Backend

* Php : >= 8.3
* YiiFramework >= 2.0

### Front

* Nodejs :v24.8.0
* Nmp :11.6.0

## Installation via Composer

### Prérequis

``
composer require webcraftdg\fractal-import-export
``
## Base de données

### Exemple Mariadb (MySql)

``
 create database baseName  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
``

### Init database

```bash
php yii.php migrate
```

### Mise à jour des droits

```bash
php yii.php fractalCms:rbac/index
```

[<- Précédent](introduction.md) | [Suivant ->](concept.md)