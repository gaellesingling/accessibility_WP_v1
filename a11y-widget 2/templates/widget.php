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
      <p id="a11y-desc" class="a11y-intro"><?php echo esc_html__('Adaptez le site selon vos préférences. Les options sont des emplacements vides. À vous de brancher vos styles/scripts.', 'a11y-widget'); ?></p>

      <?php
      $sections          = a11y_widget_get_sections();
      $first_section_id  = 'a11y-sec-0';
      $used_section_ids  = array();
      ?>
      <?php if ( ! empty( $sections ) ) : ?>
        <div class="a11y-nav" id="a11y-sections" role="tablist" aria-label="<?php echo esc_attr__( 'Catégories d’accessibilité', 'a11y-widget' ); ?>">
          <?php foreach ( $sections as $index => $section ) :
            $section_id = ! empty( $section['id'] ) ? sanitize_title( $section['id'] ) : 'a11y-sec-' . $index;
            if ( '' === $section_id ) {
                $section_id = 'a11y-sec-' . $index;
            }
            if ( in_array( $section_id, $used_section_ids, true ) ) {
                $section_id = 'a11y-sec-' . $index;
            }
            $used_section_ids[] = $section_id;
            if ( 0 === $index ) {
                $first_section_id = $section_id;
            }
            $section_title = isset( $section['title'] ) ? $section['title'] : '';
            $section_hint  = isset( $section['hint'] ) ? $section['hint'] : '';
            $tab_id        = 'a11y-tab-' . $section_id;
            ?>
            <button
              type="button"
              class="a11y-tab<?php echo 0 === $index ? ' is-active' : ''; ?>"
              id="<?php echo esc_attr( $tab_id ); ?>"
              role="tab"
              aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
              aria-controls="a11y-section-panel"
              tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
              data-section-index="<?php echo esc_attr( $index ); ?>"
            >
              <span class="a11y-tab__label"><?php echo esc_html( $section_title ); ?></span>
              <?php if ( '' !== $section_hint ) : ?>
                <span class="a11y-tab__hint"><?php echo esc_html( $section_hint ); ?></span>
              <?php endif; ?>
            </button>
          <?php endforeach; ?>
        </div>

        <section class="a11y-level2" id="a11y-section-panel" role="tabpanel" tabindex="0" aria-live="polite" aria-labelledby="<?php echo esc_attr( 'a11y-tab-' . $first_section_id ); ?>">
          <div class="a11y-grid" data-role="feature-grid"></div>
          <p class="a11y-empty" data-role="empty" hidden data-default-empty="<?php echo esc_attr__( 'Aucune fonctionnalité disponible pour le moment.', 'a11y-widget' ); ?>"><?php echo esc_html__( 'Aucune fonctionnalité disponible pour le moment.', 'a11y-widget' ); ?></p>
        </section>

        <template id="a11y-feature-template">
          <article class="a11y-card">
            <div class="meta">
              <span class="label"></span>
              <span class="hint" hidden></span>
            </div>
            <label class="a11y-switch">
              <input type="checkbox" data-feature="" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>
        </template>

        <script type="application/json" id="a11y-widget-data"><?php echo wp_json_encode( $sections ); ?></script>
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
