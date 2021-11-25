<?php
/**
 * Cart Template
 *
 * @package     pl8app
 * @subpackage  Cart
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Builds the Cart by providing hooks and calling all the hooks for the Cart
 *
 * @since 1.0
 * @return void
 */
function pl8app_checkout_cart() {

	// Check if the Update cart button should be shown
	if( pl8app_item_quantities_enabled() ) {
		add_action( 'pl8app_cart_footer_buttons', 'pl8app_update_cart_button' );
	}

	// Check if the Save Cart button should be shown
	if( ! pl8app_is_cart_saving_disabled() ) {
		add_action( 'pl8app_cart_footer_buttons', 'pl8app_save_cart_button' );
	}

	do_action( 'pl8app_before_checkout_cart' );
	echo '<form id="pl8app_checkout_cart_form" class="pl8app-col-lg-4 pl8app-col-md-4 pl8app-col-sm-12 pl8app-col-xs-12 pull-right sticky-sidebar" method="post">';
		echo '<div id="pl8app_checkout_cart_wrap">';
			do_action( 'pl8app_checkout_cart_top' );
			pl8app_get_template_part( 'checkout_cart' );
			do_action( 'pl8app_checkout_cart_bottom' );
		echo '</div>';
	echo '</form>';
	do_action( 'pl8app_after_checkout_cart' );
}

/**
 * Renders the Shopping Cart
 *
 * @since 1.0
 *
 * @param bool $echo
 * @return string Fully formatted cart
 */
function pl8app_shopping_cart( $echo = false ) {
	pl8app_get_template_part( 'cart/cart' );
}

/**
 * Renders the Allergy Form
 *
 * @ manual allergy Form
 */
function pl8app_pg_allergy_form(){

    ob_start();

    pl8app_get_template_part( 'forms/store-allergy-form' );

    $display = ob_get_clean();
    return $display;
}

/**
 * Renders the Allergy Form
 *
 * @ manual allergy Form
 */
function pl8app_pg_contact_form(){
    pl8app_get_template_part( 'forms/store-contact-form' );
}

/**
 * Renders the FAQ page
 */
function pl8app_pg_faq(){
    pl8app_get_template_part( 'forms/faq' );
}

/**
 * Renders the DELIVERY, RETURNS AND REFUNDS page
 */

function pl8app_pg_delivery_refund(){
    pl8app_get_template_part( 'forms/delivery_refund' );
}

/**
 * Render the widget before the footer(SiteMap and Store Open timing , Store information)
 */

function pl8app_wg_before_footer($attr){

    $attr = apply_filters('pl8app_widget_before_footer_filter', $attr);
    $setting = get_option('pl8app_settings');
    $copyright_year = get_option('pl8app_current_version_copyright');
    $column_responsive = 'pl8app-col-md-4';
    if(shortcode_exists('pl8app_agg_reviews')) {
        $column_responsive = 'pl8app-col-md-6 pl8app-col-lg-3';
    }
    ob_start();
    ?>
    <div class="container widget-section-before-footer pl8app-footer">
    <?php

    if(isset($attr['sitemap'])){
        ?>
        <div class="pl8app-widget <?php echo $column_responsive;?>">
        <?php
        pl8app_get_template_part( 'widget_before_footer/pl8app-sitemap' );
        ?>
        </div>
        <?php
    }
    if(shortcode_exists('pl8app_agg_reviews')) {
    ?>
        <div class="pl8app-widget <?php echo $column_responsive;?>">
            <h3 class="widget-title"><?php echo __('Reviews');?></h3>
            <?php
                do_shortcode('[pl8app_agg_reviews type="box"]');
            ?>
        </div>
    <?php
    }
    if(isset($attr['opentime'])){
        ?>
        <div class="pl8app-widget <?php echo $column_responsive;?>">
        <?php
        pl8app_get_template_part( 'widget_before_footer/pl8app-store-opentime' );
        ?>
        </div>
        <?php
    }
    if(isset($attr['storeinfo'])){
        ?>
        <div class="pl8app-widget <?php echo $column_responsive;?>">
        <?php
        pl8app_get_template_part( 'widget_before_footer/pl8app-store-information' );
        ?>
        </div>
        <?php
    }
    ?>
        <div class="pl8app-col-md-12">
            <div class="row pl8app-author-credits">
                <p class="text-center">
                    <a href="http://www.pl8app.co.uk">People Served Exactly by PL8APP</a></br>
                    <span>&copy;</span> Copyright <?PHP echo $copyright_year; ?> <?PHP echo !empty($setting['pl8app_store_name'])? $setting['pl8app_store_name']:'';?>.</br>
                    *All images are serving suggestions of the food we produce and may not be representative of the food you order</br>
                </p>
            </div>
        </div>

    </div>

    <?php
    $wg_be_footer = ob_get_clean();
    echo $wg_be_footer;
}

