<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */
function pl8app_options_page() {

	$settings_tabs = pl8app_get_settings_tabs();
	$settings_tabs = empty($settings_tabs) ? array() : $settings_tabs;
	$active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
	$active_tab    = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'general';
	$sections      = pl8app_get_settings_tab_sections( $active_tab );
	$key           = 'order_notification';

	if ( ! empty( $sections ) ) {
		$key = key( $sections );
	}

	$registered_sections = pl8app_get_settings_tab_sections( $active_tab );
	$section             = isset( $_GET['section'] ) && ! empty( $registered_sections ) && array_key_exists( $_GET['section'], $registered_sections ) ? sanitize_text_field( $_GET['section'] ) : $key;

	// Unset 'main' if it's empty and default to the first non-empty if it's the chosen section
	$all_settings = pl8app_get_registered_settings();

	// Let's verify we have a 'main' section to show
	$has_main_settings = true;
	if ( empty( $all_settings[ $active_tab ]['main'] ) ) {
		$has_main_settings = false;
	}

	// Check for old non-sectioned settings (see #4211 and #5171)
	if ( ! $has_main_settings ) {
		foreach( $all_settings[ $active_tab ] as $sid => $stitle ) {
			if ( is_string( $sid ) && ! empty( $sections) && array_key_exists( $sid, $sections ) ) {
				continue;
			} else {
				$has_main_settings = true;
				break;
			}
		}
	}

	$override = false;
	if ( false === $has_main_settings ) {
		unset( $sections['main'] );

		if ( 'main' === $section ) {
			foreach ( $sections as $section_key => $section_title ) {
				if ( ! empty( $all_settings[ $active_tab ][ $section_key ] ) ) {
					$section  = $section_key;
					$override = true;
					break;
				}
			}
		}
	}

	ob_start();
	?>
	<div class="wrap <?php echo 'wrap-' . $active_tab; ?>">
		<h2><?php _e( 'pl8app Settings', 'pl8app' ); ?></h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( pl8app_get_settings_tabs() as $tab_id => $tab_name ) {
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab'              => $tab_id,
				) );

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg( 'section', $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<?php

		$number_of_sections = count( $sections );
		$number = 0;
		if ( $number_of_sections > 1 ) {
			echo '<div><ul class="subsubsub">';
			foreach( $sections as $section_id => $section_name ) {
				echo '<li>';
				$number++;
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $active_tab,
					'section' => $section_id
				) );
				$class = '';
				if ( $section == $section_id ) {
					$class = 'current';
				}
				echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section_name . '</a>';

				if ( $number != $number_of_sections ) {
					echo ' | ';
				}
				echo '</li>';
			}
			echo '</ul></div>';
		}
		?>
		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table pl8app-settings">
				<?php

				settings_fields( 'pl8app_settings' );

				if ( 'main' === $section ) {
					do_action( 'pl8app_settings_tab_top', $active_tab );
				}

				 do_action( 'pl8app_settings_tab_top_' . $active_tab . '_' . $section );


				 do_settings_sections( 'pl8app_settings_' . $active_tab . '_' . $section );

				 do_action( 'pl8app_settings_tab_bottom_' . $active_tab . '_' . $section  );

				// For backwards compatibility
				if ( 'main' === $section ) {
					do_action( 'pl8app_settings_tab_bottom', $active_tab );
				}

				// If the main section was empty and we overrode the view with the next subsection, prepare the section for saving
				if ( true === $override ) {
					?><input type="hidden" name="pl8app_section_override" value="<?php echo $section; ?>" /><?php
				}
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}


/**
 * Options Custom Page
 *
 * Renders the options page contents
 */

