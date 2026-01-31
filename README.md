# Labels – Générateur d’étiquettes (Symfony)

Ce projet est une application Symfony qui génère, affiche et prépare à l’impression des étiquettes (produits, bocaux, yaourts, glaces, boîtes aux lettres, etc.). Les données métiers sont stockées en YAML et rendues via des templates Twig, avec prise en charge de variantes (versions) et de codes‑barres EAN‑13.

## Ce que fait le programme

- Charge des **définitions d’étiquettes** depuis `data/label*.yaml`.
- Charge des **définitions de pages** (formats A4/planche d’étiquettes) depuis `data/page.yaml`.
- Rend les étiquettes via des **templates Twig** (`templates/label/*`).
- Génère des **codes‑barres EAN‑13 en SVG** via un filtre Twig, et les met en cache dans `assets/barcodes/`.
- Propose des routes pour **prévisualiser une étiquette**, **imprimer une planche**, ou **imprimer une étiquette répétée**.

## Fonctionnement (logique métier)

### Étiquettes (Label)
- Une étiquette a un `id` (simple ou composé, ex. `icecream_fraise`).
- Les données d’une étiquette sont un tableau YAML (titre, sous‑titre, ingrédients, quantité, images, etc.).
- **Héritage par parent** : si `parent` est défini, les données du parent sont fusionnées avec celles de l’enfant.
- **Versions** : une étiquette peut définir `versions` pour surcharger des champs (ex. taille “small”, “middle”).
  - Les versions peuvent être définies inline ou pointées via des alias `v#...` stockés dans `data/label.yaml`.

### Pages (Page)
Deux usages sont supportés :
- **Planche fixe** : un format définit un nombre de colonnes/lignes (`x`, `y`) dans `data/page.yaml`.
- **Planche composée** : une page peut lister plusieurs étiquettes et leur quantité à imprimer.

### Templates
Les fichiers `templates/label/*.html.twig` définissent les rendus (ex. `three-parts`, `four-parts-in-a-round`, `boite-aux-lettres`).
Les composants réutilisables (ingrédients, quantité, QR‑code, certifications, etc.) sont dans `templates/label/element/`.

### Codes‑barres
Le filtre Twig `ean13_barcode` génère un SVG EAN‑13 et l’écrit dans `assets/barcodes/`.
Les options (`showDigits`, `widthFactor`, `height`) sont configurables par étiquette via `barcodeOptions`.

## Routes principales

- `GET /label/{id}`
  Prévisualisation d’une étiquette, avec navigation et outils.

- `GET /page/{id}`
  Rend une page composée de plusieurs étiquettes selon `data/page.yaml`.

- `GET /page/{page_id}/{label_id}`
  Remplit une planche d’un seul type d’étiquette (répétée).

### Variantes d’étiquettes
Ajoutez `?v=version1,version2` pour charger une ou plusieurs versions :
`/label/icecream_fraise?v=small`

## Structure du projet (repères utiles)

- `data/` : données YAML des pages et étiquettes.
- `src/Entity/` : modèles `Label` et `Page`.
- `src/Repository/` : lecture des fichiers YAML.
- `src/Factory/` : construction/merge des objets.
- `templates/` : rendus Twig.
- `assets/` : styles, images, et barcodes générés.
- `public/index.php` : point d’entrée de l’application.

## Ajouter une étiquette ou un format

1) **Créer/éditer un YAML**
   - Étiquette : `data/label.yaml` ou un `data/label-*.yaml`
   - Page : `data/page.yaml`

2) **Ajouter un template** si nécessaire dans `templates/label/`.

3) **Réutiliser un parent** pour mutualiser la structure et les champs communs.

4) **Définir des versions** pour les variantes d’une même étiquette.

5) **Tester** via les routes `/label/{id}` et `/page/{id}`.
