<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Batch_MenuItems_Import Class
 *
 * @since 1.0.0
 */
class pl8app_Batch_MenuItems_Import extends pl8app_Batch_Import {

	/**
	 * Set up our import config.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {

		// Set up default field map values
		$this->field_mapping = array(
		    'post_id'        => '',
			'post_title'     => '',
			'post_name'      => '',
			'post_status'    => 'draft',
			'post_author'    => '',
			'post_date'      => '',
			'post_content'   => '',
			'post_excerpt'   => '',
			'price'          => '',
			'categories'     => '',
			'addons'     	 => '',
			'sku'            => '',
			'earnings'       => '',
			'sales'          => '',
			'featured_image' => '',
			'notes'          => '',
            'product_type'   => '',
            'bundled_products'  => '',
		);
	}

	/**
	 * Process a step
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function process_step() {

		$more = false;

		if ( ! $this->can_import() ) {
			wp_die( __( 'You do not have permission to import data.', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
		}

		$i      = 1;
		$offset = $this->step > 1 ? ( $this->per_step * ( $this->step - 1 ) ) : 0;

		if( $offset > $this->total ) {
			$this->done = true;
		}

		if( ! $this->done && $this->csv->data ) {

			$more = true;
			$bundle_products = array();
			$bundled_sub_products = array();

			foreach( $this->csv->data as $key => $row ) {

				// Skip all rows until we pass our offset
				if( $key + 1 <= $offset ) {
					continue;
				}

				// Done with this batch
				if( $i > $this->per_step ) {
					break;
				}

				// Import pl8app
				$args = array(
					'post_type'    => 'menuitem',
					'post_title'   => '',
					'post_name'    => '',
					'post_status'  => '',
					'post_author'  => '',
					'post_date'    => '',
					'post_content' => '',
					'post_excerpt' => ''
				);

				foreach ( $args as $key => $field ) {
					if ( ! empty( $this->field_mapping[ $key ] ) && ! empty( $row[ $this->field_mapping[ $key ] ] ) ) {
						$args[ $key ] = $row[ $this->field_mapping[ $key ] ];
					}
				}

				if ( empty( $args['post_author'] ) ) {
	 				$user = wp_get_current_user();
	 				$args['post_author'] = $user->ID;
	 			} else {

	 				// Check all forms of possible user inputs, email, ID, login.
	 				if ( is_email( $args['post_author'] ) ) {
	 					$user = get_user_by( 'email', $args['post_author'] );
	 				} elseif ( is_numeric( $args['post_author'] ) ) {
	 					$user = get_user_by( 'ID', $args['post_author'] );
	 				} else {
	 					$user = get_user_by( 'login', $args['post_author'] );
	 				}

	 				// If we don't find one, resort to the logged in user.
	 				if ( false === $user ) {
	 					$user = wp_get_current_user();
	 				}

	 				$args['post_author'] = $user->ID;
	 			}

				// Format the date properly
				if ( ! empty( $args['post_date'] ) ) {

					$timestamp = strtotime( $args['post_date'], current_time( 'timestamp' ) );
					if( $timestamp == false ) {
						$date = date_i18n( 'Y-m-d H:i:s' );
					} else {
						$date = date( 'Y-m-d H:i:s', $timestamp );
					}

					// If the date provided results in a date string, use it, or just default to today so it imports
					if ( ! empty( $date ) ) {
						$args['post_date'] = $date;
					} else {
						$date = '';
					}
				}

				// Detect any status that could map to `publish`
				if ( ! empty( $args['post_status'] ) ) {

					$published_statuses = array(
						'live',
						'published',
					);

					$current_status = strtolower( $args['post_status'] );

					if ( in_array( $current_status, $published_statuses ) ) {
						$args['post_status'] = 'publish';
					}

				}

				$menuitem_id = wp_insert_post( $args );

				//Old Product Id
                if( ! empty( $this->field_mapping['post_id'] ) && ! empty( $row[ $this->field_mapping['post_id'] ] ) ) {
                    $bundled_sub_products[$row[ $this->field_mapping['post_id'] ]] = $menuitem_id;
                }

				// setup categories
				if( ! empty( $this->field_mapping['categories'] ) && ! empty( $row[ $this->field_mapping['categories'] ] ) ) {

					$categories = $this->str_to_array( $row[ $this->field_mapping['categories'] ] );

					$this->set_taxonomy_terms( $menuitem_id, $categories, 'menu-category' );

				}

				// setup addons
				if( ! empty( $this->field_mapping['addons'] ) && ! empty( $row[ $this->field_mapping['addons'] ] ) ) {

					$addons = $this->str_to_array( $row[ $this->field_mapping['addons'] ] );

					$this->set_taxonomy_terms( $menuitem_id, $addons, 'addon_category' );

				}

				// setup price(s)
				if( ! empty( $this->field_mapping['price'] ) && ! empty( $row[ $this->field_mapping['price'] ] ) ) {

					$price = $row[ $this->field_mapping['price'] ];

					$this->set_price( $menuitem_id, $price );

				}

				// Product Image
				if( ! empty( $this->field_mapping['featured_image'] ) && ! empty( $row[ $this->field_mapping['featured_image'] ] ) ) {

					$image = sanitize_text_field( $row[ $this->field_mapping['featured_image'] ] );

					$this->set_image( $menuitem_id, $image, $args['post_author'] );

				}

				// Sale count
				if( ! empty( $this->field_mapping['sales'] ) && ! empty( $row[ $this->field_mapping['sales'] ] ) ) {

					update_post_meta( $menuitem_id, '_pl8app_menuitem_sales', absint( $row[ $this->field_mapping['sales'] ] ) );
				}

                // Product Type
                if( ! empty( $this->field_mapping['product_type'] ) && ! empty( $row[ $this->field_mapping['product_type'] ] ) ) {

                    update_post_meta( $menuitem_id, '_pl8app_product_type', sanitize_text_field( $row[ $this->field_mapping['product_type'] ] )  );
                    $bundle_products[$menuitem_id] = array();
                }

                // Bundled Products
                if( ! empty( $this->field_mapping['bundled_products'] ) && ! empty( $row[ $this->field_mapping['bundled_products'] ] ) ) {

                    $bundled_products = $this->str_to_array( $row[ $this->field_mapping['bundled_products'] ] );
                    isset($bundle_products[$menuitem_id])? $bundle_products[$menuitem_id] = $bundled_products: '';

                }

				// Earnings
				if( ! empty( $this->field_mapping['earnings'] ) && ! empty( $row[ $this->field_mapping['earnings'] ] ) ) {

					update_post_meta( $menuitem_id, '_pl8app_menuitem_earnings', pl8app_sanitize_amount( $row[ $this->field_mapping['earnings'] ] ) );
				}

				// Notes
				if( ! empty( $this->field_mapping['notes'] ) && ! empty( $row[ $this->field_mapping['notes'] ] ) ) {

					update_post_meta( $menuitem_id, 'pl8app_product_notes', sanitize_text_field( $row[ $this->field_mapping['notes'] ] ) );
				}

				// SKU
				if( ! empty( $this->field_mapping[ 'sku' ] ) && ! empty( $row[ $this->field_mapping[ 'sku' ] ] ) ) {

					update_post_meta( $menuitem_id, 'pl8app_sku', sanitize_text_field( $row[ $this->field_mapping['sku'] ] ) );
				}

				// Custom fields
				// Code goes here

				$i++;
			}

			if(!empty($bundle_products) && is_array($bundle_products)){
			    foreach($bundle_products as $bundleitem_id => $bundled_items){
			        if(!empty($bundled_items) && is_array($bundled_items)){

			            $new_bundled_items = $bundled_items;

			            //replace old bundled items Id with new bundled Items ID
			            foreach($bundled_items as $key => $bundled_item){
			                if(isset($bundled_sub_products[$bundled_item])) $new_bundled_items[$key] = $bundled_sub_products[$bundled_item];
                        }

                        //Update bundle item's sub bundled items ID meta_value

                        update_post_meta( $bundleitem_id, '_pl8app_bundled_products', $new_bundled_items);

                    }
                }
            }
		}

		return $more;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_percentage_complete() {

		if( $this->total > 0 ) {
			$percentage = ( $this->step * $this->per_step / $this->total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set up and store the price for the menuitem
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function set_price( $menuitem_id = 0, $price = '' ) {

		if( is_numeric( $price ) ) {

			update_post_meta( $menuitem_id, 'pl8app_price', pl8app_sanitize_amount( $price ) );

		} else {

			$prices = $this->str_to_array( $price );

			if( ! empty( $prices ) ) {

				$variable_prices = array();
				$price_id        = 1;
				foreach( $prices as $price ) {

					// See if this matches the pl8app pl8app export for variable prices
					if( false !== strpos( $price, ':' ) ) {

						$price = array_map( 'trim', explode( ':', $price ) );

						$variable_prices[ $price_id ] = array( 'name' => $price[ 0 ], 'amount' => $price[ 1 ] );
						$price_id++;

					}

				}

				update_post_meta( $menuitem_id, '_variable_pricing', 1 );
				update_post_meta( $menuitem_id, 'pl8app_variable_prices', $variable_prices );

			}

		}

	}

	/**
	 * Set up and store the file menuitems
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function set_files( $menuitem_id = 0, $files = array() ) {

		if( ! empty( $files ) ) {

			$menuitem_files = array();
			$file_id        = 1;
			foreach( $files as $file ) {

				$condition = '';

				if ( false !== strpos( $file, ';' ) ) {

					$split_on  = strpos( $file, ';' );
					$file_url  = substr( $file, 0, $split_on );
					$condition = substr( $file, $split_on + 1 );

				} else {

					$file_url = $file;

				}

				$menuitem_file_args = array(
					'file' => $file_url,
					'name' => basename( $file_url ),
				);

				if ( ! empty( $condition ) ) {
					$menuitem_file_args['condition'] = $condition;
				}

				$menuitem_files[ $file_id ] = $menuitem_file_args;
				$file_id++;

			}

			update_post_meta( $menuitem_id, 'pl8app_menuitem_files', $menuitem_files );

		}

	}

	/**
	 * Set up and store the Featured Image
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function set_image( $menuitem_id = 0, $image = '', $post_author = 0 ) {

		$is_url   = false !== filter_var( $image, FILTER_VALIDATE_URL );
		$is_local = $is_url && false !== strpos( site_url(), $image );
		$ext      = pl8app_get_file_extension( $image );

		if( $is_url && $is_local ) {

			// Image given by URL, see if we have an attachment already
			$attachment_id = attachment_url_to_postid( $image );

		} elseif( $is_url ) {

			if( ! function_exists( 'media_sideload_image' ) ) {

				require_once( ABSPATH . 'wp-admin/includes/file.php' );

			}

			// Image given by external URL
			$url = media_sideload_image( $image, $menuitem_id, '', 'src' );

			if( ! is_wp_error( $url ) ) {

				$attachment_id = attachment_url_to_postid( $url );

			}


		} elseif( false === strpos( $image, '/' ) && pl8app_get_file_extension( $image ) ) {

			// Image given by name only

			$upload_dir = wp_upload_dir();

			if( file_exists( trailingslashit( $upload_dir['path'] ) . $image ) ) {

				// Look in current upload directory first
				$file = trailingslashit( $upload_dir['path'] ) . $image;

			} else {

				// Now look through year/month sub folders of upload directory for files with our image's same extension
				$files = glob( $upload_dir['basedir'] . '/*/*/*{' . $ext . '}', GLOB_BRACE );
				foreach( $files as $file ) {

					if( basename( $file ) == $image ) {

						// Found our file
						break;

					}

					// Make sure $file is unset so our empty check below does not return a false positive
					unset( $file );

				}

			}

			if( ! empty( $file ) ) {

				// We found the file, let's see if it already exists in the media library

				$guid          = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $file );
				$attachment_id = attachment_url_to_postid( $guid );


				if( empty( $attachment_id ) ) {

					// Doesn't exist in the media library, let's add it

					$filetype = wp_check_filetype( basename( $file ), null );

					// Prepare an array of post data for the attachment.
					$attachment = array(
						'guid'           => $guid,
						'post_mime_type' => $filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', $image ),
						'post_content'   => '',
						'post_status'    => 'inherit',
						'post_author'    => $post_author
					);

					// Insert the attachment.
					$attachment_id = wp_insert_attachment( $attachment, $file, $menuitem_id );

					// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
					require_once( ABSPATH . 'wp-admin/includes/image.php' );

					// Generate the metadata for the attachment, and update the database record.
					$attach_data = wp_generate_attachment_metadata( $attachment_id, $file );
					wp_update_attachment_metadata( $attachment_id, $attach_data );

				}

			}

		}

		if( ! empty( $attachment_id ) ) {

			return set_post_thumbnail( $menuitem_id, $attachment_id );

		}

		return false;

	}

	/**
	 * Set up and taxonomy terms
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function set_taxonomy_terms( $menuitem_id = 0, $terms = array(), $taxonomy = 'menu-category' ) {

		$terms = $this->maybe_create_terms( $terms, $taxonomy );

		if( ! empty( $terms ) ) {
			wp_set_object_terms( $menuitem_id, $terms, $taxonomy );
		}

		if( ! empty( $terms ) && 'addon_category' == $taxonomy ) {

			$all_addons = get_terms( array(
				'taxonomy' 	=> 'addon_category',
				'include'	=> $terms
			) );

			$addon_items = array();

			if( ! is_wp_error( $all_addons ) ) {

				foreach ( $all_addons as $addon ) {
					if( $addon->parent != 0 )
						continue;

					$addon_items[$addon->term_id] = array(
						'category' => $addon->term_id,
						'items' => []
					);
				}

				foreach ( $all_addons as $addon ) {
					if( $addon->parent == 0 )
						continue;

					$addon_items[$addon->parent]['items'][] = $addon->term_id;
				}
			}

			if( ! empty( $addon_items ) ) {
				update_post_meta( $menuitem_id, '_addon_items', $addon_items );
			}
		}
	}

	/**
	 * Locate term IDs or create terms if none are found
	 *
	 * @since 1.0.0
	 * @return array
	 */
	private function maybe_create_terms( $terms = array(), $taxonomy = 'menu-category' ) {

		// Return of term IDs
		$term_ids = array();

		foreach( $terms as $term ) {

			if( is_numeric( $term ) && 0 === (int) $term ) {

				$t = get_term( $term, $taxonomy );

			} else {

				$t = get_term_by( 'name', $term, $taxonomy );

				if( ! $t ) {

					$t = get_term_by( 'slug', $term, $taxonomy );

				}

			}

			if( ! empty( $t ) ) {

				$term_ids[] = $t->term_id;

			} else {

				$term_data = wp_insert_term( $term, $taxonomy, array( 'slug' => sanitize_title( $term ) ) );

				if( ! is_wp_error( $term_data ) ) {

					$term_ids[] = $term_data['term_id'];

				}

			}

		}

		return array_map( 'absint', $term_ids );
	}