function pl8app_general_settings_page() {

    $store_timings = get_option( 'pl8app_store_timing', array() );
    $preorder_range = isset( $store_timings['pre_order_range'] ) ? $store_timings['pre_order_range'] : '';
    $options = get_option('pl8app_settings');


    $otil_settings          = new Order_Time_Interval_Limit_Settings();
    $otil_settings          = $otil_settings->options;

    //Time interval duration
    $time_interval_duration         = !empty( $otil_settings['time_interval_duration'] ) ? $otil_settings['time_interval_duration'] : '';


    $orders_per_interval          = !empty( $otil_settings['orders_per_interval'] ) ? $otil_settings['orders_per_interval'] : '';
    $orders_per_delivery_interval = !empty( $otil_settings['orders_per_delivery_interval'] ) ? $otil_settings['orders_per_delivery_interval'] :  $orders_per_interval;
    $orders_per_pickup_interval   = !empty( $otil_settings['orders_per_pickup_interval'] ) ? $otil_settings['orders_per_pickup_interval'] :  $orders_per_interval;
    $orders_per_total_interval = !empty( $otil_settings['orders_per_total_interval'] ) ? $otil_settings['orders_per_total_interval'] :  $orders_per_interval;

    $order_interval_error_msg   = !empty( $otil_settings['order_interval_error_msg'] ) ? trim( $otil_settings['order_interval_error_msg'] ) : esc_html( 'The time slot what you have selected is not available', 'pl8app-otil' );

    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Pl8App Your Service', 'pl8app'); ?></h2>

        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Pre Order Range', 'pl8app-store-timing' ); ?></th>
                        <td>
                            <span style="font-style:italic;"><?php esc_html_e('Upto how many days a pre order can be done', 'pl8app-store-timing'); ?></span>
                            <input type="number" value="<?php echo $preorder_range; ?>" name="pl8app_store_timing[pre_order_range]">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Choose Services</th>
                        <td>
                            <?PHP
                            $enable_service = isset($options['enable_service'])?$options['enable_service']:'';
                            ?>
                            <input name="pl8app_settings[enable_service][no_service]" type="hidden" value="0">
                            <input name="pl8app_settings[enable_service][no_service]" id="pl8app_settings[enable_service][no_service]"
                                   class="" type="checkbox" value="1" <?php echo !empty($enable_service['no_service'])?'checked="checked"':''; ?>>&nbsp;
                            <label for="pl8app_settings[enable_service][no_service]">No Service</label>
                            <br>
                            <input name="pl8app_settings[enable_service][delivery]" type="hidden" value="0">
                            <input name="pl8app_settings[enable_service][delivery]" id="pl8app_settings[enable_service][delivery]"
                                   class="" type="checkbox" value="1" <?php echo !empty($enable_service['delivery'])?'checked="checked"':''; ?>>&nbsp;
                            <label for="pl8app_settings[enable_service][delivery]">Delivery</label>
                            <br>
                            <input name="pl8app_settings[enable_service][pickup]" type="hidden" value="0">
                            <input name="pl8app_settings[enable_service][pickup]" id="pl8app_settings[enable_service][pickup]"
                                   class="" type="checkbox" value="1" <?php echo !empty($enable_service['pickup'])?'checked="checked"':''; ?>>&nbsp;
                            <label for="pl8app_settings[enable_service][pickup]">Pickup</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Cooking Time/Prep Time(minutes)</th>
                        <td>
                            <input type="number" step="1" max="999999" min="0" class=" regular-text"
                                   id="pl8app_settings[prep_time]" name="pl8app_settings[prep_time]" value="<?php echo !empty($options['prep_time'])?$options['prep_time']:'';?>">
                            <label for="pl8app_settings[prep_time]"> Enter the time required for menu preparation, it
                                would be used for displaying the time slots intelligibly</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Service Cookies Expire Time</th>
                        <td><input type="number" step="1" max="999999" min="0" class=" regular-text"
                                   id="pl8app_settings[expire_service_cookie]"
                                   name="pl8app_settings[expire_service_cookie]" value="<?php echo !empty($options['expire_service_cookie'])?$options['expire_service_cookie']:'';?>">
                            <label for="pl8app_settings[expire_service_cookie]"> Enter value (in minutes) after which
                                the cookies will be expired.</label>
                        </td>
                    </tr>

                    <!-- Disable pickup time starts here -->
<!--                    <tr>-->
<!--                        <th scope="row">-->
<!--                            --><?php //esc_html_e( 'Disable Pickup Time?', 'pl8app-otil' ); ?>
<!--                        </th>-->
<!--                        <td>-->
<!--                            <input id="disable_pickup_time" type="checkbox" name="pl8app_otil[disable_pickup_time]" value="enable" --><?php //echo checked( $disable_pickup_time, 'enable', true ); ?><!-->
<!--                            <label for="disable_pickup_time"> --><?php //esc_html_e( 'Enable this option to disable pickup time.','pl8app-otil'); ?><!--</label>-->
<!--                        </td>-->
<!--                    </tr>-->
                    <!-- Disable pickup time ends here -->

                    <!-- Disable delivery time starts here -->
<!--                    <tr>-->
<!--                        <th scope="row">-->
<!--                            --><?php //esc_html_e( 'Disable Delivery Time?', 'pl8app-otil' ); ?>
<!--                        </th>-->
<!--                        <td>-->
<!--                            <input id="disable_delivery_time" type="checkbox" name="pl8app_otil[disable_delivery_time]" value="enable" --><?php //echo checked( $disable_delivery_time, 'enable', true ); ?><!-->
<!--                            <label for="disable_delivery_time"> --><?php //esc_html_e( 'Enable this option to disable delivery time.', 'pl8app-otil'); ?><!--</label>-->
<!--                        </td>-->
<!--                    </tr>-->
                    <!-- Disable pickup time ends here -->

                    <!-- Time interval for delivery starts here -->
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Time Interval for services', 'pl8app-otil' ); ?>
                        </th>
                        <td>
                            <input type="number" class="small-text" name="pl8app_otil[time_interval_duration]" value="<?php echo $time_interval_duration; ?>" id="time_interval_duration">
                            <label for="time_interval_duration"> <?php esc_html_e( 'Enter time in minutes for time slot for service. Keep it empty if you want to keep it default.', 'pl8app-otil'); ?></label>
                        </td>
                    </tr>
                    <!-- Time interval for delivery ends here -->

                    <!-- Orders interval for delivery starts here -->
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Max orders per delivery slot', 'pl8app-otil' ); ?>
                        </th>
                        <td>
                            <input type="number" class="small-text" name="pl8app_otil[orders_per_delivery_interval]" value="<?php echo $orders_per_delivery_interval; ?>" id="orders_per_delivery_interval">
                            <label for="orders_per_delivery_interval">
                                <?php esc_html_e( 'Enter the number of orders for delivery service you want to accept for each time slot.' , 'pl8app-otil'); ?>
                            </label>
                        </td>
                    </tr>
                    <!-- Orders interval time ends here -->

                    <!-- Orders interval for pickup starts here -->
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Max orders per pickup slot', 'pl8app-otil' ); ?>
                        </th>
                        <td>
                            <input type="number" class="small-text" name="pl8app_otil[orders_per_pickup_interval]" value="<?php echo $orders_per_pickup_interval; ?>" id="orders_per_pickup_interval">
                            <label for="orders_per_pickup_interval">
                                <?php esc_html_e( 'Enter the number of orders you want to accept for pickup service in each time slot.' , 'pl8app-otil'); ?>
                            </label>
                        </td>
                    </tr>
                    <!-- Orders interval time ends here -->

                    <!-- Total Orders pick up  starts here-->
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Max total orders per slot', 'pl8app-otil'); ?>
                            </th>
                            <td>
                                <input type="number" class="small-text" name="pl8app_otil[orders_per_total_interval]"
                                       value="<?php echo $orders_per_total_interval; ?>"
                                       id="orders_per_total_interval">
                                <label for="orders_per_pickup_interval">
                                    <?php esc_html_e('Enter the number of total orders you want to accept for both of delivery and pickup service in each time slot.', 'pl8app-otil'); ?>
                                </label>
                            </td>
                        </tr>
                        <!-- Total Orders pick up  ends here-->
                    <!-- Orders interval starts here -->
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Order limit reached message', 'pl8app-otil' ); ?>
                        </th>
                        <td>
                            <textarea class="large-text" cols="50" rows="5" id="menuitems_order_error" name="pl8app_otil[order_interval_error_msg]"><?php echo $order_interval_error_msg; ?></textarea>
                            <label for="menuitems_order_error"> <?php esc_html_e( 'This message will be displayed when there order limit is reach for the selected time slot.', 'pl8app-otil'); ?></label>
                            </label>
                        </td>
                    </tr>
                    <!-- Orders interval time ends here -->
                </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}


/**
 * Options for Pick up
 *
 * Renders the options related to PickUp minimum amount, Max Items
 */

