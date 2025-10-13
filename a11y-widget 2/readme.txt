=== A11y Widget – Module d’accessibilité (mini) ===
Contributors: chatgpt
Tags: accessibility, a11y, widget
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

Un bouton flottant ouvre un module d’accessibilité avec des interrupteurs **placeholders** (rien n’est appliqué par défaut). Vous pouvez brancher vos propres styles/scripts via `data-*`, l’API JS et les CustomEvents.

== Utilisation ==
1. Uploadez le dossier `a11y-widget` dans `wp-content/plugins/`.
2. Activez le plugin.
3. Par défaut, le widget s’affiche en bas à droite de toutes les pages (injection via `wp_footer`).

- Shortcode: `[a11y_widget]` pour l’afficher où vous voulez.
- Désactiver l’injection auto (dans functions.php): 
  `add_filter( 'a11y_widget_enable_auto', '__return_false' );`

== API ==
- `window.A11yWidget.registerFeature('mode-nuit', fn)` – écoute les bascules.
- `window.A11yWidget.get('mode-nuit')` – lit l’état.
- `window.A11yWidget.set('mode-nuit', true)` – définit l’état (+ persistance).

Chaque toggle applique/retire `data-*` sur `<html>`, p. ex. `data-a11yModeNuit="on"`.
Branchez vos règles CSS globales selon ces attributs, ou réagissez en JS.

== Sécurité/Accessibilité ==
- Rôle `dialog`, `aria-modal`, piège du focus, Échap pour fermer.
- Préférences persistées via localStorage.