/**
 * Get Cart Item Template
 *
 * @since 1.0
 * @param int $cart_key Cart key
 * @param array $item Cart item
 * @param bool $ajax AJAX?
 * @return string Cart item
*/
function pl8app_get_cart_item_template( $cart_key, $item, $ajax = false, $data_key ) {

	global $post;

	if( empty($item['id']) )
		return;

	$id 			= is_array( $item ) ? $item['id'] : $item;
	$price_id 		= pl8app_get_cart_item_price_id( $item );
	$edit_item_url 	= pl8app_edit_cart_item( $cart_key, $item );
	$remove_url 	= pl8app_remove_item_url( $cart_key );
	$title      	= pl8app_get_cart_item_name( $item );
	$quantity   	= isset( $item['options'] ) ? $item['options']['quantity'] : $item['quantity'];
	$price      	= pl8app_get_cart_item_price( $id, $item, $price_id );
	$addon_itm  	= get_addon_item_formatted($item);
	$instruction 	= get_special_instruction($item);
	$tax_name       = get_cart_item_tax_name($item);
	$item_qty   	= pl8app_get_item_qty_by_key( $cart_key );

	ob_start();

	pl8app_get_template_part( 'cart/item' );

	$item = ob_get_clean();
	$item = str_replace( '{item_qty}', absint( $quantity ), $item );
	$item = str_replace( '{item_title}', $title, $item );
	$item = str_replace( '{item_amount}', $price, $item );
 	$item = str_replace( '{addon_items}', $addon_itm, $item );
	$item = str_replace( '{cart_item_id}', absint( $cart_key ), $item );
	$item = str_replace( '{item_id}', absint( $id ), $item );
	$item = str_replace( '{remove_url}', $remove_url, $item );
	$item = str_replace( '{edit_menu_item}', $edit_item_url, $item );
    $item = str_replace( '{tax_name}', $tax_name, $item );
	$item = str_replace( '{special_instruction}', $instruction, $item );

	return apply_filters( 'pl8app_cart_item', $item, $id );
}

function pl8app_edit_cart_item( $cart_key, $item ) {
	if( is_array($item) && !empty($item) ) {
		return $cart_key;
	}
}

function get_addon_item_formatted( $addon_items ) {

	$html = '';

	$addon_data_items = isset( $addon_items['options']['addon_items'] ) ? $addon_items['options']['addon_items'] : '';

	if ( empty( $addon_data_items) ) {
		$addon_data_items = isset( $addon_items['addon_items'] ) ? $addon_items['addon_items'] : '';
	}

  	if( is_array( $addon_data_items ) && !empty( $addon_data_items ) ) :

    	$html.= '<ul class="addon-item-wrap">';

    	foreach( $addon_data_items as $addon_item ) :

      		if( is_array( $addon_item ) ) :

        		$addon_id = !empty( $addon_item['addon_id'] ) ? $addon_item['addon_id'] : '';

        		if( !empty( $addon_id ) ) :

          			$addon_data = get_term_by( 'id', $addon_id, 'addon_category' );
          			$addon_price = !empty( $addon_item['price'] ) ? pl8app_currency_filter( pl8app_format_amount( $addon_item['price'] ) ) : '';

          			if ( $addon_data ) :

            			$addon_item_name = $addon_data->name;

            			$html.= '<li class="pl8app-cart-item">
			              <span class="pl8app-cart-item-title">'.$addon_item_name.'</span>
			              <span class="addon-item-price cart-item-quantity-wrap">
			                <span class="pl8app-cart-item-price qty-class">'.$addon_price.'</span>
			              </span>
			            </li>';
			        endif;
			    endif;
			endif;
		endforeach;

		$html.= '</ul>';

	endif;

  	return $html;
}

/**
 * Returns the Empty Cart Message
 *
 * @since 1.0
 * @return string Cart is empty message
 */
