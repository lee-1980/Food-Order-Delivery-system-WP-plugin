<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the dashboard widgets
 *
 * @author pl8app
 * @since  1.0.0
 * @return void
 */
function pl8app_register_dashboard_widgets() {
	if ( current_user_can( apply_filters( 'pl8app_dashboard_stats_cap', 'view_shop_reports' ) ) ) {

	    global $wp_meta_boxes;
        foreach ($wp_meta_boxes['dashboard']['normal']['core'] as $key => $wp_meta_box) {

            if (strpos($key, 'pl8app') === false){
                unset($wp_meta_boxes['dashboard']['normal']['core'][$key]);
            }
        }
        foreach ($wp_meta_boxes['dashboard']['side']['core'] as $key => $wp_meta_box) {
            if (strpos($key, 'pl8app') === false){
                unset($wp_meta_boxes['dashboard']['side']['core'][$key]);
            }
        }
		wp_add_dashboard_widget( 'pl8app_dashboard_sales', __('pl8app Sales Summary','pl8app' ), 'pl8app_dashboard_sales_widget' );
        wp_add_dashboard_widget( 'pl8app_dashboard_emergency', __('pl8app Emergency Stop','pl8app' ), 'pl8app_dashboard_emergency_widget' );
        wp_add_dashboard_widget( 'pl8app_dashboard_visualization', __('pl8app Order Overview','pl8app' ), 'pl8app_dashboard_visualization_widget' );
        wp_add_dashboard_widget( 'pl8app_dashboard_inventory_notification', __('pl8app Inventory Notification','pl8app' ), 'pl8app_dashboard_notification_widget' );
	}
}
add_action('wp_dashboard_setup', 'pl8app_register_dashboard_widgets', 9999  );



/**
 * Sales Summary Dashboard Widget
 *
 * Builds and renders the Sales Summary dashboard widget. This widget displays
 * the current month's sales and earnings, total sales and earnings best selling
 * menuitems as well as recent purchases made on your pl8app Store.
 *
 * @author pl8app
 * @since  1.0.0
 * @return void
 */
function pl8app_dashboard_sales_widget( ) {
	echo '<p><img src=" ' . esc_attr( set_url_scheme( PL8_PLUGIN_URL . 'assets/images/loading.gif', 'relative' ) ) . '"/></p>';
}

/**
 * Emergency Stop button widget\
 *
 * Stop the Store based on one click button immediately
 */
function pl8app_dashboard_emergency_widget () {

    $pl8app_settings = get_option('pl8app_settings', array());

    ob_start();
    ?>
    <style>
        .ui-dialog .ui-dialog-titlebar-close {
            width: 40px !important;
        }
    </style>
    <div id="pl8app-emergency-id" class="hidden" style="max-width:800px">
        <br>
        <a class="button button-primary emergency_stop">Confirm</a>
        <br>
        <p class="warning"><?php echo __('After confirmation, Emergency tickbox will need to be unchecked to re-open the store','pl8app');?></p>
    </div>
    <?PHP

    echo '<input type="checkbox" class="emergency_modal" '. (!empty($pl8app_settings['emergency_stop'])?"checked='checked'":"") .'>' . __( 'Emergency Stop', 'pl8app' ) . '</inputcheckbox>';
    echo '<p>' . __( 'To close the store immediately, click the tickbox!', 'pl8app' ) . '</p>';

    $args = array(
        'page' => 'pl8app-store-otime'
    );

    $emgency_content = ob_get_clean();
    echo $emgency_content;
}

