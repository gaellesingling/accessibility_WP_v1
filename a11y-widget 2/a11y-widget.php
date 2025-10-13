<?php
/**
 * Plugin Name: A11y Widget – Module d’accessibilité (mini)
 * Description: Bouton flottant qui ouvre un module d’accessibilité avec placeholders (à brancher selon vos besoins). Shortcode: [a11y_widget]. 
 * Version: 1.1.0
 * Author: ChatGPT
 * License: GPL-2.0-or-later
 * Text Domain: a11y-widget
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'A11Y_WIDGET_VERSION', '1.1.0' );
define( 'A11Y_WIDGET_URL', plugin_dir_url( __FILE__ ) );
define( 'A11Y_WIDGET_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Enqueue front assets
 */
function a11y_widget_enqueue() {
    // Only load on front-end
    if ( is_admin() ) { return; }

    wp_enqueue_style(
        'a11y-widget',
        A11Y_WIDGET_URL . 'assets/widget.css',
        array(),
        A11Y_WIDGET_VERSION
    );

    wp_enqueue_script(
        'a11y-widget',
        A11Y_WIDGET_URL . 'assets/widget.js',
        array(),
        A11Y_WIDGET_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'a11y_widget_enqueue' );

/**
 * Render the widget HTML
 */
function a11y_widget_markup() {
    // Allow theme/plugins to disable automatic output
    $enable_auto = apply_filters( 'a11y_widget_enable_auto', true );
    if ( did_action('a11y_widget_printed') ) { return; } // avoid duplicates

    ob_start();
    include A11Y_WIDGET_PATH . 'templates/widget.php';
    $html = ob_get_clean();

    /**
     * Filter: change/augment the HTML before output
     */
    $html = apply_filters( 'a11y_widget_markup', $html );

    echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    do_action('a11y_widget_printed');
}

/**
 * Default widget sections/features definition.
 *
 * @return array[]
 */
function a11y_widget_get_default_sections() {
    return array(
        array(
            'id'       => 'cog',
            'title'    => __( 'Besoins cognitifs', 'a11y-widget' ),
            'features' => array(
                array(
                    'slug'       => 'dyslexie',
                    'label'      => __( 'Dyslexie', 'a11y-widget' ),
                    'hint'       => __( 'Placez votre police/espacement', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le profil dyslexie', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'lecture',
                    'label'      => __( 'Lecture facilitée', 'a11y-widget' ),
                    'hint'       => __( 'Ex : guide de lecture, surlignage', 'a11y-widget' ),
                    'aria_label' => __( 'Activer la lecture facilitée', 'a11y-widget' ),
                ),
            ),
        ),
        array(
            'id'       => 'visuel',
            'title'    => __( 'Besoins visuels', 'a11y-widget' ),
            'features' => array(
                array(
                    'slug'       => 'texte-plus-grand',
                    'label'      => __( 'Texte plus grand', 'a11y-widget' ),
                    'hint'       => __( 'Ex : +15% / +30%', 'a11y-widget' ),
                    'aria_label' => __( 'Augmenter la taille du texte', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'contraste',
                    'label'      => __( 'Contraste renforcé', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (high contrast)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le contraste renforcé', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'mode-nuit',
                    'label'      => __( 'Mode nuit', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (thème sombre)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le mode nuit', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'lumiere-bleue',
                    'label'      => __( 'Réduire la lumière bleue', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (teinte chaude)', 'a11y-widget' ),
                    'aria_label' => __( 'Réduire la lumière bleue', 'a11y-widget' ),
                ),
            ),
        ),
        array(
            'id'       => 'gesture',
            'title'    => __( 'Précision de geste', 'a11y-widget' ),
            'features' => array(
                array(
                    'slug'       => 'grands-boutons',
                    'label'      => __( 'Grands boutons', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (hit areas)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer les grands boutons', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'espacement-liens',
                    'label'      => __( 'Espacement des liens', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (espacement > 44px)', 'a11y-widget' ),
                    'aria_label' => __( 'Augmenter l’espacement des liens', 'a11y-widget' ),
                ),
            ),
        ),
        array(
            'id'       => 'color',
            'title'    => __( 'Daltonismes (exemples)', 'a11y-widget' ),
            'features' => array(
                array(
                    'slug'       => 'protanopie',
                    'label'      => __( 'Protanopie', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (palette adaptée)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le profil protanopie', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'deuteranopie',
                    'label'      => __( 'Deutéranopie', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le profil deutéranopie', 'a11y-widget' ),
                ),
            ),
        ),
    );
}

/**
 * Parse Markdown feature files located in the plugin `features/` directory.
 *
 * File format (per line, bullet list):
 *   # Mon titre de section
 *   - `slug` **Label** : Hint optionnel
 *
 * @return array[] Parsed sections.
 */
function a11y_widget_parse_markdown_sections() {
    static $cache = null;

    if ( null !== $cache ) {
        return $cache;
    }

    $dir = trailingslashit( A11Y_WIDGET_PATH ) . 'features';

    if ( ! is_dir( $dir ) ) {
        return array();
    }

    $files = glob( trailingslashit( $dir ) . '*.md' );
    if ( false === $files || empty( $files ) ) {
        return array();
    }

    $sections = array();

    foreach ( $files as $file ) {
        $lines = file( $file, FILE_IGNORE_NEW_LINES );
        if ( false === $lines ) {
            continue;
        }

        $current_index = null;

        foreach ( $lines as $raw_line ) {
            $line = trim( $raw_line );

            if ( '' === $line ) {
                continue;
            }

            if ( preg_match( '/^#{1,6}\s*(.+)$/u', $line, $matches ) ) {
                $title = wp_strip_all_tags( trim( $matches[1] ) );
                if ( '' === $title ) {
                    continue;
                }

                $sections[] = array(
                    'id'       => sanitize_title( $title ),
                    'title'    => $title,
                    'features' => array(),
                    'source'   => basename( $file ),
                );
                $current_index = count( $sections ) - 1;
                continue;
            }

            if ( 0 !== strpos( $line, '-' ) || null === $current_index ) {
                continue;
            }

            if ( preg_match( '/-\s*`([^`]+)`\s*(?:\*\*(.+?)\*\*|([^:]+))?\s*(?::\s*(.+))?$/u', $line, $matches ) ) {
                $slug = sanitize_key( $matches[1] );
                if ( '' === $slug ) {
                    continue;
                }

                $raw_label = '';
                if ( ! empty( $matches[2] ) ) {
                    $raw_label = $matches[2];
                } elseif ( ! empty( $matches[3] ) ) {
                    $raw_label = trim( $matches[3] );
                }

                if ( '' === $raw_label ) {
                    $raw_label = $slug;
                }

                $raw_label = wp_strip_all_tags( $raw_label );

                $hint = '';
                if ( isset( $matches[4] ) ) {
                    $hint = wp_strip_all_tags( trim( $matches[4] ) );
                }

                $sections[ $current_index ]['features'][] = array(
                    'slug'       => $slug,
                    'label'      => $raw_label,
                    'hint'       => $hint,
                    'aria_label' => sprintf( __( 'Activer %s', 'a11y-widget' ), $raw_label ),
                    'source'     => basename( $file ),
                );
            }
        }
    }

    $cache = $sections;

    return $cache;
}

/**
 * Merge default and Markdown-defined sections without overwriting existing slugs.
 *
 * @return array[]
 */
function a11y_widget_get_sections() {
    $sections       = a11y_widget_get_default_sections();
    $existing_slugs = array();

    foreach ( $sections as $section ) {
        if ( empty( $section['features'] ) ) {
            continue;
        }

        foreach ( $section['features'] as $feature ) {
            if ( empty( $feature['slug'] ) ) {
                continue;
            }

            $existing_slugs[ $feature['slug'] ] = true;
        }
    }

    $extra_sections = a11y_widget_parse_markdown_sections();

    foreach ( $extra_sections as $section ) {
        if ( empty( $section['features'] ) ) {
            continue;
        }

        $section_features = array();

        foreach ( $section['features'] as $feature ) {
            if ( empty( $feature['slug'] ) || isset( $existing_slugs[ $feature['slug'] ] ) ) {
                continue;
            }

            $existing_slugs[ $feature['slug'] ] = true;
            $section_features[]                 = $feature;
        }

        if ( ! empty( $section_features ) ) {
            $section['features'] = $section_features;
            $sections[]          = $section;
        }
    }

    /**
     * Filter the final list of sections sent to the template.
     *
     * @param array $sections Sections with features.
     */
    return apply_filters( 'a11y_widget_sections', $sections );
}

/**
 * Auto-inject in footer unless disabled
 */
function a11y_widget_auto_inject() {
    $enable_auto = apply_filters( 'a11y_widget_enable_auto', true );
    if ( $enable_auto ) {
        a11y_widget_markup();
    }
}
add_action( 'wp_footer', 'a11y_widget_auto_inject', 5 );

/**
 * Shortcode: [a11y_widget]
 */
function a11y_widget_shortcode() {
    ob_start();
    include A11Y_WIDGET_PATH . 'templates/widget.php';
    return ob_get_clean();
}
add_shortcode( 'a11y_widget', 'a11y_widget_shortcode' );