function pl8app_empty_cart_message() {
	return apply_filters( 'pl8app_empty_cart_message', '<span class="pl8app_empty_cart">' . __( 'CHOOSE AN ITEM FROM THE MENU TO GET STARTED.', 'pl8app' ) . '</span>' );
}

function pl8app_empty_cart_message_order_history($content){
    /**
     * when user logged in
     */
    $purchases = pl8app_get_users_orders( get_current_user_id(), 20, true, 'any' );
    if($purchases) {
        ob_start();
        ?>
        <td>
            <a href="#" class="button pl8app_reorder" data-payment-id="<?PHP echo $purchases[0]->ID; ?>">
                <span class="pl8app-add-to-cart-label"><?php echo __('<span class="dashicons dashicons-cart"></span> Reorder','pl8app');?></span>
            </a>
        </td>
        <?PHP
        $content = ob_get_clean();
        return $content;
    }else{
        return $content;
    }

}

add_filter('pl8app_empty_cart_message', 'pl8app_empty_cart_message_order_history');
/**
 * Echoes the Empty Cart Message
 *
 * @since 1.0
 * @return void
 */
function pl8app_empty_checkout_cart() {
	echo pl8app_empty_cart_message();
}
add_action( 'pl8app_cart_empty', 'pl8app_empty_checkout_cart' );

/*
 * Calculate the number of columns in the cart table dynamically.
 *
 * @since 1.0
 * @return int The number of columns
 */
function pl8app_checkout_cart_columns() {
	global $wp_filter, $wp_version;

	$columns_count = 3;

	if ( ! empty( $wp_filter['pl8app_checkout_table_header_first'] ) ) {
		$header_first_count = 0;
		$callbacks = version_compare( $wp_version, '4.7', '>=' ) ? $wp_filter['pl8app_checkout_table_header_first']->callbacks : $wp_filter['pl8app_checkout_table_header_first'] ;

		foreach ( $callbacks as $callback ) {
			$header_first_count += count( $callback );
		}
		$columns_count += $header_first_count;
	}

	if ( ! empty( $wp_filter['pl8app_checkout_table_header_last'] ) ) {
		$header_last_count = 0;
		$callbacks = version_compare( $wp_version, '4.7', '>=' ) ? $wp_filter['pl8app_checkout_table_header_last']->callbacks : $wp_filter['pl8app_checkout_table_header_last'] ;

		foreach ( $callbacks as $callback ) {
			$header_last_count += count( $callback );
		}
		$columns_count += $header_last_count;
	}

	return apply_filters( 'pl8app_checkout_cart_columns', $columns_count );
}

/**
 * Display the "Save Cart" button on the checkout
 *
 * @since 1.0
 * @return void
 */
function pl8app_save_cart_button() {

	if ( pl8app_is_cart_saving_disabled() )
		return;

	if ( pl8app_is_cart_saved() ) : ?>
		<a class="pl8app-cart-saving-button pl8app-submit button" id="pl8app-restore-cart-button" href="<?php echo esc_url( add_query_arg( array( 'pl8app_action' => 'restore_cart', 'pl8app_cart_token' => pl8app_get_cart_token() ) ) ); ?>"><?php _e( 'Restore Previous Cart', 'pl8app' ); ?></a>
	<?php endif; ?>
	<a class="pl8app-cart-saving-button pl8app-submit button" id="pl8app-save-cart-button" href="<?php echo esc_url( add_query_arg( 'pl8app_action', 'save_cart' ) ); ?>"><?php _e( 'Save Cart', 'pl8app' ); ?></a>
	<?php
}

/**
 * Displays the restore cart link on the empty cart page, if a cart is saved
 *
 * @since 1.0
 * @return void
 */
function pl8app_empty_cart_restore_cart_link() {

	if( pl8app_is_cart_saving_disabled() )
		return;

	if( pl8app_is_cart_saved() ) {
		echo ' <a class="pl8app-cart-saving-link" id="pl8app-restore-cart-link" href="' . esc_url( add_query_arg( array( 'pl8app_action' => 'restore_cart', 'pl8app_cart_token' => pl8app_get_cart_token() ) ) ) . '">' . __( 'Restore Previous Cart.', 'pl8app' ) . '</a>';
	}
}
add_action( 'pl8app_cart_empty', 'pl8app_empty_cart_restore_cart_link' );

