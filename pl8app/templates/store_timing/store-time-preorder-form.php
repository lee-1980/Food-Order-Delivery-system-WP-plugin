<div class="delivery-time-wrapper">
  <div class="delivery-time-text text-left">
    <?php _e('Select order date', 'pl8app-store-timing'); ?>
  </div>

  <?php
  $store_timing   = new pl8app_StoreTiming_Functions();
  $preorder_dates = $store_timing->get_service_dates();

  $date_range     = !empty( $preorder_dates ) ? $preorder_dates : array();

  if ( is_array( $date_range ) && !empty( $date_range ) ) : ?>

    <select class="pl8app_get_delivery_dates pl8app-form-control">
      <?php foreach( $date_range['formatted_date'] as $key => $date ) : ?>
        <option value="<?php echo $date_range['raw_date'][$key]; ?>" > <?php echo $date; ?></option>
      <?php endforeach; ?>
    </select>

  <?php endif; ?>

</div>