function pl8app_pickup_settings_page() {

    $options = get_option('pl8app_settings');

    $otil_settings          = new Order_Time_Interval_Limit_Settings();
    $otil_settings          = $otil_settings->options;

    $menuitems_per_order          = !empty( $otil_settings['menuitems_per_order'] ) ? $otil_settings['menuitems_per_order'] : '';
    $menuitems_per_pickup_order   = !empty( $otil_settings['menuitems_per_pickup_order'] ) ? $otil_settings['menuitems_per_pickup_order'] : $menuitems_per_order;
    $menuitems_order_error      = !empty( $otil_settings['menuitems_order_error'] ) ? $otil_settings['menuitems_order_error'] : '';


    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Pl8App PickUp Options', 'pl8app'); ?></h2>

        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">

                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                <table class="form-table" role="presentation">

                    <!-- Menuitems per orders starts here -->
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Maximum items per pickup order', 'pl8app-otil' ); ?>
                        </th>
                        <td>
                            <input type="number" class="small-text" name="pl8app_otil[menuitems_per_pickup_order]" value="<?php echo $menuitems_per_pickup_order; ?>" id="menuitems_per_pickup_order">
                            <label for="menuitems_per_pickup_order"> <?php esc_html_e( 'Enter max number of menuitems allowed for pickup order. Leave empty if you don\'t want to restrict', 'pl8app-otil'); ?></label>
                        </td>
                    </tr>
                    <!-- Menuitems per order ends here -->


                    <!-- Menuitems order error -->
                    <tr>
                        <th scope="row">
                            <?php esc_html_e( 'Error message for max items', 'pl8app-otil' ); ?>
                        </th>
                        <td>
                            <textarea class="large-text" cols="50" rows="5" id="menuitems_order_error" name="pl8app_otil[menuitems_order_error]"><?php echo $menuitems_order_error; ?></textarea>
                            <label for="menuitems_order_error"> <?php esc_html_e( 'This message will be displayed when the order has more than allowed menu items in one order.', 'pl8app-otil'); ?></label>
                        </td>
                    </tr>
                    <!-- Menuitems order error -->

                    <tr>
                        <th scope="row">Enable minimum order</th>
                        <td><input type="hidden" name="pl8app_settings[allow_minimum_order]" value="-1">
                            <input type="checkbox" id="pl8app_settings[allow_minimum_order]"
                                   name="pl8app_settings[allow_minimum_order]" value="1" class="" <?php $options['allow_minimum_order'] = !empty($options['allow_minimum_order'])? $options['allow_minimum_order']:''; echo checked( $options['allow_minimum_order'], '1', true ); ?>>
                            <label for="pl8app_settings[allow_minimum_order]"> Enable this if you want to restrict
                                users to order for a minimum amount.</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Minimum order amount for pickup</th>
                        <td>
                            <input type="number" step="1" max="999999" min="0" class=" small-text"
                                   id="pl8app_settings[minimum_order_price_pickup]"
                                   name="pl8app_settings[minimum_order_price_pickup]"
                                   value="<?php echo !empty($options['minimum_order_price_pickup'])?$options['minimum_order_price_pickup']:'';?>" placeholder="100">
                            <label for="pl8app_settings[minimum_order_price_pickup]"> The minimum order amount in order
                                to place the order for pickup service.</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Minimum order error message for pickup</th>
                        <td>
                            <textarea class=" large-text" cols="50" rows="5" id="pl8app_settings[minimum_order_error_pickup]"
                                      name="pl8app_settings[minimum_order_error_pickup]" placeholder="We accept order for at least {min_order_price} for pickup">
                                <?php echo !empty($options['minimum_order_error_pickup'])?$options['minimum_order_error_pickup']:'';?>
                            </textarea>
                            <label for="pl8app_settings[minimum_order_error_pickup]"> This would be the error message
                                when someone tries to place an order with less than the minimum order amount for pickup
                                service, You can use {min_order_price} variable in the message.
                            </label>
                        </td>
                    </tr>
                </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}


/**
 * Options for Delivery
 *
 * Renders the options related to Delivery Minimum amount, Max Items
 */

