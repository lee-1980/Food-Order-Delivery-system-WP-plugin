<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Batch_pl8app_Export Class
 *
 * @since  1.0.0
 */
class pl8app_Batch_pl8app_Export extends pl8app_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = 'menuitems';

	/**
	 * Set the CSV columns
	 *
	 * @since  1.0.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'ID'                       	=> __( 'ID', 'pl8app' ),
			'post_name'                	=> __( 'Slug', 'pl8app' ),
			'post_title'               	=> __( 'Name', 'pl8app' ),
			'post_date'                	=> __( 'Date Created', 'pl8app' ),
			'post_author'              	=> __( 'Author', 'pl8app' ),
			'post_content'             	=> __( 'Description', 'pl8app' ),
			'post_excerpt'             	=> __( 'Excerpt', 'pl8app' ),
			'post_status'              	=> __( 'Status', 'pl8app' ),
			'categories'               	=> __( 'Categories', 'pl8app' ),
			'addons'               		=> __( 'Options and Upgrades', 'pl8app' ),
			'pl8app_price' 				=> __( 'Price', 'pl8app' ),
			'_thumbnail_id'            	=> __( 'Featured Image', 'pl8app' ),
			'pl8app_sku' 				=> __( 'SKU', 'pl8app' ),
			'pl8app_product_notes' 		=> __( 'Notes', 'pl8app' ),
			'_pl8app_menuitem_sales' 	=> __( 'Sales', 'pl8app' ),
			'_pl8app_menuitem_earnings'	=> __( 'Earnings', 'pl8app' ),
            '_pl8app_product_type'      => __( 'Item Type', 'pl8app'),
            '_pl8app_bundled_products'  => __( 'Bundled Items', 'pl8app'),
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		$meta = array(
			'pl8app_price',
			'_thumbnail_id',
			'pl8app_sku',
			'pl8app_product_notes',
			'_pl8app_menuitem_sales',
			'_pl8app_menuitem_earnings',
            '_pl8app_product_type',
            '_pl8app_bundled_products'
		);

		$args = array(
			'post_type'      => 'menuitem',
			'posts_per_page' => 30,
			'paged'          => $this->step,
			'orderby'        => 'ID',
			'order'          => 'ASC'
		);

		$menuitems = new WP_Query( $args );

		if ( $menuitems->posts ) {
			foreach ( $menuitems->posts as $menuitem ) {

				$row = array();

				foreach( $this->csv_cols() as $key => $value ) {

					// Setup default value
					$row[ $key ] = '';

					if( in_array( $key, $meta ) ) {

						switch( $key ) {

							case '_thumbnail_id' :

								$image_id    = get_post_thumbnail_id( $menuitem->ID );
								$row[ $key ] = wp_get_attachment_url( $image_id );

								break;

							case 'pl8app_price' :

								if( pl8app_has_variable_prices( $menuitem->ID ) ) {

									$prices = array();
									foreach( pl8app_get_variable_prices( $menuitem->ID ) as $price ) {
										$prices[] = $price['name'] . ': ' . $price['amount'];
									}

									$row[ $key ] = implode( ' | ', $prices );

								} else {

									$row[ $key ] = pl8app_get_menuitem_price( $menuitem->ID );

								}

								break;

                            case '_pl8app_bundled_products' :

                                $bundled_menuitems = (array) get_post_meta( $menuitem->ID, '_pl8app_bundled_products', true );

                                if(!empty($bundled_menuitems)){
                                    $row[ $key ] = implode( ' | ', $bundled_menuitems );
                                }

                                break;

							default :

								$row[ $key ] = get_post_meta( $menuitem->ID, $key, true );

								break;

						}

					} else if( isset( $menuitem->$key ) ) {

						switch( $key ) {

							case 'post_author' :

								$row[ $key ] = get_the_author_meta( 'user_login', $menuitem->post_author );

								break;

							default :

								$row[ $key ] = $menuitem->$key;

								break;
						}

					} else if( 'categories' == $key ) {

						$terms = get_the_terms( $menuitem->ID, 'menu-category' );
						if( $terms ) {
							$terms = wp_list_pluck( $terms, 'name' );
							$row[ $key ] = implode( ' | ', $terms );
						}

					} else if( 'addons' == $key ) {

						$terms = get_the_terms( $menuitem->ID, 'addon_category' );
						if( $terms ) {
							$terms = wp_list_pluck( $terms, 'name' );
							$row[ $key ] = implode( ' | ', $terms );
						}

					}

				}

				$data[] = $row;

			}

			$data = apply_filters( 'pl8app_export_get_data', $data );
			$data = apply_filters( 'pl8app_export_get_data_' . $this->export_type, $data );

			return $data;
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_percentage_complete() {

		$args = array(
			'post_type'		   => 'menuitem',
			'posts_per_page'   => -1,
			'post_status'	   => 'any',
			'fields'           => 'ids',
		);

		$menuitems  = new WP_Query( $args );
		$total      = (int) $menuitems->post_count;
		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
}



/**
 * pla8pp_Batch_pl8app_category_Export Class
 *
 * @since  1.0.0
 */
