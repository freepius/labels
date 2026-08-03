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

## Placeholders dynamiques ([[...]])

Le rendu supporte des placeholders éditables en direct dans les valeurs YAML.

### Syntaxes supportées

- `[[KEY]]`
- `[[KEY : valeur par defaut]]`

Exemples :

- `DDM : [[DDM]]`
- `DDM : [[DDM : 03 / 08 / 2027]]`

Regle importante : la valeur par defaut doit rester du texte simple.
Si un rendu HTML ou SVG particulier est necessaire, le balisage doit entourer
le placeholder au lieu d'etre mis dedans.

Exemples corrects :

- HTML : `DDM : <b>[[DDM]]</b>`
- SVG : `DDM : <tspan dx="75">[[DDM]]</tspan>`

Exemple incorrect :

- `DDM : [[DDM : <tspan dx="75">]]`

### Comportement au rendu

- La valeur par défaut est affichée immédiatement.
- Un panneau **Valeurs dynamiques** est ajouté sur la page avec un input par `KEY`.
- La saisie met à jour toutes les occurrences de la même `KEY` en temps réel.
- Le bouton **Réinitialiser** remet toutes les valeurs à leur défaut.

### Persistance URL

- Les valeurs éditées sont stockées dans la query string pour partager/recharger la vue :

- format de cle : `dyn.KEY`
- exemple : `?dyn.DDM=03%20%2F%2008%20%2F%202027`

- Si une valeur est égale au défaut, son paramètre `dyn.KEY` est retiré de l'URL.

### Compatibilite HTML et SVG

Le parseur inspecte les noeuds texte du DOM, puis cree automatiquement :

- un `span` en contexte HTML
- un `tspan` en contexte SVG

Cela permet d'utiliser la meme syntaxe dans des templates HTML classiques et dans des contenus SVG (`text`, `textPath`, etc.).
