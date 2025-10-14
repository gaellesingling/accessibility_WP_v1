<?php
/**
 * Administration settings for the accessibility widget.
 *
 * @package A11yWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Normalize a list of feature slugs.
 *
 * @param mixed $items Slugs to sanitize.
 *
 * @return string[]
 */
if ( ! function_exists( 'a11y_widget_normalize_feature_slugs' ) ) {
    function a11y_widget_normalize_feature_slugs( $items ) {
        if ( ! is_array( $items ) ) {
            $items = array( $items );
        }

        $normalized = array();

        foreach ( $items as $slug ) {
            $slug = sanitize_key( $slug );

            if ( '' === $slug ) {
                continue;
            }

            $normalized[ $slug ] = true;
        }

        return array_keys( $normalized );
    }
}

/**
 * Option name helper for disabled features.
 *
 * @return string
 */
function a11y_widget_get_disabled_features_option_name() {
    return 'a11y_widget_disabled_features';
}

/**
 * Retrieve the list of disabled features stored in the database.
 *
 * @return string[]
 */
function a11y_widget_get_disabled_features() {
    $stored = get_option( a11y_widget_get_disabled_features_option_name(), array() );

    if ( empty( $stored ) ) {
        return array();
    }

    return a11y_widget_normalize_feature_slugs( $stored );
}

/**
 * Sanitize disabled features before saving the option.
 *
 * @param mixed $input Raw input.
 *
 * @return string[]
 */
function a11y_widget_sanitize_disabled_features( $input ) {
    if ( null === $input ) {
        return array();
    }

    return a11y_widget_normalize_feature_slugs( $input );
}

/**
 * Register plugin settings used by the admin screen.
 */
function a11y_widget_register_settings() {
    register_setting(
        'a11y_widget_settings',
        a11y_widget_get_disabled_features_option_name(),
        array(
            'type'              => 'array',
            'sanitize_callback' => 'a11y_widget_sanitize_disabled_features',
            'default'           => array(),
        )
    );
}
add_action( 'admin_init', 'a11y_widget_register_settings' );

/**
 * Add the "Accessibilité" menu entry in the WordPress administration.
 */
function a11y_widget_register_admin_menu() {
    add_menu_page(
        __( 'Accessibilité RGAA', 'a11y-widget' ),
        __( 'Accessibilité', 'a11y-widget' ),
        'manage_options',
        'a11y-widget',
        'a11y_widget_render_admin_page',
        'dashicons-universal-access-alt',
        58
    );
}
add_action( 'admin_menu', 'a11y_widget_register_admin_menu' );

/**
 * Enqueue styles for the admin settings screen.
 *
 * @param string $hook Current admin page.
 */