function pl8app_delivery_settings_page() {
    $options = get_option('pl8app_settings');
    $store_timings = isset($options['delivery_timing'])?$options['delivery_timing']:array();
    $otil_settings          = new Order_Time_Interval_Limit_Settings();
    $otil_settings          = $otil_settings->options;
    $menuitems_order_error      = !empty( $otil_settings['menuitems_order_error'] ) ? $otil_settings['menuitems_order_error'] : '';
    $menuitems_per_order          = !empty( $otil_settings['menuitems_per_order'] ) ? $otil_settings['menuitems_per_order'] : '';
    $menuitems_per_delivery_order = !empty( $otil_settings['menuitems_per_delivery_order'] ) ? $otil_settings['menuitems_per_delivery_order'] : $menuitems_per_order;

    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Pl8App Delivery Options', 'pl8app'); ?></h2>

        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">

                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                <!-- Store Days Open Starts Here -->
                <h4 class="pl8app-label-section"><?php esc_html_e( '--- Delivery Cutoff Time ---', 'pl8app' ); ?></h4>
                <span><?php esc_html_e( 'Select the start and end time for which the delivery will be turned off. Setting the delivery cutoff time will restrict the users to select delivery for the specified time.' ); ?></span>

                <?php
                $days = pl8app_StoreTiming::pl8app_st_get_weekdays();
                ?>
                <div id="pl8app_store_timings" class="pl8app_store_timings store_delivery_cut">
                    <?php
                    if ( is_array( $days ) && !empty( $days ) ) :

                        foreach( $days as $key => $day ) : ?>
                            <div class="pl8app_store_timings_day">
                                <div class="pl8app_st_days_name"><?php echo $day; ?></div>
                                <div class="pl8app_st_days_checkbox">
                                    <label for="<?php echo $day; ?>" class="store_timing_checkbox_wrapper">
                                        <?php
                                        // Open Day Checkbox
                                        if (isset($store_timings['open_day'][$day])) : ?>
                                            <input type="checkbox" id="<?php echo $day; ?>" class="st_checkbox"
                                                   name="<?php echo "pl8app_settings[delivery_timing][open_day][$day]" ?>" checked="checked"
                                                   value="enable">
                                        <?php else : ?>
                                            <input type="checkbox" class="st_checkbox"
                                                   name="<?php echo "pl8app_settings[delivery_timing][open_day][$day]" ?>" id="<?php echo $day; ?>"
                                            >
                                        <?php endif; ?>
                                        <span class="st_checkbox_slider round"></span>

                                    </label>
                                    <span class="checkbox_stat_label open <?php echo isset($store_timings['open_day'][$day])?"":"hidden"?>">Open</span>
                                    <span class="checkbox_stat_label closed <?php echo isset($store_timings['open_day'][$day])?"hidden":""?>">Closed</span>

                                </div>
                                <?PHP
                                if (isset($store_timings['open_time'][$day]) && is_array($store_timings['open_time'][$day])) :
                                    ?>
                                <div class="st_day_timetable_wrapper <?PHP echo !empty($store_timings['open_day'][$day])?"":"hidden"; ?>" data-day="<?PHP echo $day;?>" >
                                    <span class="checkbox_stat_label 24hours"><input type="checkbox" name="<?php echo "pl8app_settings[delivery_timing][24hours][$day]" ?>" <?PHP echo isset($store_timings['24hours'][$day])? 'checked="checked" value="enabled"':''?>> 24Hours-Open</span>
                                <div class="st_day_timetable <?PHP echo isset($store_timings['24hours'][$day])? "hidden":""?>" >
                                    <?php
                                    $i = 0;
                                    $length = count($store_timings['open_time'][$day]);
                                    foreach ($store_timings['open_time'][$day] as $index => $time) {
                                        ?>
                                        <div class="st_day_time">
                                            <div class="pl8app_st_open_time">
                                                <?php
                                                // Open Time
                                                if (isset($time)) : ?>
                                                    <input type="text" class="pl8app storetime"
                                                           name="<?php echo "pl8app_settings[delivery_timing][open_time][$day][$i]" ?>"
                                                           value="<?php echo $time; ?>">
                                                <?php else: ?>
                                                    <input type="text" class="pl8app storetime"
                                                           name="<?php echo "pl8app_settings[delivery_timing][open_time][$day][$i]; " ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="pass_stion">-</div>
                                            <div class="pl8app_st_close_time">
                                                <?php if (isset($store_timings['close_time'][$day][$i])) : ?>
                                                    <input type="text" class="pl8app storetime"
                                                           name="<?php echo "pl8app_settings[delivery_timing][close_time][$day][$i]" ?>"
                                                           value="<?php echo $store_timings['close_time'][$day][$i]; ?>">
                                                <?php else: ?>
                                                    <input type="text" class="pl8app storetime"
                                                           name="<?php echo "pl8app_settings[delivery_timing][close_time][$day][$i];" ?>">
                                                <?php endif; ?>
                                            </div>
                                            <div class="st_day_time_remove <?php echo $length - 1 == $i?'':'hidden'; ?>">
                                                <span class="st_remove_time_icon"><span class="Ce1Y1c" style="top: -12px">
                                                        <svg xmlns="https://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                            <path
                                                    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path><path
                                                    d="M0 0h24v24H0z" fill="none"></path></svg></span></span>
                                            </div>
                                        </div>
                                    <?php $i++; } ?>
                                        </div>
                                        <div class="st_day_time_add <?php echo !empty($store_timings['open_time'][$day][$i-1])&&!empty($store_timings['close_time'][$day][$i-1])?'':'hidden'; ?>">
                                            <span class="st_day_time_add_new_line"><span>Add hours</span></span>
                                        </div>
                                        </div>
                                    <?php else : ?>
                                    <div class="st_day_timetable_wrapper hidden" data-day="<?PHP echo $day;?>">
                                        <span class="checkbox_stat_label 24hours"><input type="checkbox" name="<?php echo "pl8app_settings[delivery_timing][24hours][$day]" ?>"> 24Hours-Open</span>
                                        <div class="st_day_timetable">
                                            <div class="st_day_time">
                                                <div class="pl8app_st_open_time">
                                                    <input type="text" class="pl8app storetime"
                                                           name="<?php echo "pl8app_settings[delivery_timing][open_time][$day][0]"; ?>">
                                                </div>
                                                <div class="pass_stion">-</div>
                                                <div class="pl8app_st_close_time">
                                                    <input type="text" class="pl8app storetime"
                                                           name="<?php echo "pl8app_settings[delivery_timing][close_time][$day][0]"; ?>">
                                                </div>
                                                <div class="st_day_time_remove hidden">
                                    <span class="st_remove_time_icon">
                                        <span class="Ce1Y1c" style="top: -12px">
                                            <svg xmlns="https://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
                                                <path d="M0 0h24v24H0z" fill="none"></path>
                                            </svg>
                                        </span>
                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="st_day_time_add hidden">
                                            <span class="st_day_time_add_new_line"><span>Add hours</span></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php
                        endforeach; ?>
                    <?php endif; ?>
                </div>
                </div>
                <?php
                pl8app_Delivery_Fee_Settings::get_template_part( 'delivery-fee-setting-fields' );
                ?>
                <div class="pl8app-delivery-fee-wrapper">
                    <table class="form-table" role="presentation">


                        <!-- Menuitems per orders starts here -->
                        <tr>
                            <th scope="row">
                                <?php esc_html_e( 'Maximum items per delivery order', 'pl8app-otil' ); ?>
                            </th>
                            <td>
                                <input type="number" class="small-text" name="pl8app_otil[menuitems_per_delivery_order]" value="<?php echo $menuitems_per_delivery_order; ?>" id="menuitems_per_delivery_order">
                                <label for="menuitems_per_delivery_order"> <?php esc_html_e( 'Enter max number of menuitems allowed for delivery order. Leave empty if you don\'t want to restrict', 'pl8app-otil'); ?></label>
                            </td>
                        </tr>
                        <!-- Menuitems per order ends here -->

                        <!-- Menuitems order error -->
                        <tr>
                            <th scope="row">
                                <?php esc_html_e( 'Error message for max items', 'pl8app-otil' ); ?>
                            </th>
                            <td>
                                <textarea class="large-text" cols="50" rows="5" id="menuitems_order_error" name="pl8app_otil[menuitems_order_error]"><?php echo $menuitems_order_error; ?></textarea>
                                <label for="menuitems_order_error"> <?php esc_html_e( 'This message will be displayed when the order has more than allowed menu items in one order.', 'pl8app-otil'); ?></label>
                            </td>
                        </tr>
                        <!-- Menuitems order error -->

                        <tr>
                            <th scope="row">Enable minimum order</th>
                            <td><input type="hidden" name="pl8app_settings[allow_minimum_order]" value="-1">
                                <input type="checkbox" id="pl8app_settings[allow_minimum_order]"
                                       name="pl8app_settings[allow_minimum_order]" value="1" class="" <?php $options['allow_minimum_order'] = !empty($options['allow_minimum_order'])?$options['allow_minimum_order']:''; echo checked( $options['allow_minimum_order'], '1', true ); ?>>
                                <label for="pl8app_settings[allow_minimum_order]"> Enable this if you want to restrict
                                    users to order for a minimum amount.</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Minimum order amount for delivery</th>
                            <td>
                                <input type="number" step="1" max="999999" min="0" class=" small-text"
                                       id="pl8app_settings[minimum_order_price]" name="pl8app_settings[minimum_order_price]"
                                       value="<?php echo !empty($options['minimum_order_price'])?$options['minimum_order_price']:'';?>" placeholder="100">
                                <label for="pl8app_settings[minimum_order_price]"> The minimum order
                                    amount in order to place the order for delivery service.</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Minimum order error message for delivery</th>
                            <td>
                            <textarea class="large-text" cols="50" rows="5" id="pl8app_settings[minimum_order_error]"
                                      name="pl8app_settings[minimum_order_error]" placeholder="We accept order for at least {min_order_price}">
                                <?php echo !empty($options['minimum_order_error'])?$options['minimum_order_error']:'';?>
                            </textarea>
                                <label for="pl8app_settings[minimum_order_error]"> This would be the error message when
                                    someone tries to place an order with less than the minimum order amount for delivery
                                    service, You can use {min_order_price} variable in the message.</label>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}