	/**
	 * Retrieve URL to pl8app list table
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_list_table_url() {
		return admin_url( 'edit.php?post_type=menuitem' );
	}

	/**
	 * Retrieve pl8app label
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_import_type_label() {
		return pl8app_get_label_plural( true );
	}

}



class pl8app_Batch_Categories_Import extends pl8app_Batch_Import {

    /**
     * Set up our import config.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {

        // Set up default field map values
        $this->field_mapping = array(
            'term_taxonomy'        =>'',
            'term_slug'            => '',
            'term_parent'               => '',
            'term_name'                 => '',
        );

        $this->per_step  = $this->total;
    }

    /**
     * Process a step
     *
     * @since 1.0.0
     * @return bool
     */
    public function process_step() {

        $more = false;

        if ( ! $this->can_import() ) {
            wp_die( __( 'You do not have permission to import data.', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
        }

        if( $this->csv->data ) {

            // put terms in order with no child going before its parent
            $custom_terms = $this->csv->data;
            $terms = array();
            $taxonomy = 'menu-category';

            // put terms in order with no child going before its parent
            foreach ( $custom_terms as $key => $row ) {

                $term_parent = ! empty( $this->field_mapping['term_parent'] ) && ! empty( $row[ $this->field_mapping['term_parent'] ] )?$row[ $this->field_mapping['term_parent'] ] :'';
                if ( empty($term_parent) ){
                    $term_taxonomy = ! empty( $this->field_mapping['term_taxonomy'] ) && ! empty( $row[ $this->field_mapping['term_taxonomy'] ] )?$row[ $this->field_mapping['term_taxonomy'] ] :'';

                    if($taxonomy !== trim($term_taxonomy)){
                        wp_send_json_error( array(
                            'error' => __( 'This taxonomy data is not for Category', 'pl8app' )
                        ) );
                    }

                    $term_name = ! empty( $this->field_mapping['term_name'] ) && ! empty( $row[ $this->field_mapping['term_name'] ] )?$row[ $this->field_mapping['term_name'] ] :'';
                    $term_slug = ! empty( $this->field_mapping['term_slug'] ) && ! empty( $row[ $this->field_mapping['term_slug'] ] )?$row[ $this->field_mapping['term_slug'] ] :'';

                    $t = get_term_by( 'name', $term_name, $taxonomy );

                    if( ! $t ) {

                        $t = get_term_by( 'slug', $term_slug, $taxonomy );

                    }

                    if( empty( $t ) ) {
                        $term_data =  wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug ) );
                        if (!is_wp_error($term_data)) {
                            $terms[$term_slug] = $term_data['term_id'];
                        }
                    }
                    else{
                        $terms[$term_slug] = $t->term_id;
                    }

                }
                else {
                    continue;
                }
            }

            foreach ( $custom_terms as $key => $row ) {
                $term_parent = ! empty( $this->field_mapping['term_parent'] ) && ! empty( $row[ $this->field_mapping['term_parent'] ] )?$row[ $this->field_mapping['term_parent'] ] :'';
                if(!empty($term_parent)){
                    $term_taxonomy = ! empty( $this->field_mapping['term_taxonomy'] ) && ! empty( $row[ $this->field_mapping['term_taxonomy'] ] )?$row[ $this->field_mapping['term_taxonomy'] ] :'';

                    if($taxonomy !== trim($term_taxonomy)){
                        wp_send_json_error( array(
                            'error' => __( 'This taxonomy data is not for Category', 'pl8app' )
                        ) );
                    }

                    $term_name = ! empty( $this->field_mapping['term_name'] ) && ! empty( $row[ $this->field_mapping['term_name'] ] )?$row[ $this->field_mapping['term_name'] ] :'';
                    $term_slug = ! empty( $this->field_mapping['term_slug'] ) && ! empty( $row[ $this->field_mapping['term_slug'] ] )?$row[ $this->field_mapping['term_slug'] ] :'';

                    $t = get_term_by( 'name', $term_name, $taxonomy );

                    if( ! $t ) {

                        $t = get_term_by( 'slug', $term_slug, $taxonomy );

                    }

                    if( empty( $t ) ) {
                        wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug , 'parent' => isset($terms[$term_parent])?$terms[$term_parent]: 0) );
                    }

                }
            }