function a11y_widget_enqueue_admin_assets( $hook ) {
    if ( 'toplevel_page_a11y-widget' !== $hook ) {
        return;
    }

    wp_enqueue_style(
        'a11y-widget-admin',
        A11Y_WIDGET_URL . 'assets/admin.css',
        array(),
        A11Y_WIDGET_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'a11y_widget_enqueue_admin_assets' );

/**
 * Render the admin page that lets site administrators hide specific features.
 */
function a11y_widget_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $sections        = a11y_widget_get_sections();
    $disabled        = a11y_widget_get_disabled_features();
    $disabled_lookup = array_fill_keys( $disabled, true );
    ?>
    <div class="wrap a11y-widget-admin">
        <h1><?php esc_html_e( 'Accessibilité RGAA', 'a11y-widget' ); ?></h1>
        <p class="a11y-widget-admin__intro">
            <?php esc_html_e( 'Toutes les fonctionnalités sont actives par défaut. Décochez celles que vous souhaitez masquer aux utilisateurs finaux.', 'a11y-widget' ); ?>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields( 'a11y_widget_settings' ); ?>

            <?php if ( empty( $sections ) ) : ?>
                <p class="a11y-widget-admin-empty">
                    <?php esc_html_e( 'Aucune fonctionnalité n’est disponible pour le moment.', 'a11y-widget' ); ?>
                </p>
            <?php else : ?>
                <div class="a11y-widget-admin-grid">
                    <?php
                    foreach ( $sections as $section ) :
                        $section_title = isset( $section['title'] ) ? $section['title'] : '';
                        $section_slug  = isset( $section['slug'] ) ? sanitize_title( $section['slug'] ) : '';
                        $children      = isset( $section['children'] ) && is_array( $section['children'] ) ? $section['children'] : array();
                        ?>
                        <fieldset class="a11y-widget-admin-section">
                            <legend class="a11y-widget-admin-section__title"><?php echo esc_html( $section_title ); ?></legend>

                            <div class="a11y-widget-admin-section__content">
                                <?php if ( empty( $children ) ) : ?>
                                    <p class="a11y-widget-admin-empty">
                                        <em><?php esc_html_e( 'Aucune fonctionnalité dans cette catégorie.', 'a11y-widget' ); ?></em>
                                    </p>
                                <?php else : ?>
                                    <?php
                                    foreach ( $children as $feature ) :
                                        $feature_slug  = isset( $feature['slug'] ) ? sanitize_key( $feature['slug'] ) : '';
                                        $feature_label = isset( $feature['label'] ) ? $feature['label'] : '';
                                        $feature_hint  = isset( $feature['hint'] ) ? $feature['hint'] : '';

                                        if ( '' === $feature_slug || '' === $feature_label ) {
                                            continue;
                                        }

                                        $is_disabled = isset( $disabled_lookup[ $feature_slug ] );
                                        $input_id    = 'a11y-widget-toggle-' . ( $section_slug ? $section_slug . '-' : '' ) . $feature_slug;
                                        ?>
                                        <div class="a11y-widget-admin-feature">
                                            <div class="a11y-widget-admin-feature__description">
                                                <label for="<?php echo esc_attr( $input_id ); ?>">
                                                    <span class="a11y-widget-admin-feature__label"><?php echo esc_html( $feature_label ); ?></span>
                                                    <?php if ( '' !== $feature_hint ) : ?>
                                                        <span class="description"><?php echo esc_html( $feature_hint ); ?></span>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                            <div class="a11y-widget-admin-toggle">
                                                <label class="a11y-widget-switch" for="<?php echo esc_attr( $input_id ); ?>">
                                                    <span class="screen-reader-text">
                                                        <?php
                                                        printf(
                                                            /* translators: %s: feature label */
                                                            esc_html__( 'Masquer la fonctionnalité « %s » pour les utilisateurs', 'a11y-widget' ),
                                                            wp_strip_all_tags( $feature_label )
                                                        );
                                                        ?>
                                                    </span>
                                                    <input
                                                        type="checkbox"
                                                        id="<?php echo esc_attr( $input_id ); ?>"
                                                        name="<?php echo esc_attr( a11y_widget_get_disabled_features_option_name() ); ?>[]"
                                                        value="<?php echo esc_attr( $feature_slug ); ?>"
                                                        <?php checked( $is_disabled ); ?>
                                                    />
                                                    <span class="a11y-widget-switch__ui">
                                                        <span
                                                            class="a11y-widget-switch__state"
                                                            data-state-visible="<?php echo esc_attr_x( 'Visible', 'feature state', 'a11y-widget' ); ?>"
                                                            data-state-hidden="<?php echo esc_attr_x( 'Masqué', 'feature state', 'a11y-widget' ); ?>"
                                                        ></span>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </fieldset>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php submit_button( __( 'Enregistrer les modifications', 'a11y-widget' ) ); ?>
        </form>
    </div>
    <?php
}

/**
 * Remove disabled features from the sections used on the front-end.
 *
 * @param array $sections Sections passed to the template.
 *
 * @return array
 */
function a11y_widget_filter_disabled_features( $sections ) {
    $doing_ajax = false;

    if ( function_exists( 'wp_doing_ajax' ) ) {
        $doing_ajax = wp_doing_ajax();
    } elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        $doing_ajax = true;
    }

    if ( is_admin() && ! $doing_ajax ) {
        return $sections;
    }

    $disabled = a11y_widget_get_disabled_features();

    if ( empty( $disabled ) ) {
        return $sections;
    }

    $disabled_lookup = array_fill_keys( $disabled, true );
    $filtered        = array();

    foreach ( $sections as $section ) {
        if ( ! isset( $section['children'] ) || ! is_array( $section['children'] ) ) {
            continue;
        }

        $children = array();

        foreach ( $section['children'] as $feature ) {
            $slug = isset( $feature['slug'] ) ? sanitize_key( $feature['slug'] ) : '';

            if ( '' === $slug ) {
                continue;
            }

            if ( isset( $disabled_lookup[ $slug ] ) ) {
                continue;
            }

            $children[] = $feature;
        }

        if ( empty( $children ) ) {
            continue;
        }

        $section['children'] = array_values( $children );
        $filtered[]           = $section;
    }

    return $filtered;
}
add_filter( 'a11y_widget_sections', 'a11y_widget_filter_disabled_features', 20 );