/**
 * Display the "Save Cart" button on the checkout
 *
 * @since 1.0
 * @return void
 */
function pl8app_update_cart_button() {

	if ( ! pl8app_item_quantities_enabled() )
		return; ?>

	<input type="submit" name="pl8app_update_cart_submit" class="button pl8app-submit pl8app-no-js" value="<?php _e( 'Update Cart', 'pl8app' ); ?>"/>
	<input type="hidden" name="pl8app_action" value="update_cart"/>
	<?php
}

/**
 * Display the messages that are related to cart saving
 *
 * @since 1.0
 * @return void
 */
function pl8app_display_cart_messages() {
	$messages = PL8PRESS()->session->get( 'pl8app_cart_messages' );

	if ( $messages ) {
		foreach ( $messages as $message_id => $message ) {

			// Try and detect what type of message this is
			if ( strpos( strtolower( $message ), 'error' ) ) {
				$type = 'error';
			} elseif ( strpos( strtolower( $message ), 'success' ) ) {
				$type = 'success';
			} else {
				$type = 'info';
			}

			$classes = apply_filters( 'pl8app_' . $type . '_class', array(
				'pl8app_errors', 'pl8app-alert', 'pl8app-alert-' . $type
			) );

			echo '<div class="' . implode( ' ', $classes ) . '">';
			// Loop message codes and display messages
			echo '<p class="pl8app_error" id="pl8app_msg_' . $message_id . '">' . $message . '</p>';
			echo '</div>';
		}

		// Remove all of the cart saving messages
		PL8PRESS()->session->set( 'pl8app_cart_messages', null );
	}
}
add_action( 'pl8app_before_checkout_cart', 'pl8app_display_cart_messages' );

/**
 * Show Added To Cart Messages
 *
 * @since 1.0
 * @param int $menuitem_id Download (Post) ID
 * @return void
 */
function pl8app_show_added_to_cart_messages( $menuitem_id ) {
	if ( isset( $_POST['pl8app_action'] ) && $_POST['pl8app_action'] == 'add_to_cart' ) {
		if ( $menuitem_id != absint( $_POST['menuitem_id'] ) )
			$menuitem_id = absint( $_POST['menuitem_id'] );

		$alert = '<div class="pl8app_added_to_cart_alert">'
		. sprintf( __('You have successfully added %s to your shopping cart.','pl8app' ), get_the_title( $menuitem_id ) )
		. ' <a href="' . pl8app_get_checkout_uri() . '" class="pl8app_alert_checkout_link">' . __('Checkout.','pl8app' ) . '</a>'
		. '</div>';

		echo apply_filters( 'pl8app_show_added_to_cart_messages', $alert );
	}
}
add_action('pl8app_after_menuitem_content', 'pl8app_show_added_to_cart_messages');


/**
 * Contact Us form Submission process
 */
function pl8app_contact_us_action_form_submission(){

    if(isset($_POST['pl8app_contact_us_nonce_field']) && wp_verify_nonce($_POST['pl8app_contact_us_nonce_field'], 'pl8app_contact_us_nonce')) {

        $error_msg = '';
        if(trim($_POST['pl8app-your-name']) === '') {
            $error_msg .= 'Please enter your name. ';
            $hasError = true;
        } else {
            $name = trim($_POST['pl8app-your-name']);
        }

        if(trim($_POST['pl8app-your-email']) === '')  {
            $error_msg .= 'Please enter your email address. ';
            $hasError = true;
        } else if (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", trim($_POST['pl8app-your-email']))) {
            $error_msg .= 'You entered an invalid email address. ';
            $hasError = true;
        } else {
            $email = trim($_POST['pl8app-your-email']);
        }

        if(trim($_POST['pl8app-subject']) === '') {
            $error_msg .= 'Please enter email subject. ';
            $hasError = true;
        } else {
            $subject = trim($_POST['pl8app-subject']);
        }

        if(trim($_POST['pl8app-your-message']) === '') {
            $error_msg .= 'Please enter a message. ';
            $hasError = true;
        } else {
            if(function_exists('stripslashes')) {
                $comments = stripslashes(trim($_POST['pl8app-your-message']));
            } else {
                $comments = trim($_POST['pl8app-your-message']);
            }
        }

        if(!isset($hasError)) {
            $emailTo = pl8app_get_option('pl8app_st_email');

            if (!isset($emailTo) || ($emailTo == '') ){
                $emailTo = get_option('admin_email');
            }
            $emailTo = sanitize_email($emailTo);
            $body = "Name: ". $name ." \r\n
                     Email: ". $email ." \r\n
                     Comments:   " . $comments;
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: '. $email ,
                'Reply-To: ' . $email);

            $mailResult = wp_mail($emailTo, sanitize_text_field($subject), $body, $headers);

            if(!empty($mailResult)){
                $redirect = add_query_arg(array('status'=> 'success', 'message' => __('Email was sent successfully!','pl8app')), home_url($_POST['_wp_http_referer']));
            }
            else{
                $redirect = add_query_arg(array('status'=> 'failed', 'message' => __('Failed in sending email!','pl8app')), home_url($_POST['_wp_http_referer']));
            }

        }
        else{
            $redirect = add_query_arg(array('status'=> 'failed', 'message' => $error_msg), home_url($_POST['_wp_http_referer']));
        }


    }
    else{
        $redirect = add_query_arg(array('status'=> 'failed', 'message' => __('Unauthorized Request!','pl8app')), home_url($_POST['_wp_http_referer']));
    }
    wp_redirect($redirect); exit;
}
add_action( 'admin_post_nopriv_pl8app_contact_us_action', 'pl8app_contact_us_action_form_submission' );
add_action( 'admin_post_pl8app_contact_us_action', 'pl8app_contact_us_action_form_submission' );