function pl8app_dashboard_visualization_widget() {

    $store_service_setting = get_option('pl8app_settings', array());
    $otil_settings = get_option('pl8app_otil', array());
    $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();

    if (isset($store_timings['open_day']) && is_array($store_timings['open_day']) && count($store_timings['open_day']) > 0 && !empty($store_timings['pre_order_range'])){

        $store_timing   = new pl8app_StoreTiming_Functions();
        $preorder_dates = $store_timing->get_service_dates();
        $default_date  = !empty( $preorder_dates['formatted_date'] ) ? $preorder_dates['formatted_date'][0] : current_time('Y-m-d');

    }
    else{

        $default_date = current_time('Y-m-d');

    }

    $service_type = isset($store_service_setting['enable_service'])?$store_service_setting['enable_service']:array();
    $delivery_max = isset($otil_settings['orders_per_delivery_interval'])?$otil_settings['orders_per_delivery_interval']:'';
    $pickup_max = isset($otil_settings['orders_per_pickup_interval'])?$otil_settings['orders_per_pickup_interval']:'';

    $service_type_name = '';

    if(!empty($service_type['delivery'])) {
        $service_type_name = 'delivery';
    }
    if(!empty($service_type['pickup'])) {
        $service_type_name = 'pickup';
    }

    if(!empty($service_type['pickup']) && !empty($service_type['delivery'])){
        $service_type_name = 'delivery_and_pickup';
    }

    ?>
    <div class="pl8app_dashboard_widget">
        <div class="table table_visual_respresenation">
            <p class="selected-order-date">
                <span>
                    <label for="service-date-filter">Service Date:</label>
                    <?php if( !empty( $default_date ) ) :
                        $default_date = pl8app_local_date( $default_date );
                        $default_date = apply_filters( 'pl8app_service_date_view', $default_date );
                    endif; ?>
                    <input type="text" id="service-date-filter" name="service-date" class="pl8app-service-date"
                       value="<?PHP echo $default_date; ?>">
                </span>
            </p>
            <div class="visual_container horizontal rounded">
                <p>
                    <span class="maxslot right" data-service="<?PHP echo $service_type_name?>">
                        <?php if( !empty($service_type['delivery'])) {
                        ?>
                        <span class="delivery-max"><strong>Max per delivery</strong>: <?PHP echo $delivery_max;?></span>
                        <?php }
                        if(!empty($service_type['pickup'])) { ?>
                        <span class="pickup-max"><strong>Max per pickup</strong>: <?PHP echo $pickup_max;?></span>
                        <?php } ?>
                    </span>
                </p>
                <table width="100%" border="1">
                    <col style="width:20%">
                    <col style="width:20%">
                    <col style="width:60%">
                    <thead>
                    <tr>
                        <th>Time Slot</th>
                        <th>Service</th>
                        <th>Progress</th>
                    </tr>
                    </thead>
                    <tbody class="slot-chart-content">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}