class pla8pp_Batch_pl8app_category_Export extends pl8app_Batch_Export{

    /**
     * Our export type. Used for export-type specific filters/actions
     *
     * @var string
     * @since  1.0.0
     */
    public $export_type = 'categories';

    /**
     * Set the CSV columns
     *
     * @since  1.0.0
     * @return array $cols All the columns
     */
    public function csv_cols() {

        $cols = array(
            'term_id'                       	=> __( 'ID', 'pl8app' ),
            'term_taxonomy'                	=> __( 'Taxonomy', 'pl8app' ),
            'term_slug'               	=> __( 'Slug', 'pl8app' ),
            'term_parent'                	=> __( 'Parent', 'pl8app' ),
            'term_name'              	=> __( 'Name', 'pl8app' )
        );

        return $cols;
    }

    /**
     * Get the Export Data
     *
     * @since  1.0.0
     * @return array $data The data for the CSV file
     */
    public function get_data() {

        $data = array();

        $args = array(
            'number'        => 30,
            'offset'        => ((int) $this->step > 0)? ( (int) $this->step - 1) * 30: 1,
            'hide_empty' => false
        );

        $custom_terms = (array) get_terms( 'menu-category', $args );


        if( !empty($custom_terms) )
        {
            // put terms in order with no child going before its parent
            while ( $t = array_shift( $custom_terms ) ) {
                if ( $t->parent == 0 || isset( $terms[$t->parent] ) )
                    $terms[$t->term_id] = $t;
                else
                    $custom_terms[] = $t;
            }
        }

        if( !empty($terms)) {
            foreach ( $terms as $t ) {

                $row = array();

                foreach( $this->csv_cols() as $key => $value ) {

                    // Setup default value
                    $row[ $key ] = '';

                    switch ($key) {

                        case 'term_id' :
                            $row[$key] = $t->term_id;

                            break;
                        case 'term_taxonomy' :
                            $row[$key] = $t->taxonomy;

                            break;
                        case 'term_slug' :
                            $row[$key] = $t->slug;

                            break;
                        case 'term_parent' :
                            $row[$key] = $t->parent ? $terms[$t->parent]->slug : '';

                            break;
                        case 'term_name' :
                            $row[$key] = !empty( $t->name )?$t->name:'';

                            break;
                    }
                }

                $data[] = $row;

            }

            $data = apply_filters( 'pl8app_export_get_data', $data );
            $data = apply_filters( 'pl8app_export_get_data_' . $this->export_type, $data );

            return $data;
        }

        return false;

    }

    /**
     * Return the calculated completion percentage
     *
     * @since  1.0.0
     * @return int
     */
    public function get_percentage_complete() {

        $args = array(
            'hide_empty' => false,
            'get'        => 'all'
        );

        $total      = wp_count_terms( 'menu-category', $args);
        $percentage = 100;

        if( $total > 0 ) {
            $percentage = ( ( 30 * $this->step ) / $total ) * 100;
        }

        if( $percentage > 100 ) {
            $percentage = 100;
        }

        return $percentage;
    }


}