/**
 * Allergy form Submission process
 */
function pl8app_allergy_action_form_submission(){

    if(isset($_POST['pl8app_allergy_nonce_field']) && wp_verify_nonce($_POST['pl8app_allergy_nonce_field'], 'pl8app_allergy_nonce')) {

        $error_msg = '';
        if(trim($_POST['pl8app-your-name']) === '') {
            $error_msg .= 'Please enter your name. ';
            $hasError = true;
        } else {
            $name = trim($_POST['pl8app-your-name']);
        }

        if(trim($_POST['pl8app-your-email']) === '')  {
            $error_msg .= 'Please enter your email address. ';
            $hasError = true;
        } else if (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", trim($_POST['pl8app-your-email']))) {
            $error_msg .= 'You entered an invalid email address. ';
            $hasError = true;
        } else {
            $email = trim($_POST['pl8app-your-email']);
        }

        if(trim($_POST['pl8app-your-message']) === '') {
            $error_msg .= 'Please enter a message. ';
            $hasError = true;
        } else {
            if(function_exists('stripslashes')) {
                $comments = stripslashes(trim($_POST['pl8app-your-message']));
            } else {
                $comments = trim($_POST['pl8app-your-message']);
            }
        }

        if(!isset($hasError)) {
            $emailTo = pl8app_get_option('pl8app_st_email');
            if (!isset($emailTo) || ($emailTo == '') ){
                $emailTo = get_option('admin_email');
            }
            $emailTo = sanitize_email($emailTo);
            $body = sanitize_text_field(
                    "Name: ". $name ." \r\n
                     Email: ". $email ." \r\n
                     Comments:   " . $comments);
            $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                        'From: '. $name .' \r\n',
                        'Reply-To: ' . $email);
            $subject = sanitize_text_field('[Allergy Notification] From '.$name);

            $mailResult = wp_mail($emailTo, $subject, $body, $headers);

            if(!empty($mailResult)){
                $redirect = add_query_arg(array('status'=> 'success', 'message' => __('Email was sent successfully!','pl8app')), home_url($_POST['_wp_http_referer']));
            }
            else{
                $redirect = add_query_arg(array('status'=> 'failed', 'message' => __('Failed in sending email!','pl8app')), home_url($_POST['_wp_http_referer']));
            }

        }
        else{
            $redirect = add_query_arg(array('status'=> 'failed', 'message' => $error_msg), home_url($_POST['_wp_http_referer']));
        }


    }
    else{
        $redirect = add_query_arg(array('status'=> 'failed', 'message' => __('Unauthorized Request!','pl8app')), home_url($_POST['_wp_http_referer']));
    }
    wp_redirect($redirect); exit;
}
add_action( 'admin_post_nopriv_pl8app_allergy_action', 'pl8app_allergy_action_form_submission' );
add_action( 'admin_post_pl8app_allergy_action', 'pl8app_allergy_action_form_submission' );