function pl8app_dashboard_notification_widget(){

    $options = get_option('pl8app_settings', array());
    $threshold_stock_value = !empty($options['threshold_stock_value'])? $options['threshold_stock_value'] : 0;
    if(isset($options['low_stock_notification']) && $options['low_stock_notification'] == 1){
        $query = array(
            'post_type'      => 'menuitem',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );

        $posts_result = new WP_Query( $query );
        $menuitems = $posts_result->posts;
        foreach($menuitems as $menuitem){
            $post_id = $menuitem->ID;
            $stock_enable = get_post_meta( $post_id, 'pl8app_item_stock_enable', true );
            $stock_meta =  get_post_meta( $post_id, 'pl8app_item_stock', true );
            $available_stock = !empty($stock_meta) ? (int)$stock_meta : 0;

            if($stock_enable == 1 && (int)$threshold_stock_value >= (int)$available_stock){
                ?>
                <p style="border-left: 4px solid #d63638; padding: 5px; background-color: #f0f0f1;">
                    Stock Warning: <strong><?php echo $menuitem->post_title;?></strong>'s is now low on stock (<?php echo $available_stock; ?>)
                </p>
                <?php
            }

        }
    }

}
/**
 * Loads the dashboard sales widget via ajax
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_load_dashboard_sales_widget( ) {

	if ( ! current_user_can( apply_filters( 'pl8app_dashboard_stats_cap', 'view_shop_reports' ) ) ) {
		die();
	}

	$stats = new pl8app_Payment_Stats; ?>
	<div class="pl8app_dashboard_widget">
		<div class="table table_left table_current_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Current Month', 'pl8app' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t monthly_earnings"><?php _e( 'Earnings', 'pl8app' ); ?></td>
						<td class="b b-earnings"><?php echo pl8app_currency_filter( pl8app_format_amount( $stats->get_earnings( 0, 'this_month' ) ) ); ?></td>
					</tr>
					<tr>
						<?php $monthly_sales = $stats->get_sales( 0, 'this_month', false, array( 'publish', 'revoked' ) ); ?>
						<td class="first t monthly_sales"><?php echo _n( 'Sale', 'Sales', $monthly_sales, 'pl8app' ); ?></td>
						<td class="b b-sales"><?php echo $monthly_sales; ?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Last Month', 'pl8app' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t earnings"><?php echo __( 'Earnings', 'pl8app' ); ?></td>
						<td class="b b-last-month-earnings"><?php echo pl8app_currency_filter( pl8app_format_amount( $stats->get_earnings( 0, 'last_month' ) ) ); ?></td>
					</tr>
					<tr>
						<td class="first t sales">
							<?php $last_month_sales = $stats->get_sales( 0, 'last_month', false, array( 'publish', 'revoked' ) ); ?>
							<?php echo _n( 'Sale', 'Sales', pl8app_format_amount( $last_month_sales, false ), 'pl8app' ); ?>
						</td>
						<td class="b b-last-month-sales">
							<?php echo $last_month_sales; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_today">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php _e( 'Today', 'pl8app' ); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t sales"><?php _e( 'Earnings', 'pl8app' ); ?></td>
						<td class="last b b-earnings">
							<?php $earnings_today = $stats->get_earnings( 0, 'today', false ); ?>
							<?php echo pl8app_currency_filter( pl8app_format_amount( $earnings_today ) ); ?>
						</td>
					</tr>
					<tr>
						<td class="t sales">
							<?php _e( 'Sales', 'pl8app' ); ?>
						</td>
						<td class="last b b-sales">
							<?php $sales_today = $stats->get_sales( 0, 'today', false, array( 'publish', 'revoked' ) ); ?>
							<?php echo $sales_today; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_totals">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Totals', 'pl8app' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t earnings"><?php _e( 'Total Earnings', 'pl8app' ); ?></td>
						<td class="last b b-earnings"><?php echo pl8app_currency_filter( pl8app_format_amount( pl8app_get_total_earnings() ) ); ?></td>
					</tr>
					<tr>
						<td class="t sales"><?php _e( 'Total Sales', 'pl8app' ); ?></td>
						<td class="last b b-sales"><?php echo pl8app_get_total_sales(); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both"></div>
		<?php do_action( 'pl8app_sales_summary_widget_after_stats', $stats ); ?>
		<?php
		$p_query = new pl8app_Payments_Query( array(
			'number'   => 5,
			'status'   => 'publish'
		) );

		$payments = $p_query->get_payments();

		if ( $payments ) { ?>
		<div class="table recent_purchases">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php _e( 'Recent Purchases', 'pl8app' ); ?>
							<a href="<?php echo admin_url( 'admin.php?page=pl8app-payment-history' ); ?>">&nbsp;&ndash;&nbsp;<?php _e( 'View All', 'pl8app' ); ?></a>
						</td>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $payments as $payment ) { ?>
						<tr>
							<td class="pl8app_order_label">
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details' ) ); ?>">
									<?php echo get_the_title( $payment->ID ) ?>
									&mdash; <?php echo $payment->email ?>
								</a>
								<?php if ( ! empty( $payment->user_id ) && ( $payment->user_id > 0 ) ) {
									$user = get_user_by( 'id', $payment->user_id );
									if ( $user ) {
										echo "(" . $user->data->user_login . ")";
									}
								} ?>
							</td>
							<td class="pl8app_order_price">
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details' ) ); ?>">
									<span class="pl8app_price_label"><?php echo pl8app_currency_filter( pl8app_format_amount( $payment->total ), pl8app_get_payment_currency_code( $payment->ID ) ); ?></span>
								</a>
							</td>
						</tr>
						<?php
					} // End foreach ?>
				</tbody>
			</table>
		</div>
		<?php } // End if ?>
		<?php do_action( 'pl8app_sales_summary_widget_after_purchases', $payments ); ?>
	</div>
	<?php
	die();
}
add_action( 'wp_ajax_pl8app_load_dashboard_widget', 'pl8app_load_dashboard_sales_widget' );

/**
 * Add menuitem count to At a glance widget
 *
 * @author pl8app
 * @since  1.0.0
 * @return void
 */
function pl8app_dashboard_at_a_glance_widget( $items ) {
	$num_posts = wp_count_posts( 'menuitem' );

	if ( $num_posts && $num_posts->publish ) {
		$text = _n( '%s ' . pl8app_get_label_singular(), '%s ' . pl8app_get_label_plural(), $num_posts->publish, 'pl8app' );

		$text = sprintf( $text, number_format_i18n( $num_posts->publish ) );

		if ( current_user_can( 'edit_products' ) ) {
			$text = sprintf( '<a class="menuitem-count" href="edit.php?post_type=menuitem">%1$s</a>', $text );
		} else {
			$text = sprintf( '<span class="menuitem-count">%1$s</span>', $text );
		}

		$items[] = $text;
	}

	return $items;
}
add_filter( 'dashboard_glance_items', 'pl8app_dashboard_at_a_glance_widget', 1 );