/**
 * pla8pp_Batch_pl8app_addon_Export Class
 *
 * @since  1.0.0
 */
class pla8pp_Batch_pl8app_addon_Export extends pl8app_Batch_Export{

    /**
     * Our export type. Used for export-type specific filters/actions
     *
     * @var string
     * @since  1.0.0
     */
    public $export_type = 'options_upgrades';

    /**
     * Set the CSV columns
     *
     * @since  1.0.0
     * @return array $cols All the columns
     */
    public function csv_cols() {

        $cols = array(
            'term_id'                   => __( 'ID', 'pl8app' ),
            'term_taxonomy'             => __( 'Taxonomy', 'pl8app' ),
            'term_slug'               	=> __( 'Slug', 'pl8app' ),
            'term_parent'               => __( 'Parent', 'pl8app' ),
            'term_name'              	=> __( 'Name', 'pl8app' ),
            'use_it_like'               => __( 'Selection Type', 'pl8app'),
            'price'                     => __( 'Price', 'pl8app'),
        );

        return $cols;
    }

    /**
     * Get the Export Data
     *
     * @since  1.0.0
     * @return array $data The data for the CSV file
     */
    public function get_data() {

        $data = array();

        $args = array(
            'number'        => 30,
            'offset'        => ((int) $this->step > 0)? ( (int) $this->step - 1) * 30: 1,
            'hide_empty' => false,
        );

        $custom_terms = (array) get_terms( 'addon_category', $args );


        if( !empty($custom_terms) )
        {
            // put terms in order with no child going before its parent
            while ( $t = array_shift( $custom_terms ) ) {
                if ( $t->parent == 0 || isset( $terms[$t->parent] ) )
                    $terms[$t->term_id] = $t;
                else
                    $custom_terms[] = $t;
            }
        }

        if( !empty($terms)) {
            foreach ( $terms as $t ) {

                $row = array();

                foreach( $this->csv_cols() as $key => $value ) {

                    // Setup default value
                    $row[ $key ] = '';

                    switch ($key) {

                        case 'term_id' :
                            $row[$key] = $t->term_id;

                            break;
                        case 'term_taxonomy' :
                            $row[$key] = $t->taxonomy;

                            break;
                        case 'term_slug' :
                            $row[$key] = $t->slug;

                            break;
                        case 'term_parent' :
                            $row[$key] = $t->parent ? $terms[$t->parent]->slug : '';

                            break;
                        case 'term_name' :
                            $row[$key] = !empty( $t->name )?$t->name:'';

                            break;
                    }
                }

                $term_meta = get_option( "taxonomy_term_$t->term_id" );

                if( $t->parent == 0 ) {
                    $use_addon_like =  isset($term_meta['use_it_like']) ? $term_meta['use_it_like'] : 'checkbox';
                    $row['use_it_like'] = $use_addon_like == 'checkbox' ? 'Multiple' : 'Single';
                } else {
                    $row['price'] = !empty( $term_meta['price'] ) ? $term_meta['price'] : '0';
                }

                $data[] = $row;

            }

            $data = apply_filters( 'pl8app_export_get_data', $data );
            $data = apply_filters( 'pl8app_export_get_data_' . $this->export_type, $data );

            return $data;
        }

        return false;

    }

    /**
     * Return the calculated completion percentage
     *
     * @since  1.0.0
     * @return int
     */
    public function get_percentage_complete() {

        $args = array(
            'hide_empty' => false,
            'get'        => 'all'
        );

        $total      = wp_count_terms( 'addon_category', $args);
        $percentage = 100;

        if( $total > 0 ) {
            $percentage = ( ( 30 * $this->step ) / $total ) * 100;
        }

        if( $percentage > 100 ) {
            $percentage = 100;
        }

        return $percentage;
    }


}