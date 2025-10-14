<?php
/**
 * Plugin Name: A11y Widget – Module d’accessibilité (mini)
 * Description: Bouton flottant qui ouvre un module d’accessibilité avec placeholders (à brancher selon vos besoins). Shortcode: [a11y_widget]. 
 * Version: 1.2.0
 * Author: ChatGPT
 * License: GPL-2.0-or-later
 * Text Domain: a11y-widget
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'A11Y_WIDGET_VERSION', '1.2.0' );
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
                    'slug'        => 'vision-placeholder',
                    'label'       => __( 'Exemple : augmenter la lisibilité', 'a11y-widget' ),
                    'hint'        => __( 'Ajoutez vos réglages pour la vision (contraste, taille du texte…).', 'a11y-widget' ),
                    'aria_label'  => __( 'Exemple de réglage pour la vision', 'a11y-widget' ),
                    'placeholder' => true,
                ),
            ),
        ),
        array(
            'slug'     => 'cognitif',
            'title'    => __( 'Cognitif', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'        => 'cognitif-placeholder',
                    'label'       => __( 'Exemple : aide à la lecture', 'a11y-widget' ),
                    'hint'        => __( 'Ajoutez vos outils pour le confort cognitif.', 'a11y-widget' ),
                    'aria_label'  => __( 'Exemple de réglage cognitif', 'a11y-widget' ),
                    'placeholder' => true,
                ),
            ),
        ),
        array(
            'slug'     => 'moteur',
            'title'    => __( 'Moteur', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'        => 'moteur-placeholder',
                    'label'       => __( 'Exemple : zones cliquables agrandies', 'a11y-widget' ),
                    'hint'        => __( 'Ajoutez vos options pour la navigation motrice.', 'a11y-widget' ),
                    'aria_label'  => __( 'Exemple de réglage moteur', 'a11y-widget' ),
                    'placeholder' => true,
                ),
            ),
        ),
        array(
            'slug'     => 'epilepsie',
            'title'    => __( 'Épilepsie', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'        => 'epilepsie-placeholder',
                    'label'       => __( 'Exemple : réduire les animations', 'a11y-widget' ),
                    'hint'        => __( 'Ajoutez vos outils pour limiter les stimuli visuels.', 'a11y-widget' ),
                    'aria_label'  => __( 'Exemple de réglage pour l’épilepsie', 'a11y-widget' ),
                    'placeholder' => true,
                ),
            ),
        ),
        array(
            'slug'     => 'audition',
            'title'    => __( 'Audition', 'a11y-widget' ),
            'children' => array(
                array(
                    'slug'        => 'audition-placeholder',
                    'label'       => __( 'Exemple : activer les sous-titres', 'a11y-widget' ),
                    'hint'        => __( 'Ajoutez vos options pour l’accessibilité audio.', 'a11y-widget' ),
                    'aria_label'  => __( 'Exemple de réglage pour l’audition', 'a11y-widget' ),
                    'placeholder' => true,
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
                        'slug'           => $slug,
                        'title'          => $title,
                        'children'       => array(),
                        'children_order' => array(),
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
                $sections[ $current_section ]['children_order'][] = $slug;
            }
        }
    }

    $ordered_sections = array();
    foreach ( $section_order as $slug ) {
        if ( ! isset( $sections[ $slug ] ) ) {
            continue;
        }

        $section = $sections[ $slug ];
        $ordered_children = array();
        if ( ! empty( $section['children_order'] ) ) {
            foreach ( $section['children_order'] as $child_slug ) {
                if ( isset( $section['children'][ $child_slug ] ) ) {
                    $ordered_children[] = $section['children'][ $child_slug ];
                }
            }
        }

        $section['children'] = $ordered_children;
        unset( $section['children_order'] );

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

        if ( ! isset( $sections_by_slug[ $slug ] ) ) {
            $sections_by_slug[ $slug ] = array(
                'slug'           => $slug,
                'title'          => isset( $section['title'] ) ? $section['title'] : '',
                'children'       => array(),
                'children_order' => array(),
            );
            $ordered_slugs[] = $slug;
        } elseif ( isset( $section['title'] ) && '' !== $section['title'] && '' === $sections_by_slug[ $slug ]['title'] ) {
            $sections_by_slug[ $slug ]['title'] = $section['title'];
        }

        $children = array();
        if ( isset( $section['children'] ) && is_array( $section['children'] ) ) {
            $children = $section['children'];
        }

        foreach ( $children as $child ) {
            if ( empty( $child['slug'] ) ) {
                continue;
            }

            $child_slug = sanitize_key( $child['slug'] );
            if ( '' === $child_slug ) {
                continue;
            }

            if ( isset( $sections_by_slug[ $slug ]['children'][ $child_slug ] ) ) {
                continue;
            }

            $child['slug'] = $child_slug;
            $sections_by_slug[ $slug ]['children'][ $child_slug ] = $child;
            $sections_by_slug[ $slug ]['children_order'][]        = $child_slug;
            $child_slug_global[ $child_slug ]                     = true;
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
                'slug'           => $slug,
                'title'          => isset( $section['title'] ) ? $section['title'] : '',
                'children'       => array(),
                'children_order' => array(),
            );
            $ordered_slugs[] = $slug;
        } elseif ( '' !== $section['title'] && '' === $sections_by_slug[ $slug ]['title'] ) {
            $sections_by_slug[ $slug ]['title'] = $section['title'];
        }

        if ( empty( $section['children'] ) ) {
            continue;
        }

        foreach ( $section['children'] as $child ) {
            if ( empty( $child['slug'] ) ) {
                continue;
            }

            $child_slug = sanitize_key( $child['slug'] );
            if ( '' === $child_slug ) {
                continue;
            }

            if ( isset( $child_slug_global[ $child_slug ] ) ) {
                continue;
            }

            if ( isset( $sections_by_slug[ $slug ]['children'][ $child_slug ] ) ) {
                continue;
            }

            $child['slug']                                   = $child_slug;
            $child_slug_global[ $child_slug ]                = true;
            $sections_by_slug[ $slug ]['children'][ $child_slug ] = $child;
            $sections_by_slug[ $slug ]['children_order'][]        = $child_slug;
        }
    }

    $sections = array();
    foreach ( $ordered_slugs as $slug ) {
        if ( ! isset( $sections_by_slug[ $slug ] ) ) {
            continue;
        }

        $section = $sections_by_slug[ $slug ];
        if ( ! isset( $section['title'] ) || ! is_string( $section['title'] ) ) {
            $section['title'] = '';
        }

        $ordered_children = array();
        if ( isset( $section['children_order'] ) && is_array( $section['children_order'] ) ) {
            foreach ( $section['children_order'] as $child_slug ) {
                if ( isset( $section['children'][ $child_slug ] ) ) {
                    $ordered_children[] = $section['children'][ $child_slug ];
                }
            }
        }

        $section['children'] = $ordered_children;
        unset( $section['children_order'] );

        $sections[] = $section;
    }

    $sections = a11y_widget_apply_custom_feature_layout( $sections );

    /**
     * Filter the final list of sections sent to the template.
     *
     * @param array $sections Sections with children.
     */
    return apply_filters( 'a11y_widget_sections', $sections );
}