            $this->done = true;

        }

        return $more;
    }

    /**
     * Return the calculated completion percentage
     *
     * @since 1.0.0
     * @return int
     */
    public function get_percentage_complete() {

        if( $this->total > 0 ) {
            $percentage = ( $this->step * $this->per_step / $this->total ) * 100;
        }

        if( $percentage > 100 ) {
            $percentage = 100;
        }

        return $percentage;
    }
    /**
     * Retrieve URL to pl8app list table
     *
     * @since 1.0.0
     * @return string
     */
    public function get_list_table_url() {
        return admin_url( 'edit.php?taxonomy=menu-category&post_type=menuitem' );
    }

    /**
     * Retrieve pl8app label
     *
     * @since 1.0.0
     * @return void
     */
    public function get_import_type_label() {
        return __('Categories','pl8app');
    }
}

class pl8app_Batch_Addons_Import extends pl8app_Batch_Import {

    /**
     * Set up our import config.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {

        // Set up default field map values
        $this->field_mapping = array(
            'term_taxonomy'        =>'',
            'term_slug'            => '',
            'term_parent'               => '',
            'term_name'            => '',
            'use_it_like'          => '',
            'price'                => ''
        );

        $this->per_step  = $this->total;
    }

    /**
     * Process a step
     *
     * @since 1.0.0
     * @return bool
     */
    public function process_step() {

        $more = false;

        if ( ! $this->can_import() ) {
            wp_die( __( 'You do not have permission to import data.', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
        }

        if( $this->csv->data ) {

            // put terms in order with no child going before its parent
            $custom_terms = $this->csv->data;
            $terms = array();
            $taxonomy = 'addon_category';

            // put terms in order with no child going before its parent
            foreach ( $custom_terms as $key => $row ) {

                $term_parent = ! empty( $this->field_mapping['term_parent'] ) && ! empty( $row[ $this->field_mapping['term_parent'] ] )?$row[ $this->field_mapping['term_parent'] ] :'';
                if ( empty($term_parent) ){
                    $term_taxonomy = ! empty( $this->field_mapping['term_taxonomy'] ) && ! empty( $row[ $this->field_mapping['term_taxonomy'] ] )?$row[ $this->field_mapping['term_taxonomy'] ] :'';

                    if($taxonomy !== trim($term_taxonomy)){
                        wp_send_json_error( array(
                            'error' => __( 'This taxonomy data is not for Option and Upgrade', 'pl8app' )
                        ) );
                    }

                    $term_name = ! empty( $this->field_mapping['term_name'] ) && ! empty( $row[ $this->field_mapping['term_name'] ] )?$row[ $this->field_mapping['term_name'] ] :'';
                    $term_slug = ! empty( $this->field_mapping['term_slug'] ) && ! empty( $row[ $this->field_mapping['term_slug'] ] )?$row[ $this->field_mapping['term_slug'] ] :'';
                    $use_it_like = ! empty( $this->field_mapping['use_it_like'] ) && ! empty( $row[ $this->field_mapping['use_it_like'] ] )?$row[ $this->field_mapping['use_it_like'] ] :'';


                    $t = get_term_by( 'name', $term_name, $taxonomy );

                    if( ! $t ) {

                        $t = get_term_by( 'slug', $term_slug, $taxonomy );

                    }

                    if( empty( $t ) ) {
                        $term_data =  wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug ) );
                        if (!is_wp_error($term_data)) {
                            $terms[$term_slug] = $term_data['term_id'];
                            update_option("taxonomy_term_".$term_data['term_id'], array('use_it_like' => $use_it_like));
                            update_term_meta($term_data['term_id'], '_type', $use_it_like);
                        }
                    }
                    else{
                        $terms[$term_slug] = $t->term_id;
                    }

                }
                else {
                    continue;
                }
            }

            foreach ( $custom_terms as $key => $row ) {
                $term_parent = ! empty( $this->field_mapping['term_parent'] ) && ! empty( $row[ $this->field_mapping['term_parent'] ] )?$row[ $this->field_mapping['term_parent'] ] :'';
                if(!empty($term_parent)){
                    $term_taxonomy = ! empty( $this->field_mapping['term_taxonomy'] ) && ! empty( $row[ $this->field_mapping['term_taxonomy'] ] )?$row[ $this->field_mapping['term_taxonomy'] ] :'';

                    if($taxonomy !== trim($term_taxonomy)){
                        wp_send_json_error( array(
                            'error' => __( 'This taxonomy data is not for Option and Upgrade', 'pl8app' )
                        ) );
                    }

                    $term_name = ! empty( $this->field_mapping['term_name'] ) && ! empty( $row[ $this->field_mapping['term_name'] ] )?$row[ $this->field_mapping['term_name'] ] :'';
                    $term_slug = ! empty( $this->field_mapping['term_slug'] ) && ! empty( $row[ $this->field_mapping['term_slug'] ] )?$row[ $this->field_mapping['term_slug'] ] :'';
                    $price = ! empty( $this->field_mapping['price'] ) && ! empty( $row[ $this->field_mapping['price'] ] )?$row[ $this->field_mapping['price'] ] :'';

                    $t = get_term_by( 'name', $term_name, $taxonomy );

                    if( ! $t ) {

                        $t = get_term_by( 'slug', $term_slug, $taxonomy );

                    }

                    if( empty( $t ) ) {
                        $term_data = wp_insert_term( $term_name, $taxonomy, array( 'slug' => $term_slug , 'parent' => isset($terms[$term_parent])?$terms[$term_parent]: 0) );

                        if (!is_wp_error($term_data)) {
                            update_term_meta($term_data['term_id'], '_price', (float) $price);
                            update_option("taxonomy_term_".$term_data['term_id'], array('price' => (float) $price));
                        }
                    }

                }
            }

            $this->done = true;

        }

        return $more;
    }

    /**
     * Return the calculated completion percentage
     *
     * @since 1.0.0
     * @return int
     */
    public function get_percentage_complete() {

        if( $this->total > 0 ) {
            $percentage = ( $this->step * $this->per_step / $this->total ) * 100;
        }

        if( $percentage > 100 ) {
            $percentage = 100;
        }

        return $percentage;
    }

    /**
     * Retrieve URL to pl8app list table
     *
     * @since 1.0.0
     * @return string
     */
    public function get_list_table_url() {
        return admin_url( 'edit.php?taxonomy=addon_category&post_type=menuitem' );
    }

    /**
     * Retrieve pl8app label
     *
     * @since 1.0.0
     * @return void
     */
    public function get_import_type_label() {
        return __('Option and Upgrades','pl8app');
    }
}