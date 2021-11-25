<?php
/**
 * Menu Item general data panel.
 *
 * @package pl8app/Admin
 */

defined('ABSPATH') || exit;
$has_variable_prices = $menuitem_object->has_variable_prices();
$type = $menuitem_object->get_type();

$options = get_option('pl8app_settings');
$taxes = isset($options['tax']) && is_array($options['tax'])? $options['tax'] : array();
$menuitem_vat = $menuitem_object->get_vat();

global $wpdb;

$bundled_products = $menuitem_object->get_bundled_menuitems();

?>
<div id="general_menuitem_data" class="panel pl8app_options_panel pl8app-metaboxes-wrapper">
    <div class="pl8app-metabox-container">
        <div class="toolbar toolbar-top">
			<span class="pl8app-toolbar-title">
				<?php esc_html_e('Item Details', 'pl8app'); ?>
			</span>
            <span class="alignright">
                <?php esc_html_e('Menu Item Type — ', 'pl8app'); ?>
                <select name="_pl8app_product_type">
                <?PHP foreach (pl8app_get_menuitem_types() as $key => $pl8app_get_menuitem_type) { ?>
                    <option value="<?PHP echo $key; ?>" <?PHP selected($menuitem_object->get_type(), $key) ?>><?PHP echo $pl8app_get_menuitem_type; ?></option>
                <?PHP } ?>
                </select>
            </span>
        </div>
        <div class="options_group pricing">
            <div class="pl8app-tab-content">

                <?php
                // Veg / Non Veg Option
                //				pl8app_radio(
                //					array(
                //						'id'        => 'pl8app_menu_type',
                //						'value'     => $menuitem_object->get_type(),
                //						'label'     => __( 'Menu Item Type', 'pl8app' ),
                //						'options' 	=> pl8app_get_menuitem_types(),
                //						'wrapper_class' => 'admin_vegan_radio',
                //					)
                //				);
                // Variable Pricing
                //				pl8app_checkbox(
                //					array(
                //						'id'          => '_variable_pricing',
                //						'label'       => __( 'Variable pricing', 'pl8app' ),
                //						'description' => __( 'Check this box if the menu has multiple options and you want to specify price for different options.', 'pl8app' ),
                //						'value'       => $has_variable_prices ? 'yes' : 'no',
                //					)
                //				);
                //
                //				pl8app_text_input(
                //					array(
                //						'id' => 'pl8app_variable_price_label',
                //						'value' => get_post_meta( $menuitem_object->ID, 'pl8app_variable_price_label', true),
                //						'label' => __( 'Price Label', 'pl8app' ),
                //						'wrapper_class' => $has_variable_prices ? 'pl8app-variable-prices' : 'pl8app-variable-prices hidden',
                //					)
                //				);

                pl8app_text_input(
                    array(
                        'id' => 'pl8app_price',
                        'value' => $menuitem_object->get_price(),
                        'label' => __('Price', 'pl8app') . ' (' . pl8app_currency_symbol() . ')',
                        'wrapper_class' => $type == 'bundle' ? 'hidden' : '',
                        'data_type' => 'price',
                    )
                );
                ?>
                <p class="form-field pl8app_menuitem_vat_field <?PHP echo $type == 'bundle' ? 'hidden' : ''; ?>">
                    <label for="pl8app_menuitem_vat">Apply Tax (%) </label>
                    <select type="text" class="pl8app-input" name="pl8app_menuitem_vat" id="pl8app_menuitem_vat" >
                        <option value=""> Zero rate (no tax) </option>
                        <?PHP if(isset($options['tax']) && is_array($options['tax'])) {
                        foreach($options['tax'] as $key => $tax) {?>
                            <option value="<?PHP echo $key; ?>" <?PHP echo selected($menuitem_vat, $key); ?>> <?PHP echo !empty($tax['rate'])? $tax['name']: ''; ?> (<?PHP echo !empty($tax['rate'])? $tax['rate'].'%': ''; ?>) <?PHP echo !empty($tax['desc'])? $tax['desc']: ''; ?> </option>
                        <?PHP } } ?>
                    </select>
                    <span style="margin-left: 10px;">
                        <?php
                        $url = admin_url( 'admin.php?page=pl8app-financial-settings' );
                        echo sprintf( __( 'Edit the taxes in <a href="%1$s" target="_blank">Financial Settings</a>', 'pl8app' ), esc_url( $url ) );
                        ?>
                    </span>
                </p>
                <div class="form-field pl8app_bundled_field <?PHP echo $type == 'bundle' ? '' : 'hidden'; ?>">
                    <div style="position: relative;">
                        <?php
                        pl8app_text_input(
                            array(
                                'id' => '_pl8app_bundled_discount',
                                'value' => $menuitem_object->get_bundle_discount(),
                                'label' => __('Discount amount', 'pl8app') . ' (' . pl8app_currency_symbol() . ')',
                                'wrapper_class' => '',
                                'data_type' => 'price',
                            )
                        );
                        ?>
                        <div style="position: absolute; top:8px;">
                            <div>
                            <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help"
                                  title="Bundle fixed price discount for total of items in selection"></span>
                            </div>
                        </div>
                        <span class="discount-error hidden">Invalid! discount is over the limit(<span class="bundle-total"></span>).</span>

                    </div>


                    <div class="pl8app-add-menuitem-to-purchase aa inside add">
                        <h4 for="_pl8app_bundled_products">Add item to bundled list</h4>
                        <ul>
                            <li class="menuitem">
        				<span class="pl8app-payment-details-label-mobile">
        					<?php printf(_x('Select %s To Add', 'payment details select item to add - mobile', 'pl8app'), pl8app_get_label_singular()); ?>
        				</span>
                                <?php echo PL8PRESS()->html->product_dropdown(array(
                                    'name' => 'pl8app-nobundle-menuitem-select',
                                    'id' => 'pl8app-nobundle-menuitem-select',
                                    'chosen' => true,
                                    'bundles' => false,
                                    'exclude' => array($menuitem_object->ID)
                                )); ?>
                            </li>

                            <li class="pl8app-add-menuitem-to-purchase-actions actions">
						<span class="pl8app-payment-details-label-mobile">
							<?php _e('Actions', 'pl8app'); ?>
						</span>
                                <a href="" id="pl8app-bundle-add-menuitem"
                                   class="button button-secondary"><?php printf(__('Add New %s', 'pl8app'), pl8app_get_label_singular()); ?></a>
                            </li>
                        </ul>
                    </div>
                    <div class="pl8app-add-menuitem-to-purchase aa inside list">
                        <h4 for="_pl8app_bundled_products">Bundled Items</h4>
                        <ul>
                            <?php foreach ($bundled_products as $bundled_id) {
                                $bundled_item = new pl8app_Menuitem($bundled_id); ?>
                            <li style="visibility: visible;">
                                <span class="move"></span>
                                <span class="item_id" aria-label="Default">
                                    <input type="hidden" name="_pl8app_bundled_products[]" value="<?PHP echo $bundled_id; ?>" >
                                </span>
                                <span class="data" data-price="<?PHP echo $bundled_item->get_price(); ?>">
                                    <span class="name"><?PHP echo $bundled_item->post_title; ?></span>
                                    <span class="info">
                                        <?PHP echo pl8app_price($bundled_id); ?>
                                    </span>
                                </span>
                                <span class="type">
                                    <a href="<?PHP echo get_edit_post_link($bundled_id); ?>" target="_blank">View item<br>#<?PHP echo $bundled_id; ?></a>
                                </span>
                                <span class="remove hint--left" aria-label="Remove">×</span>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>

            </div>
            <?php do_action('pl8app_menuitem_pricing'); ?>
        </div>
    </div>
    <?php do_action('pl8app_menuitem_options_general_item_details'); ?>
    <div class="pl8app-metabox-container">
        <div class="toolbar toolbar-top">
			<span class="pl8app-toolbar-title">
				<?php esc_html_e('Show or Hide on certain timing', 'pl8app'); ?>
			</span>
        </div>
        <div class="options_group pricing">
            <?php
            $days = pl8app_StoreTiming::pl8app_st_get_weekdays();
            $menuitem_timings = get_post_meta($menuitem_object->ID, 'pl8app_menuitem_timing', true);
            ?>
            <div id="pl8app_store_timings" class="pl8app_store_timings menu_available_time">
                <?php
                if (is_array($days) && !empty($days)) :

                    foreach ($days as $key => $day) : ?>
                        <div class="pl8app_store_timings_day">
                            <div class="pl8app_st_days_name"><?php echo $day; ?></div>
                            <div class="pl8app_st_days_checkbox">
                                <label for="<?php echo $day; ?>" class="store_timing_checkbox_wrapper">
                                    <?php
                                    // Open Day Checkbox
                                    if (isset($menuitem_timings['open_day'][$day])) : ?>
                                        <input type="checkbox" id="<?php echo $day; ?>" class="st_checkbox"
                                               name="<?php echo "pl8app_menuitem_timing[open_day][$day]" ?>"
                                               checked="checked"
                                               value="enable">
                                    <?php else : ?>
                                        <input type="checkbox" class="st_checkbox"
                                               name="<?php echo "pl8app_menuitem_timing[open_day][$day]" ?>"
                                               id="<?php echo $day; ?>"
                                        >
                                    <?php endif; ?>
                                    <span class="st_checkbox_slider round"></span>

                                </label>
                                <span class="checkbox_stat_label open <?php echo isset($menuitem_timings['open_day'][$day]) ? "" : "hidden" ?>">Open</span>
                                <span class="checkbox_stat_label closed <?php echo isset($menuitem_timings['open_day'][$day]) ? "hidden" : "" ?>">Closed</span>
                            </div>
                            <?PHP
                            if (isset($menuitem_timings['open_time'][$day]) && is_array($menuitem_timings['open_time'][$day]) && count($menuitem_timings['open_time'][$day]) > 0) :
                                ?>
                                <div class="st_day_timetable_wrapper <?PHP echo !empty($menuitem_timings['open_day'][$day]) ? "" : "hidden"; ?>"
                                     data-day="<?PHP echo $day; ?>">
                                    <div class="st_day_timetable">
                                        <?php

                                        $length = count($menuitem_timings['open_time'][$day]);
                                        $i = 0;
                                        foreach ($menuitem_timings['open_time'][$day] as $index => $time) {
                                            ?>
                                            <div class="st_day_time">
                                                <div class="pl8app_st_open_time">
                                                    <?php
                                                    // Open Time
                                                    if (!empty($time)) : ?>
                                                        <input type="text" class="pl8app storetime"
                                                               name="<?php echo "pl8app_menuitem_timing[open_time][$day][$i]" ?>"
                                                               value="<?php echo $time; ?>">
                                                    <?php else: ?>
                                                        <input type="text" class="pl8app storetime"
                                                               name="<?php echo "pl8app_menuitem_timing[open_time][$day][$i]; " ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="pass_stion">-</div>
                                                <div class="pl8app_st_close_time">
                                                    <?php if (!empty($menuitem_timings['close_time'][$day][$i])) : ?>
                                                        <input type="text" class="pl8app storetime"
                                                               name="<?php echo "pl8app_menuitem_timing[close_time][$day][$i]" ?>"
                                                               value="<?php echo $menuitem_timings['close_time'][$day][$i]; ?>">
                                                    <?php else: ?>
                                                        <input type="text" class="pl8app storetime"
                                                               name="<?php echo "pl8app_menuitem_timing[close_time][$day][$i];" ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="st_day_time_remove <?php echo $length - 1 >= $i ? '' : 'hidden'; ?>">
                                <span class="st_remove_time_icon">
                                    <span class="Ce1Y1c" style="top: -12px">
                                        <svg xmlns="https://www.w3.org/2000/svg" width="24" height="24"
                                             viewBox="0 0 24 24">
                                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
                                            <path d="M0 0h24v24H0z" fill="none"></path>
                                        </svg>
                                    </span>
                                </span>
                                                </div>
                                            </div>
                                            <?php $i++;
                                        } ?>
                                    </div>
                                    <div class="st_day_time_add <?php echo !empty($menuitem_timings['open_time'][$day][$i - 1]) && !empty($menuitem_timings['close_time'][$day][$i - 1]) ? '' : 'hidden'; ?>">
                                        <span class="st_day_time_add_new_line"><span>Add hours</span></span>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="st_day_timetable_wrapper hidden" data-day="<?PHP echo $day; ?>">
                                    <div class="st_day_timetable">
                                        <div class="st_day_time">
                                            <div class="pl8app_st_open_time">
                                                <input type="text" class="pl8app storetime"
                                                       name="<?php echo "pl8app_menuitem_timing[open_time][$day][0]"; ?>">
                                            </div>
                                            <div class="pass_stion">-</div>
                                            <div class="pl8app_st_close_time">
                                                <input type="text" class="pl8app storetime"
                                                       name="<?php echo "pl8app_menuitem_timing[close_time][$day][0]"; ?>">
                                            </div>
                                            <div class="st_day_time_remove hidden">
                                    <span class="st_remove_time_icon">
                                        <span class="Ce1Y1c" style="top: -12px">
                                            <svg xmlns="https://www.w3.org/2000/svg" width="24" height="24"
                                                 viewBox="0 0 24 24">
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
    </div>

    <?php do_action('pl8app_menuitem_options_general_data'); ?>
</div>