/**
 * Inventory Setting Page
 *
 */

function pl8app_inventory_settings_page() {
    $options = get_option('pl8app_settings', array());
    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Inventory Settings', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php echo __('Stock Threshold Value', 'pl8app');?></th>
                            <td>
                                <input type="number" class="small-text" id="pl8app_settings[threshold_stock_value]"
                                       name="pl8app_settings[threshold_stock_value]"
                                       value="<?PHP echo !empty($options['threshold_stock_value']) ? $options['threshold_stock_value'] : 5; ?>"
                                       placeholder="5">
                                <label for="pl8app_settings[threshold_stock_value]"><?php echo __('The minimum stock value below which it will be considered as the item running low on stock.','pl8app'); ?></label>
                            </td>

                        </tr>
                        <tr>
                            <th scope="row">
                                <?php echo __('Low Stock Notification', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[low_stock_notification]" value="-1">
                                <input id="pl8app_settings[low_stock_notification]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[low_stock_notification]"
                                    <?PHP echo !empty($options['low_stock_notification']) ? checked(1, $options['low_stock_notification'], false) : ''; ?>>
                                <label for="pl8app_settings[low_stock_notification]">
                                    <?php echo __('When checked, admin will get notified in admin dashboard when stock of particular item goes below the threshold value.','pl8app'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Out of Stock Button Text', 'pl8app');?></th>
                            <td>
                                <input type="text" class="regular-text" id="pl8app_settings[no_stock_text]"
                                       name="pl8app_settings[no_stock_text]"
                                       value="<?PHP echo !empty($options['no_stock_text']) ? $options['no_stock_text'] : ''; ?>"
                                       placeholder="Sold Out">
                                <label for="pl8app_settings[no_stock_text]">
                                    <?php echo __(' Text for Out of Stock button.','pl8app'); ?>
                                </label>
                            </td>

                        </tr>
                        <tr>
                            <th scope="row">
                                <?php echo __('Return to Stock', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[return_to_stock]" value="-1">
                                <input id="pl8app_settings[return_to_stock]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[return_to_stock]"
                                    <?PHP echo !empty($options['return_to_stock']) ? checked(1, $options['return_to_stock'], false) : ''; ?>>
                                <label for="pl8app_settings[return_to_stock]">
                                    <?php echo __('Check this option to update stock value of an item if the associated order is cancelled. It will check all the "Cancelled" orders for this purpose.','pl8app'); ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo __('Disable Empty Stock Items', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[enable_null_stock]" value="-1">
                                <input id="pl8app_settings[enable_null_stock]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[enable_null_stock]"
                                    <?PHP echo !empty($options['enable_null_stock']) ? checked(1, $options['enable_null_stock'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_null_stock]">
                                    <?php echo __('When checked, items without a valid stock value will go out of stock. It will be necessary to enter a stock value for those items.','pl8app'); ?>
                                </label>
                            </td>
                        </tr>

                    </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}


/**
 * Order Notification
 */
function pl8app_order_notification(){
    $options = get_option('pl8app_settings', array());
    ob_start();

    $notification_styles = array(
        'default' => __('Default', 'pl8app'),
        'sound' => __('Alternative', 'pl8app'),
        'voice' => __('Voice only', 'pl8app'),
    );

    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Order Notification', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <?php echo __('Enable Notification', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[enable_order_notification]" value="-1">
                                <input id="pl8app_settings[enable_order_notification]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[enable_order_notification]"
                                    <?PHP echo !empty($options['enable_order_notification']) ? checked(1, $options['enable_order_notification'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_order_notification]">
                                    <?php echo __('Enable order notification','pl8app'); ?>
                                </label>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo __('Notification Style', 'pl8app'); ?>
                            </th>
                            <td>
                                <select id="pl8app_settings[order_notification_styles]" name="pl8app_settings[order_notification_styles]"
                                        class="" data-placeholder="">
                                    <?php foreach($notification_styles as $key => $value) {?>
                                        <option value="<?php echo $key; ?>" <?php echo isset($options['order_notification_styles']) && $options['order_notification_styles'] == $key?'selected="selected"':''; ?>><?php echo $value; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="pl8app_settings[login_method]">
                                    <?php echo __('Pick which of the notification styles.', 'pl8app'); ?></label></td>
                            </td>
                        </tr>

                    </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}

/**
 * Checkout Options setting
 */

function pl8app_checkout_options(){
    $options = get_option('pl8app_settings', array());
    ob_start();
    $login_methods = array(
            'login_guest' => __('Login/Register with guest checkout', 'pl8app'),
        'login_only' => __('Login/Register only', 'pl8app'),
        'guest_only' => __('Guest checkout only', 'pl8app'),
    );
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Checkout Options', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">

                        <tr>
                            <th scope="row">Login/Register Option</th>
                            <td>
                                <select id="pl8app_settings[login_method]" name="pl8app_settings[login_method]"
                                        class="" data-placeholder="">
                                    <?php foreach($login_methods as $key => $value) {?>
                                        <option value="<?php echo $key; ?>" <?php echo isset($options['login_method']) && $options['login_method'] == $key?'selected="selected"':''; ?>><?php echo $value; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                                <label for="pl8app_settings[login_method]">
                                    <?php echo __('This option affects how
                                    login/register options are offered on checkout page.', 'pl8app'); ?></label></td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php echo __('Enforce SSL on Checkout', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[enforce_ssl]" value="-1">
                                <input id="pl8app_settings[enforce_ssl]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[enforce_ssl]"
                                    <?PHP echo !empty($options['enforce_ssl']) ? checked(1, $options['enforce_ssl'], false) : ''; ?>>
                                <label for="pl8app_settings[enforce_ssl]">
                                    <?php echo __('Check this to force users to be redirected to the secure checkout page. You must have an SSL certificate installed to use this option.','pl8app'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php echo __('Enable Cart Saving', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[enable_cart_saving]" value="-1">
                                <input id="pl8app_settings[enable_cart_saving]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[enable_cart_saving]"
                                    <?PHP echo !empty($options['enable_cart_saving']) ? checked(1, $options['enable_cart_saving'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_cart_saving]">
                                    <?php echo __('Check this to enable cart saving on the checkout.','pl8app'); ?>
                                </label>
                                <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" style="display: inline" title="<strong>Cart Saving</strong><br />Cart saving allows shoppers to create a temporary link to their current shopping cart so they can come back to it later, or share it with someone."></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}


/**
 * Printer Settings
 */

function pl8app_printer_settings(){
    $options = get_option('pl8app_settings', array());
    $order_printing_fonts = array(
            array(
               'value' => 'Times, serif',
               'label' => 'Times New'
            ),
        array(
            'value' => 'Georgia, serif',
            'label' => 'Georgia'
        ),
        array(
            'value' => 'Palatino, serif',
            'label' => 'Palatino'
        ),
        array(
            'value' => 'Arial, Helvetica',
            'label' => 'Arial'
        ),
        array(
            'value' => 'Comic Sans MS, cursive, sans-serif',
            'label' => 'Comic Sans'
        ),
        array(
            'value' => 'Lucida Sans Unicode, sans-serif',
            'label' => 'Lucida Sans'
        ),
        array(
            'value' => 'Tahoma, Geneva, sans-serif',
            'label' => 'Tahoma'
        ),
        array(
            'value' => 'Trebuchet MS, Helvetica, sans-serif',
            'label' => 'Trebuchet MS'
        ),
        array(
            'value' => 'Courier New, Courier, monospace',
            'label' => 'Courier New'
        ),
        array(
            'value' => 'Lucida Console, Monaco, monospace',
            'label' => 'Lucida Console'
        ),

    );
    $paper_sizes = array(
        array(
            'value' => '',
            'label' => 'Select Paper Size'
        ),
        array(
            'value'=>'56.9mm',
            'label'=>'57mm x 38mm (Mobile/Small CC Terminals)'
        ),
        array(
            'value'=>'57.0mm',
            'label'=>'57mm x 40mm (Mobile/Small CC Terminals)'
        ),
        array(
            'value'=>'57.1mm',
            'label'=>'57mm x 50mm (Mobile/Small CC Terminals)'
        ),
        array(
            'value'=>'79.9mm',
            'label'=>'80mm x 60mm (Thermal Receipt Printers)'
        ),
        array(
            'value'=>'80.0mm',
            'label'=>'80mm x 70mm (Thermal Receipt Printers)'
        ),
        array(
            'value'=>'80.1mm',
            'label'=>'80mm x 80mm (Thermal Receipt Printers)'
        )
    );
    $printer_setting = new pl8app_Print_Settings();
    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Printer Settings', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields('pl8app_settings'); ?>
                </table>
                <div class="pl8app-wrapper">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <?php echo __('Enable Printing Option', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[enable_printing]" value="-1">
                                <input id="pl8app_settings[enable_printing]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[enable_printing]"
                                    <?PHP echo !empty($options['enable_printing']) ? checked(1, $options['enable_printing'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing]">
                                    <?php echo __('Check this option to enable printing of invoice','pl8app'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Store Logo', 'pl8app');?></th>
                            <td>
                                <input type="text" class="regular-text" id="pl8app_settings[store_logo]"
                                       name="pl8app_settings[store_logo]"
                                       value="<?PHP echo !empty($options['store_logo']) ? $options['store_logo'] : ''; ?>">
                                <span style="display: inline;">&nbsp;<input type="button" class="pl8app_settings_upload_button button-secondary"
                                                                            value="Upload File"></span>
                                <label for="pl8app_settings[store_logo]">
                                    <?php echo __('Select an image to use as the logo in the invoice. Recommended size 280x75.', 'pl8app');?></label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php echo __('Select Order Statuses', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[order_print_status]" value="-1">
                                <input id="pl8app_settings[order_print_status][pending]" value="Pending"
                                       type="checkbox"
                                       name="pl8app_settings[order_print_status][pending]"
                                    <?PHP echo !empty($options['order_print_status']['pending']) ? checked('Pending', $options['order_print_status']['pending'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing][pending]">
                                    <?php echo __('Pending','pl8app'); ?>
                                </label>
                                <br>
                                <input id="pl8app_settings[order_print_status][accepted]" value="Accepted"
                                       type="checkbox"
                                       name="pl8app_settings[order_print_status][accepted]"
                                    <?PHP echo !empty($options['order_print_status']['accepted']) ? checked('Accepted', $options['order_print_status']['accepted'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing][accepted]">
                                    <?php echo __('Accepted','pl8app'); ?>
                                </label>
                                <br>
                                <input id="pl8app_settings[order_print_status][processing]" value="Processing"
                                       type="checkbox"
                                       name="pl8app_settings[order_print_status][processing]"
                                    <?PHP echo !empty($options['order_print_status']['processing']) ? checked('Processing', $options['order_print_status']['processing'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing][processing]">
                                    <?php echo __('Processing','pl8app'); ?>
                                </label>
                                <br>
                                <input id="pl8app_settings[order_print_status][ready]" value="Ready"
                                       type="checkbox"
                                       name="pl8app_settings[order_print_status][ready]"
                                    <?PHP echo !empty($options['order_print_status']['ready']) ? checked('Ready', $options['order_print_status']['ready'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing][ready]">
                                    <?php echo __('Ready','pl8app'); ?>
                                </label>
                                <br>
                                <input id="pl8app_settings[order_print_status][transit]" value="In Transit"
                                       type="checkbox"
                                       name="pl8app_settings[order_print_status][transit]"
                                    <?PHP echo !empty($options['order_print_status']['transit']) ? checked('In Transit', $options['order_print_status']['transit'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing][transit]">
                                    <?php echo __('In Transit','pl8app'); ?>
                                </label>
                                <br>
                                <input id="pl8app_settings[order_print_status][cancelled]" value="Cancelled"
                                       type="checkbox"
                                       name="pl8app_settings[order_print_status][cancelled]"
                                    <?PHP echo !empty($options['order_print_status']['cancelled']) ? checked('Cancelled', $options['order_print_status']['cancelled'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing][cancelled]">
                                    <?php echo __('Cancelled','pl8app'); ?>
                                </label>
                                <br>
                                <input id="pl8app_settings[order_print_status][completed]" value="Completed"
                                       type="checkbox"
                                       name="pl8app_settings[order_print_status][completed]"
                                    <?PHP echo !empty($options['order_print_status']['completed']) ? checked('Completed', $options['order_print_status']['completed'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing][completed]">
                                    <?php echo __('Completed','pl8app'); ?>
                                </label>
                                <br>
                                <input id="pl8app_settings[order_print_status][no_show]" value="Customer No Show"
                                       type="checkbox"
                                       name="pl8app_settings[order_print_status][no_show]"
                                    <?PHP echo !empty($options['order_print_status']['no_show']) ? checked('Customer No Show', $options['order_print_status']['no_show'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_printing][no_show]">
                                    <?php echo __(' Customer No Show','pl8app'); ?>
                                </label>
                                <br>
                                <p class="description"><?php echo __('Select the order statuses for which the print will work.','pl8app'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Printing Font', 'pl8app')?></th>
                            <td>
                                <select id="pl8app_settings[order_printing_font]" name="pl8app_settings[order_printing_font]"
                                        class="" data-placeholder="">
                                    <?php foreach($order_printing_fonts as $row) {?>
                                        <option value="<?php echo $row['value']; ?>" <?php echo isset($options['order_printing_font']) && $options['order_printing_font'] == $row['value']?'selected="selected"':''; ?>><?php echo $row['label']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="pl8app_settings[login_method]">
                                    <?php echo __('Choose the text font for printing.', 'pl8app'); ?></label></td>
                        </tr>

                        <tr>
                            <th scope="row"><?php echo __('Select Paper Size', 'pl8app')?></th>
                            <td>
                                <select id="pl8app_settings[paper_size]" name="pl8app_settings[paper_size]"
                                        class="" data-placeholder="">
                                    <?php foreach($paper_sizes as $row) {?>
                                        <option value="<?php echo $row['value']; ?>" <?php echo isset($options['paper_size']) && $options['paper_size'] == $row['value']?'selected="selected"':''; ?>><?php echo $row['label']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <label for="pl8app_settings[paper_size]">
                                    <?php echo __('Select the paper size that you want to print', 'pl8app'); ?></label></td>
                        </tr>

                        <tr>
                            <th scope="row"><?php echo __('Footer Text', 'pl8app')?></th>
                            <td><?PHP
                                if (!empty($options['footer_area_content'])) {
                                    $value = $options['footer_area_content'];
                                } else {
                                    $value = '';
                                }
                                ob_start();
                                wp_editor(stripslashes($value), 'pl8app_settings_' . esc_attr('footer_area_content'), array('textarea_name' => 'pl8app_settings[' . esc_attr('footer_area_content') . ']', 'textarea_rows' => absint(20)));
                                $html = ob_get_clean();
                                $html .= '<br/><label for="pl8app_settings[' . pl8app_sanitize_key('footer_area_content') . ']"> ' . wp_kses_post('Enter the details you want to show on invoice below the items listing and total price.You can add image and align the content using the editor.') . '</label>';

                                echo $html;
                                ?>
                            </td>
                        </tr>


                        <tr>
                            <th scope="row" class="pl8app-label-section"><h3><?php echo __('Auto Printing Settings','pl8app') ?></h3></th>
                        </tr>

                        <tr>
                            <th scope="row">
                                <?php echo __('Enable Automatic Printing', 'pl8app'); ?>
                            </th>
                            <td>
                                <input type="hidden" name="pl8app_settings[enable_auto_printing]" value="-1">
                                <input id="pl8app_settings[enable_auto_printing]" value="1"
                                       type="checkbox"
                                       name="pl8app_settings[enable_auto_printing]"
                                    <?PHP echo !empty($options['enable_auto_printing']) ? checked(1, $options['enable_auto_printing'], false) : ''; ?>>
                                <label for="pl8app_settings[enable_auto_printing]">
                                    <?php echo __('Check this option to enable automatic printing.','pl8app'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php echo __('Chrome Kiosk Mode Shortcut For AutoPrinting', 'pl8app'); ?>
                            </th>
                            <td>
                                <p><?php echo __('To enable Chrome browser kiosk printing for Auto-Printing, use this ', 'pl8app');?> <a download href="<?php echo PL8_PLUGIN_URL.'assets/chrome/pl8app_Auto_Print.zip'?>"><?php echo __('shortcut', 'pl8app'); ?></a></p>
                                <p><?php echo __('Please ensure the correct printer for order printing is selected in Chrome prior to opening shortcut.');?></p>
                                <p><?php echo __('Close current all chrome browsers opened, then open the shortcut file downloaded.');?></p>
                                <p><?php echo __('For Auto-Printing to work you will have to allow Popups from this site within your Chrome browser.');?></p>
                            </td>
                        </tr>


                        <tr>
                            <th scope="row"><?php echo __('Auto Print Preference', 'pl8app')?></th>
                            <td>
                                <table >
                                    <thead>
                                    <tr>
                                        <th><?php echo __('Per Order Status','pl8app'); ?></th>
                                        <th><?php echo __('Copies per print','pl8app'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pl8app_settings[auto_print][pending][status]" value="-1">
                                            <input id="pl8app_settings[auto_print][pending][status]" value="1"
                                                   type="checkbox"
                                                   name="pl8app_settings[auto_print][pending][status]"
                                                <?PHP echo isset($options['auto_print']['pending']['status']) && $options['auto_print']['pending']['status'] == -1 ? '' : 'checked'; ?>>
                                            <label for="pl8app_settings[auto_print][pending][status]">
                                                <?php echo __('Pending','pl8app'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input id="pl8app_settings[auto_print][pending][copies]"
                                                   value="<?PHP echo !empty($options['auto_print']['pending']['copies']) ? $options['auto_print']['pending']['copies'] : 1; ?>"
                                                   type="number" name="pl8app_settings[auto_print][pending][copies]" min="1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pl8app_settings[auto_print][accepted][status]" value="-1">
                                            <input id="pl8app_settings[auto_print][accepted][status]" value="1"
                                                   type="checkbox"
                                                   name="pl8app_settings[auto_print][accepted][status]"
                                                <?PHP echo isset($options['auto_print']['accepted']['status']) && $options['auto_print']['accepted']['status'] == -1 ? '': 'checked'; ?>>
                                            <label for="pl8app_settings[auto_print][accepted][status]">
                                                <?php echo __('Accepted','pl8app'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input id="pl8app_settings[auto_print][accepted][copies]"
                                                   value="<?PHP echo !empty($options['auto_print']['accepted']['copies']) ? $options['auto_print']['accepted']['copies'] : 1; ?>"
                                                   type="number" name="pl8app_settings[auto_print][accepted][copies]" min="1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pl8app_settings[auto_print][processing][status]" value="-1">
                                            <input id="pl8app_settings[auto_print][processing][status]" value="1"
                                                   type="checkbox"
                                                   name="pl8app_settings[auto_print][processing][status]"
                                                <?PHP echo isset($options['auto_print']['processing']['status']) && $options['auto_print']['processing']['status'] == -1 ? '': 'checked'; ?>>
                                            <label for="pl8app_settings[auto_print][processing][status]">
                                                <?php echo __('Processing','pl8app'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input id="pl8app_settings[auto_print][processing][copies]"
                                                   value="<?PHP echo !empty($options['auto_print']['processing']['copies']) ? $options['auto_print']['processing']['copies'] : 1; ?>"
                                                   type="number" name="pl8app_settings[auto_print][processing][copies]" min="1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pl8app_settings[auto_print][ready][status]" value="-1">
                                            <input id="pl8app_settings[auto_print][ready][status]" value="1"
                                                   type="checkbox"
                                                   name="pl8app_settings[auto_print][ready][status]"
                                                <?PHP echo isset($options['auto_print']['ready']['status']) && $options['auto_print']['ready']['status'] == -1 ? '': 'checked'; ?>>
                                            <label for="pl8app_settings[auto_print][ready][status]">
                                                <?php echo __('Ready','pl8app'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input id="pl8app_settings[auto_print][ready][copies]"
                                                   value="<?PHP echo !empty($options['auto_print']['ready']['copies']) ? $options['auto_print']['ready']['copies'] : 1; ?>"
                                                   type="number" name="pl8app_settings[auto_print][ready][copies]" min="1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pl8app_settings[auto_print][transit][status]" value="-1">
                                            <input id="pl8app_settings[auto_print][transit][status]" value="1"
                                                   type="checkbox"
                                                   name="pl8app_settings[auto_print][transit][status]"
                                                <?PHP echo isset($options['auto_print']['transit']['status']) && $options['auto_print']['transit']['status'] == -1 ? '': 'checked'; ?>>
                                            <label for="pl8app_settings[auto_print][transit][status]">
                                                <?php echo __('In Transit','pl8app'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input id="pl8app_settings[auto_print][transit][copies]"
                                                   value="<?PHP echo !empty($options['auto_print']['transit']['copies']) ? $options['auto_print']['transit']['copies'] : 1; ?>"
                                                   type="number" name="pl8app_settings[auto_print][transit][copies]" min="1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pl8app_settings[auto_print][cancelled][status]" value="-1">
                                            <input id="pl8app_settings[auto_print][cancelled][status]" value="1"
                                                   type="checkbox"
                                                   name="pl8app_settings[auto_print][cancelled][status]"
                                                <?PHP echo isset($options['auto_print']['cancelled']['status']) && $options['auto_print']['cancelled']['status'] == -1 ? '':'checked'; ?>>
                                            <label for="pl8app_settings[auto_print][cancelled][status]">
                                                <?php echo __('Cancelled','pl8app'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input id="pl8app_settings[auto_print][cancelled][copies]"
                                                   value="<?PHP echo !empty($options['auto_print']['cancelled']['copies']) ? $options['auto_print']['cancelled']['copies'] : 1; ?>"
                                                   type="number" name="pl8app_settings[auto_print][cancelled][copies]" min="1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pl8app_settings[auto_print][completed][status]" value="-1">
                                            <input id="pl8app_settings[auto_print][completed][status]" value="1"
                                                   type="checkbox"
                                                   name="pl8app_settings[auto_print][completed][status]"
                                                <?PHP echo isset($options['auto_print']['completed']['status']) && $options['auto_print']['completed']['status'] == -1 ? '' : 'checked'; ?>>
                                            <label for="pl8app_settings[auto_print][completed][status]">
                                                <?php echo __('Completed','pl8app'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input id="pl8app_settings[auto_print][completed][copies]"
                                                   value="<?PHP echo !empty($options['auto_print']['completed']['copies']) ? $options['auto_print']['completed']['copies'] : 1; ?>"
                                                   type="number" name="pl8app_settings[auto_print][completed][copies]" min="1">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pl8app_settings[auto_print][no_show][status]" value="-1">
                                            <input id="pl8app_settings[auto_print][no_show][status]" value="1"
                                                   type="checkbox"
                                                   name="pl8app_settings[auto_print][no_show][status]"
                                                <?PHP echo isset($options['auto_print']['no_show']['status']) && $options['auto_print']['no_show']['status'] == -1 ? '': 'checked'; ?>>
                                            <label for="pl8app_settings[auto_print][no_show][status]">
                                                <?php echo __('Customer No Show','pl8app'); ?>
                                            </label>
                                        </td>
                                        <td>
                                            <input id="pl8app_settings[auto_print][no_show][copies]"
                                                   value="<?PHP echo !empty($options['auto_print']['no_show']['copies']) ? $options['auto_print']['no_show']['copies'] : 1; ?>"
                                                   type="number" name="pl8app_settings[auto_print][no_show][copies]" min="1">
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>

                    </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}