/**
 * Apply the administrator-defined feature layout to sections.
 *
 * @param array $sections Sections with their features.
 *
 * @return array
 */
function a11y_widget_apply_custom_feature_layout( $sections ) {
    if ( empty( $sections ) || ! is_array( $sections ) ) {
        return array();
    }

    if ( ! function_exists( 'a11y_widget_get_feature_layout' ) ) {
        return $sections;
    }

    $layout = a11y_widget_get_feature_layout();

    if ( empty( $layout ) || ! is_array( $layout ) ) {
        return $sections;
    }

    $feature_map = array();

    foreach ( $sections as $section ) {
        if ( empty( $section['children'] ) || ! is_array( $section['children'] ) ) {
            continue;
        }

        foreach ( $section['children'] as $feature ) {
            if ( empty( $feature['slug'] ) ) {
                continue;
            }

            $feature_slug = sanitize_key( $feature['slug'] );

            if ( '' === $feature_slug ) {
                continue;
            }

            $feature['slug']          = $feature_slug;
            $feature_map[ $feature_slug ] = $feature;
        }
    }

    if ( empty( $feature_map ) ) {
        return $sections;
    }

    $assigned = array();

    foreach ( $sections as &$section ) {
        if ( empty( $section['slug'] ) ) {
            continue;
        }

        $section_slug = sanitize_title( $section['slug'] );

        if ( '' === $section_slug ) {
            continue;
        }

        $ordered_children = array();

        if ( isset( $layout[ $section_slug ] ) && is_array( $layout[ $section_slug ] ) ) {
            foreach ( $layout[ $section_slug ] as $child_slug ) {
                $child_slug = sanitize_key( $child_slug );

                if ( '' === $child_slug ) {
                    continue;
                }

                if ( isset( $assigned[ $child_slug ] ) || ! isset( $feature_map[ $child_slug ] ) ) {
                    continue;
                }

                $feature = $feature_map[ $child_slug ];
                $feature['slug'] = $child_slug;

                $ordered_children[]      = $feature;
                $assigned[ $child_slug ] = true;
            }
        }

        if ( isset( $section['children'] ) && is_array( $section['children'] ) ) {
            foreach ( $section['children'] as $feature ) {
                if ( empty( $feature['slug'] ) ) {
                    continue;
                }

                $child_slug = sanitize_key( $feature['slug'] );

                if ( '' === $child_slug || isset( $assigned[ $child_slug ] ) || ! isset( $feature_map[ $child_slug ] ) ) {
                    continue;
                }

                $feature['slug']         = $child_slug;
                $ordered_children[]      = $feature;
                $assigned[ $child_slug ] = true;
            }
        }

        $section['children'] = $ordered_children;
    }
    unset( $section );

    return $sections;
}

// Load admin settings and feature visibility management.
require_once A11Y_WIDGET_PATH . 'includes/admin-settings.php';

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
