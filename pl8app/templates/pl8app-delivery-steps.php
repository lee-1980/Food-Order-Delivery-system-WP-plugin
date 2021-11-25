<?php

global $pl8app_options;

$service_type = pl8app_get_option('enable_service', array());
$services = array();
$service_type_name = '';

if(!empty($service_type['delivery'])) {
    array_push($services, 'delivery');
    $service_type_name = 'delivery';
}
if(!empty($service_type['pickup'])) {
    array_push($services, 'pickup');
    $service_type_name = 'pickup';
}

if(!empty($service_type['pickup']) && !empty($service_type['delivery'])){
    $service_type_name = 'delivery_and_pickup';
}

$store_times = pla_get_store_timings();
$store_times = apply_filters('pl8app_store_delivery_timings', $store_times);

//If empty check if pickup hours are available
if (empty($store_times) && !is_array($store_times)) {
    $store_times = apply_filters('pl8app_store_pickup_timings', $store_times);
}

$closed_message = pl8app_get_option('store_closed_msg', __('Sorry, we are closed for ordering now.', 'pl8app'));

?>

<div class="pl8app-delivery-wrap">

    <?php if (empty($store_times) || !is_array($store_times)) : ?>
        <div class="alert alert-warning">
            <?php echo $closed_message; ?>
        </div>
    <?php else: ?>

        <div class="pl8app-row">

            <!-- Error Message Starts Here -->
            <div class="alert alert-warning pl8app-errors-wrap disabled"></div>
            <!-- Error Message Ends Here -->

            <?php do_action('pl8app_delivery_location_field'); ?>

            <div class="pl8app-tabs-wrapper pl8app-delivery-options text-center service-option-<?php echo $service_type_name; ?>">

                <ul class="nav nav-pills" id="pl8appdeliveryTab">

                    <?php foreach ($services as $service) : ?>

                        <!-- Service Option Starts Here -->
                        <li class="nav-item">
                            <a class="nav-link single-service-selected" id="nav-<?php echo $service; ?>-tab"
                               data-service-type="<?php echo $service; ?>" data-toggle="tab"
                               href="#nav-<?php echo $service; ?>" role="tab"
                               aria-controls="nav-<?php echo $service; ?>" aria-selected="false">
                                <?php echo pl8app_service_label($service); ?>
                            </a>
                        </li>
                        <!-- Service Option Ends Here -->

                    <?php endforeach; ?>
                </ul>

                <div class="tab-content" id="pl8app-tab-content">
                    <?php
                    foreach ($services as $service) {
                        pl8app_get_template_part('pl8app', $service);
                    }
                    ?>
                    <button type="button" data-menu-id='{menuitem_id}'
                            class="btn btn-primary btn-block pl8app-delivery-opt-update">
                        <?php esc_html_e('Update', 'pl8app'); ?></button>
                </div>

            </div>
        </div>
    <?php endif; ?>
</div>