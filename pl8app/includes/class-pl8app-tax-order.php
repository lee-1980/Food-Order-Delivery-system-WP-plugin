<?php

// Only load class if it hasn't already been loaded.
if ( ! class_exists( 'Yikes_Custom_Taxonomy_Order' ) ) {

    /**
     * Class Yikes_Custom_Taxonomy_Order.
     */
    class Yikes_Custom_Taxonomy_Order {

        /**
         * Unique instance of this plugin.
         *
         * @var Yikes_Custom_Taxonomy_Order
         */
        private static $instance;

        /**
         * Return an instance of our plugin.
         *
         * @return Yikes_Custom_Taxonomy_Order
         */
        public static function get_instance() {

            if ( null === self::$instance ) {

                self::$instance = new self();

            }

            return self::$instance;

        }

        /**
         * Main Constructor.
         */
        public function __construct() {

            // Hooks.
            add_action( 'admin_head', array( $this, 'admin_order_terms' ) );
            add_action( 'init', array( $this, 'front_end_order_terms' ) );
            add_action( 'wp_ajax_yikes_sto_update_taxonomy_order', array( $this, 'update_taxonomy_order' ) );
        }

        /**
         * Order the terms on the admin side.
         */
        public function admin_order_terms() {
            $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : '';


            if ( ! isset( $_GET['orderby'] ) && ! empty( $screen ) && ! empty( $screen->base ) && $screen->base === 'edit-tags' && 'menu-category' == $screen->taxonomy ) {
                $this->enqueue();
                $this->default_term_order( $screen->taxonomy );
                add_filter( 'terms_clauses', array( $this, 'set_tax_order' ), 10, 3 );
            }
        }


        /**
         * Order the taxonomies on the front end.
         */
        public function front_end_order_terms() {
            if ( ! is_admin() && apply_filters( 'yikes_simple_taxonomy_ordering_front_end_order_terms', true ) ) {
                add_filter( 'terms_clauses', array( $this, 'set_tax_order' ), 10, 3 );
            }
        }

        /**
         * Enqueue assets.
         */
        public function enqueue() {
            $tax = function_exists( 'get_current_screen' ) ? get_current_screen()->taxonomy : '';
            wp_enqueue_style( 'yikes-tax-drag-drop-styles', PL8_PLUGIN_URL . "assets/css/yikes-tax-drag-drop.min.css", array(), PL8_VERSION, 'all' );
            wp_enqueue_script( 'select2', PL8_PLUGIN_URL . 'assets/js/select2/select2.min.js', array( 'jquery' ), PL8_VERSION, true );
            wp_enqueue_script( 'yikes-tax-drag-drop', PL8_PLUGIN_URL . "assets/js/admin/yikes-tax-drag-drop.min.js", array( 'jquery-ui-core', 'jquery-ui-sortable' ), PL8_VERSION, true );
            wp_localize_script(
                'yikes-tax-drag-drop',
                'simple_taxonomy_ordering_data',
                array(
                    'preloader_url'    => esc_url( admin_url( 'images/wpspin_light.gif' ) ),
                    'term_order_nonce' => wp_create_nonce( 'term_order_nonce' ),
                    'paged'            => isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 0,
                    'per_page_id'      => "edit_{$tax}_per_page",
                )
            );
        }

        /**
         * Default the taxonomy's terms' order if it's not set.
         *
         * @param string $tax_slug The taxonomy's slug.
         */
        public function default_term_order( $tax_slug ) {
            $terms = get_terms( $tax_slug, array( 'hide_empty' => false ) );
            $order = $this->get_max_taxonomy_order( $tax_slug );
            foreach ( $terms as $term ) {
                if ( ! get_term_meta( $term->term_id, 'tax_position', true ) ) {
                    update_term_meta( $term->term_id, 'tax_position', $order );
                    $order++;
                }
            }
        }

        /**
         * Get the maximum tax_position for this taxonomy. This will be applied to terms that don't have a tax position.
         */
        private function get_max_taxonomy_order( $tax_slug ) {
            global $wpdb;
            $max_term_order = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT MAX( CAST( tm.meta_value AS UNSIGNED ) )
					FROM $wpdb->terms t
					JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id AND tt.taxonomy = '%s'
					JOIN $wpdb->termmeta tm ON tm.term_id = t.term_id WHERE tm.meta_key = 'tax_position'",
                    $tax_slug
                )
            );
            $max_term_order = is_array( $max_term_order ) ? current( $max_term_order ) : 0;
            return (int) $max_term_order === 0 || empty( $max_term_order ) ? 1 : (int) $max_term_order + 1;
        }

        /**
         * Re-Order the taxonomies based on the tax_position value.
         *
         * @param array $pieces     Array of SQL query clauses.
         * @param array $taxonomies Array of taxonomy names.
         * @param array $args       Array of term query args.
         */
        public function set_tax_order( $pieces, $taxonomies, $args ) {
            foreach ( $taxonomies as $taxonomy ) {
                if ( $taxonomy == 'menu-category') {
                    global $wpdb;

                    $join_statement = " LEFT JOIN $wpdb->termmeta AS term_meta ON t.term_id = term_meta.term_id AND term_meta.meta_key = 'tax_position'";

                    if ( ! $this->does_substring_exist( $pieces['join'], $join_statement ) ) {
                        $pieces['join'] .= $join_statement;
                    }
                    $pieces['orderby'] = 'ORDER BY CAST( term_meta.meta_value AS UNSIGNED )';
                }
            }
            return $pieces;
        }

        /**
         * Check if a substring exists inside a string.
         *
         * @param string $string    The main string (haystack) we're searching in.
         * @param string $substring The substring we're searching for.
         *
         * @return bool True if substring exists, else false.
         */
        protected function does_substring_exist( $string, $substring ) {
            return strstr( $string, $substring ) !== false;
        }

        /**
         * AJAX Handler to update terms' tax position.
         */
        public function update_taxonomy_order() {
            if ( ! check_ajax_referer( 'term_order_nonce', 'term_order_nonce', false ) ) {
                wp_send_json_error();
            }

            $taxonomy_ordering_data = filter_var_array( wp_unslash( $_POST['taxonomy_ordering_data'] ), FILTER_SANITIZE_NUMBER_INT );
            $base_index             = filter_var( wp_unslash( $_POST['base_index'] ), FILTER_SANITIZE_NUMBER_INT ) ;
            foreach ( $taxonomy_ordering_data as $order_data ) {

                // Due to the way WordPress shows parent categories on multiple pages, we need to check if the parent category's position should be updated.
                // If the category's current position is less than the base index (i.e. the category shouldn't be on this page), then don't update it.
                if ( $base_index > 0 ) {
                    $current_position = get_term_meta( $order_data['term_id'], 'tax_position', true );
                    if ( (int) $current_position < (int) $base_index ) {
                        continue;
                    }
                }

                update_term_meta( $order_data['term_id'], 'tax_position', ( (int) $order_data['order'] + (int) $base_index ) );
            }

            do_action( 'yikes_sto_taxonomy_order_updated', $taxonomy_ordering_data, $base_index );

            wp_send_json_success();
        }


    }
}

$yikes_custom_taxonomy_order = Yikes_Custom_Taxonomy_Order::get_instance();

