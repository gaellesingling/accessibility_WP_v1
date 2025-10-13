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
        <?php
        $tablist_id  = 'a11y-section-tabs';
        $tabpanel_id = 'a11y-section-panel';
        $template_id = 'a11y-feature-template';
        $payload     = array();
        $first_tab_id = '';
        ?>
        <nav id="<?php echo esc_attr( $tablist_id ); ?>" class="a11y-tabs" role="tablist" aria-label="<?php echo esc_attr__( 'Catégories d’accessibilité', 'a11y-widget' ); ?>">
          <?php foreach ( $sections as $index => $section ) :
            $section_slug  = ! empty( $section['slug'] ) ? sanitize_title( $section['slug'] ) : '';
            $section_id    = $section_slug ? $section_slug : ( ! empty( $section['id'] ) ? sanitize_title( $section['id'] ) : sanitize_title( uniqid( 'a11y-sec-', true ) ) );
            $section_title = isset( $section['title'] ) ? $section['title'] : '';
            $children      = isset( $section['children'] ) ? (array) $section['children'] : array();
            $features_data = array();

            if ( ! empty( $children ) ) {
                foreach ( $children as $feature ) {
                    $feature_slug       = isset( $feature['slug'] ) ? sanitize_title( $feature['slug'] ) : '';
                    $feature_label      = isset( $feature['label'] ) ? $feature['label'] : '';
                    $feature_hint       = isset( $feature['hint'] ) ? $feature['hint'] : '';
                    $feature_aria_label = isset( $feature['aria_label'] ) ? $feature['aria_label'] : $feature_label;

                    if ( '' === $feature_slug || '' === $feature_label ) {
                        continue;
                    }

                    $features_data[] = array(
                        'slug'       => $feature_slug,
                        'label'      => wp_strip_all_tags( $feature_label ),
                        'hint'       => wp_strip_all_tags( $feature_hint ),
                        'aria_label' => wp_strip_all_tags( $feature_aria_label ),
                    );
                }
            }

            $payload[] = array(
                'index'    => (int) $index,
                'id'       => $section_id,
                'slug'     => $section_slug ? $section_slug : $section_id,
                'title'    => wp_strip_all_tags( $section_title ),
                'features' => $features_data,
            );

            $tab_id     = 'a11y-tab-' . $section_id;
            $is_active  = 0 === (int) $index;
            if ( $is_active && '' === $first_tab_id ) {
                $first_tab_id = 'a11y-tab-' . $section_id;
            }
            $tab_class  = 'a11y-tab' . ( $is_active ? ' is-active' : '' );
            ?>
            <button
              type="button"
              class="<?php echo esc_attr( $tab_class ); ?>"
              role="tab"
              id="<?php echo esc_attr( $tab_id ); ?>"
              aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
              aria-controls="<?php echo esc_attr( $tabpanel_id ); ?>"
              tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
              data-section-index="<?php echo esc_attr( $index ); ?>"
              data-section-id="<?php echo esc_attr( $section_id ); ?>"
              data-tablist-id="<?php echo esc_attr( $tablist_id ); ?>"
            >
              <?php echo esc_html( $section_title ); ?>
            </button>
          <?php endforeach; ?>
        </nav>

        <div
          class="a11y-section-panel"
          role="tabpanel"
          id="<?php echo esc_attr( $tabpanel_id ); ?>"
          tabindex="0"
          aria-live="polite"
          data-role="section-panel"
          <?php if ( '' !== $first_tab_id ) : ?>aria-labelledby="<?php echo esc_attr( $first_tab_id ); ?>"<?php endif; ?>
        >
          <div class="a11y-grid" data-role="feature-grid"></div>
          <p class="a11y-empty" data-role="feature-empty" hidden><?php echo esc_html__( 'Aucune fonctionnalité disponible pour le moment.', 'a11y-widget' ); ?></p>
        </div>

        <template id="<?php echo esc_attr( $template_id ); ?>" data-role="feature-template">
          <article class="a11y-card">
            <div class="meta">
              <span class="label" data-role="feature-label"></span>
              <span class="hint" data-role="feature-hint" hidden></span>
            </div>
            <label class="a11y-switch">
              <input type="checkbox" data-role="feature-input" data-feature="" aria-label="" />
              <span class="track"></span><span class="thumb"></span>
            </label>
          </article>
        </template>

        <script type="application/json" data-role="feature-data">
          <?php echo wp_json_encode( $payload ); ?>
        </script>
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
