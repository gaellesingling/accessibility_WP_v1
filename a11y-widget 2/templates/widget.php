<?php
/**
 * Widget markup (front)
 * This is printed in the footer or via shortcode.
 */
?>
<button class="a11y-launcher" id="a11y-launcher" aria-haspopup="dialog" aria-expanded="false" aria-controls="a11y-overlay" aria-label="<?php echo esc_attr__('Ouvrir le module d’accessibilité', 'a11y-widget'); ?>">
  <svg viewBox="0 0 24 24" role="img" aria-hidden="true"><path d="M12 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm6.75 6.5h-4.5v11a1 1 0 1 1-2 0v-5h-1v5a1 1 0 1 1-2 0v-11h-4.5a1 1 0 1 1 0-2h14a1 1 0 1 1 0 2Z"/></svg>
</button>

<div class="a11y-overlay" id="a11y-overlay" role="presentation" aria-hidden="true">
  <section class="a11y-panel" role="dialog" aria-modal="true" aria-labelledby="a11y-title" aria-describedby="a11y-desc">
    <header class="a11y-header">
      <svg class="a11y-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm6.75 6.5h-4.5v11a1 1 0 1 1-2 0v-5h-1v5a1 1 0 1 1-2 0v-11h-4.5a1 1 0 1 1 0-2h14a1 1 0 1 1 0 2Z"/></svg>
      <h2 id="a11y-title" class="a11y-title"><?php echo esc_html__('Accessibilité du site', 'a11y-widget'); ?></h2>
      <div class="a11y-spacer" aria-hidden="true"></div>
      <button class="a11y-close" id="a11y-close" aria-label="<?php echo esc_attr__('Fermer le module', 'a11y-widget'); ?>">✕</button>
    </header>

    <div class="a11y-content" id="a11y-content">
      <p id="a11y-desc" style="padding: 8px 12px; margin: 0;"><?php echo esc_html__('Adaptez le site selon vos préférences. Les options sont des emplacements vides. À vous de brancher vos styles/scripts.', 'a11y-widget'); ?></p>

      <section class="a11y-section" aria-labelledby="a11y-section-cog">
        <h3 id="a11y-section-cog"><?php echo esc_html__('Besoins cognitifs', 'a11y-widget'); ?></h3>
        <div class="a11y-grid">
          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Dyslexie', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Placez votre police/espacement', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="dyslexie" aria-label="<?php echo esc_attr__('Activer le profil dyslexie', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>

          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Lecture facilitée', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Ex : guide de lecture, surlignage', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="lecture" aria-label="<?php echo esc_attr__('Activer la lecture facilitée', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>
        </div>
      </section>

      <section class="a11y-section" aria-labelledby="a11y-section-visuel">
        <h3 id="a11y-section-visuel"><?php echo esc_html__('Besoins visuels', 'a11y-widget'); ?></h3>
        <div class="a11y-grid">
          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Texte plus grand', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Ex : +15% / +30%', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="texte-plus-grand" aria-label="<?php echo esc_attr__('Augmenter la taille du texte', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>

          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Contraste renforcé', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Placeholder (high contrast)', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="contraste" aria-label="<?php echo esc_attr__('Activer le contraste renforcé', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>

          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Mode nuit', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Placeholder (thème sombre)', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="mode-nuit" aria-label="<?php echo esc_attr__('Activer le mode nuit', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>

          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Réduire la lumière bleue', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Placeholder (teinte chaude)', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="lumiere-bleue" aria-label="<?php echo esc_attr__('Réduire la lumière bleue', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>
        </div>
      </section>

      <section class="a11y-section" aria-labelledby="a11y-section-gesture">
        <h3 id="a11y-section-gesture"><?php echo esc_html__('Précision de geste', 'a11y-widget'); ?></h3>
        <div class="a11y-grid">
          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Grands boutons', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Placeholder (hit areas)', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="grands-boutons" aria-label="<?php echo esc_attr__('Activer les grands boutons', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>

          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Espacement des liens', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Placeholder (espacement > 44px)', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="espacement-liens" aria-label="<?php echo esc_attr__('Augmenter l’espacement des liens', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>
        </div>
      </section>

      <section class="a11y-section" aria-labelledby="a11y-section-color">
        <h3 id="a11y-section-color"><?php echo esc_html__('Daltonismes (exemples)', 'a11y-widget'); ?></h3>
        <div class="a11y-grid">
          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Protanopie', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Placeholder (palette adaptée)', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="protanopie" aria-label="<?php echo esc_attr__('Activer le profil protanopie', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>
          <article class="a11y-card">
            <div class="meta"><span class="label"><?php echo esc_html__('Deutéranopie', 'a11y-widget'); ?></span><span class="hint"><?php echo esc_html__('Placeholder', 'a11y-widget'); ?></span></div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="deuteranopie" aria-label="<?php echo esc_attr__('Activer le profil deutéranopie', 'a11y-widget'); ?>" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>
        </div>
      </section>
    </div>

    <footer class="a11y-footer">
      <div>
        <button class="a11y-btn" id="a11y-reset"><?php echo esc_html__('Réinitialiser', 'a11y-widget'); ?></button>
      </div>
      <div>
        <button class="a11y-btn primary" id="a11y-close2"><?php echo esc_html__('Fermer', 'a11y-widget'); ?></button>
      </div>
    </footer>
  </section>
</div>
