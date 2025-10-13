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

      <?php $sections = a11y_widget_get_sections(); ?>
      <?php if ( ! empty( $sections ) ) : ?>
      <?php foreach ( $sections as $section ) : ?>
        <?php
        $section_slug  = ! empty( $section['slug'] ) ? sanitize_title( $section['slug'] ) : '';
        $section_id    = $section_slug ? $section_slug : ( ! empty( $section['id'] ) ? sanitize_title( $section['id'] ) : sanitize_title( uniqid( 'a11y-sec-', true ) ) );
        $section_title = isset( $section['title'] ) ? $section['title'] : '';
        $children      = isset( $section['children'] ) ? (array) $section['children'] : array();
        ?>
        <section class="a11y-section" aria-labelledby="a11y-section-<?php echo esc_attr( $section_id ); ?>">
          <h3 id="a11y-section-<?php echo esc_attr( $section_id ); ?>"><?php echo esc_html( $section_title ); ?></h3>
          <?php if ( ! empty( $children ) ) : ?>
            <div class="a11y-grid">
              <?php foreach ( $children as $feature ) :
                $slug       = isset( $feature['slug'] ) ? $feature['slug'] : '';
                $label      = isset( $feature['label'] ) ? $feature['label'] : '';
                $hint       = isset( $feature['hint'] ) ? $feature['hint'] : '';
                $aria_label = isset( $feature['aria_label'] ) ? $feature['aria_label'] : $label;
                if ( '' === $slug || '' === $label ) {
                    continue;
                }
                ?>
                <article class="a11y-card">
                  <div class="meta"><span class="label"><?php echo esc_html( $label ); ?></span><?php if ( '' !== $hint ) : ?><span class="hint"><?php echo esc_html( $hint ); ?></span><?php endif; ?></div>
                  <label class="a11y-switch">
                    <input type="checkbox" data-feature="<?php echo esc_attr( $slug ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>" />
                    <span class="track"></span><span class="thumb"></span>
                  </label>
                </article>
              <?php endforeach; ?>
            </div>
          <?php else : ?>
            <p class="a11y-empty"><?php echo esc_html__( 'Aucune fonctionnalité disponible pour le moment.', 'a11y-widget' ); ?></p>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
      <?php else : ?>
        <p class="a11y-empty"><?php echo esc_html__( 'Aucune fonctionnalité disponible pour le moment.', 'a11y-widget' ); ?></p>
      <?php endif; ?>
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
