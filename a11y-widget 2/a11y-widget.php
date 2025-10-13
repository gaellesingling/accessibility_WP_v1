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
 * Default widget sections definition (hierarchical: level 1 categories + level 2 placeholders).
 *
 * @return array[]
 */
function a11y_widget_get_default_sections() {
    return array(
        array(
            'slug'     => 'vision',
            'title'    => __( 'Vision', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'       => 'vision-texte-plus-grand',
                    'label'      => __( 'Texte plus grand', 'a11y-widget' ),
                    'hint'       => __( 'Ex : +15% / +30%', 'a11y-widget' ),
                    'aria_label' => __( 'Augmenter la taille du texte', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'vision-contraste',
                    'label'      => __( 'Contraste renforcé', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (high contrast)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le contraste renforcé', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'vision-mode-nuit',
                    'label'      => __( 'Mode nuit', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (thème sombre)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le mode nuit', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'vision-lumiere-bleue',
                    'label'      => __( 'Réduire la lumière bleue', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (teinte chaude)', 'a11y-widget' ),
                    'aria_label' => __( 'Réduire la lumière bleue', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'vision-protanopie',
                    'label'      => __( 'Profil protanopie', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (palette adaptée)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le profil protanopie', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'vision-deuteranopie',
                    'label'      => __( 'Profil deutéranopie', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le profil deutéranopie', 'a11y-widget' ),
                ),
            ),
        ),
        array(
            'slug'     => 'cognitif',
            'title'    => __( 'Cognitif', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'       => 'cognitif-dyslexie',
                    'label'      => __( 'Dyslexie', 'a11y-widget' ),
                    'hint'       => __( 'Placez votre police/espacement', 'a11y-widget' ),
                    'aria_label' => __( 'Activer le profil dyslexie', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'cognitif-lecture',
                    'label'      => __( 'Lecture facilitée', 'a11y-widget' ),
                    'hint'       => __( 'Ex : guide de lecture, surlignage', 'a11y-widget' ),
                    'aria_label' => __( 'Activer la lecture facilitée', 'a11y-widget' ),
                ),
            ),
        ),
        array(
            'slug'     => 'moteur',
            'title'    => __( 'Moteur', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'       => 'moteur-grands-boutons',
                    'label'      => __( 'Grands boutons', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (hit areas)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer les grands boutons', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'moteur-espacement-liens',
                    'label'      => __( 'Espacement des liens', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (espacement > 44px)', 'a11y-widget' ),
                    'aria_label' => __( 'Augmenter l’espacement des liens', 'a11y-widget' ),
                ),
            ),
        ),
        array(
            'slug'     => 'epilepsie',
            'title'    => __( 'Épilepsie', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'       => 'epilepsie-reduire-animations',
                    'label'      => __( 'Réduire les animations', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (désactiver les effets rapides)', 'a11y-widget' ),
                    'aria_label' => __( 'Réduire les animations pour limiter le risque de crises', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'epilepsie-avertissement-clignotement',
                    'label'      => __( 'Avertissement clignotements', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (alerte avant contenu clignotant)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer un avertissement avant les clignotements', 'a11y-widget' ),
                ),
            ),
        ),
        array(
            'slug'     => 'audition',
            'title'    => __( 'Audition', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'       => 'audition-sous-titres',
                    'label'      => __( 'Sous-titres', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (activer les sous-titres / captions)', 'a11y-widget' ),
                    'aria_label' => __( 'Activer les sous-titres', 'a11y-widget' ),
                ),
                array(
                    'slug'       => 'audition-transcriptions',
                    'label'      => __( 'Transcriptions', 'a11y-widget' ),
                    'hint'       => __( 'Placeholder (proposer une transcription audio)', 'a11y-widget' ),
                    'aria_label' => __( 'Afficher la transcription audio', 'a11y-widget' ),
                ),
            ),
        ),
    );
}

/**
 * Parse Markdown feature files located in the plugin `features/` directory.
 *
 * File format (per line, bullet list):
 *   # Mon titre de section (catégorie niveau 1)
 *   - `slug` **Label** : Hint optionnel (placeholders niveau 2)
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

    sort( $files );

    $sections      = array();
    $section_order = array();

    foreach ( $files as $file ) {
        $lines = file( $file, FILE_IGNORE_NEW_LINES );
        if ( false === $lines ) {
            continue;
        }

        $current_section = null;

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

                $slug = sanitize_title( $title );
                if ( '' === $slug ) {
                    $current_section = null;
                    continue;
                }

                if ( ! isset( $sections[ $slug ] ) ) {
                    $sections[ $slug ] = array(
                        'slug'     => $slug,
                        'title'    => $title,
                        'children' => array(),
                    );
                    $section_order[] = $slug;
                } elseif ( '' === $sections[ $slug ]['title'] ) {
                    $sections[ $slug ]['title'] = $title;
                }

                $current_section = $slug;
                continue;
            }

            if ( 0 !== strpos( $line, '-' ) || null === $current_section ) {
                continue;
            }

            if ( preg_match( '/-\s*`([^`]+)`\s*(?:\*\*(.+?)\*\*|([^:]+))?\s*(?::\s*(.+))?$/u', $line, $matches ) ) {
                $slug = sanitize_key( $matches[1] );
                if ( '' === $slug ) {
                    continue;
                }

                if ( isset( $sections[ $current_section ]['children'][ $slug ] ) ) {
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

                $sections[ $current_section ]['children'][ $slug ] = array(
                    'slug'       => $slug,
                    'label'      => $raw_label,
                    'hint'       => $hint,
                    'aria_label' => sprintf( __( 'Activer %s', 'a11y-widget' ), $raw_label ),
                    'source'     => basename( $file ),
                );
            }
        }
    }

    $ordered_sections = array();
    foreach ( $section_order as $slug ) {
        if ( ! isset( $sections[ $slug ] ) ) {
            continue;
        }

        $section = $sections[ $slug ];
        if ( ! empty( $section['children'] ) ) {
            $section['children'] = array_values( $section['children'] );
        } else {
            $section['children'] = array();
        }

        $ordered_sections[] = $section;
    }

    $cache = $ordered_sections;

    return $cache;
}

/**
 * Merge default and Markdown-defined sections without overwriting existing slugs.
 *
 * @return array[]
 */
function a11y_widget_get_sections() {
    $defaults          = a11y_widget_get_default_sections();
    $sections_by_slug  = array();
    $ordered_slugs     = array();
    $child_slug_global = array();

    foreach ( $defaults as $section ) {
        if ( empty( $section['slug'] ) ) {
            continue;
        }

        $slug = sanitize_title( $section['slug'] );
        if ( '' === $slug ) {
            continue;
        }

        $section['slug'] = $slug;

        if ( ! isset( $section['children'] ) || ! is_array( $section['children'] ) ) {
            $section['children'] = array();
        }

        $sections_by_slug[ $slug ] = $section;
        $ordered_slugs[]           = $slug;

        foreach ( $section['children'] as $child ) {
            if ( empty( $child['slug'] ) ) {
                continue;
            }

            $child_slug_global[ $child['slug'] ] = true;
        }
    }

    $extra_sections = a11y_widget_parse_markdown_sections();

    foreach ( $extra_sections as $section ) {
        if ( empty( $section['slug'] ) ) {
            continue;
        }

        $slug = sanitize_title( $section['slug'] );
        if ( '' === $slug ) {
            continue;
        }

        if ( ! isset( $sections_by_slug[ $slug ] ) ) {
            $sections_by_slug[ $slug ] = array(
                'slug'     => $slug,
                'title'    => isset( $section['title'] ) ? $section['title'] : '',
                'children' => array(),
            );
            $ordered_slugs[] = $slug;
        } elseif ( '' !== $section['title'] ) {
            $sections_by_slug[ $slug ]['title'] = $sections_by_slug[ $slug ]['title'] ? $sections_by_slug[ $slug ]['title'] : $section['title'];
        }

        if ( empty( $section['children'] ) ) {
            continue;
        }

        if ( ! isset( $sections_by_slug[ $slug ]['children'] ) || ! is_array( $sections_by_slug[ $slug ]['children'] ) ) {
            $sections_by_slug[ $slug ]['children'] = array();
        }

        foreach ( $section['children'] as $child ) {
            if ( empty( $child['slug'] ) ) {
                continue;
            }

            if ( isset( $child_slug_global[ $child['slug'] ] ) ) {
                continue;
            }

            $child_slug_global[ $child['slug'] ] = true;
            $sections_by_slug[ $slug ]['children'][] = $child;
        }
    }

    $sections = array();
    foreach ( $ordered_slugs as $slug ) {
        if ( ! isset( $sections_by_slug[ $slug ] ) ) {
            continue;
        }

        $section = $sections_by_slug[ $slug ];
        if ( ! isset( $section['title'] ) ) {
            $section['title'] = ''; // ensure key exists for template.
        }

        if ( ! isset( $section['children'] ) || ! is_array( $section['children'] ) ) {
            $section['children'] = array();
        }

        if ( ! empty( $section['children'] ) ) {
            $section['children'] = array_values( $section['children'] );
        }

        $sections[] = $section;
    }

    /**
     * Filter the final list of sections sent to the template.
     *
     * @param array $sections Sections with children.
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
