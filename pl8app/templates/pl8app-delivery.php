<div class="tab-pane fade delivery-settings-wrapper" id="nav-delivery" role="tabpanel" aria-labelledby="nav-delivery-tab">

  <!-- Delivery Time Wrap -->
  <div class="pl8app-delivery-time-wrap pl8app-time-wrap">

    <?php do_action( 'pl8app_before_service_time', 'delivery' ); ?>

    <?php

    if ( pl8app_is_service_enabled( 'delivery' ) ) :

      $store_times        = pla_get_store_timings();
      $store_timings      = apply_filters( 'pl8app_store_delivery_timings', $store_times );
      $store_time_format  = pl8app_get_option( 'store_time_format' );
      $time_format = 'H:i';
      $time_format        = apply_filters( 'pl8app_store_time_format', $time_format, $store_time_format);

      ?>
      <div class="delivery-time-text">
        <?php echo apply_filters( 'pl8app_delivery_time_string', esc_html_e( 'Select a delivery time', 'pl8app' ) ); ?>
      </div>

  		<select class="pl8app-delivery pl8app-allowed-delivery-hrs pl8app-hrs pl8app-form-control" id="pl8app-delivery-hours" name="pl8app_allowed_hours">
  		  <?php
        if( is_array( $store_timings ) ) :
          foreach( $store_timings as $time ) :
            $loop_time = date( $time_format, $time ); ?>
            <option value='<?php echo $loop_time; ?>'><?php echo $loop_time; ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
  		</select>
    <?php endif; ?>

    <?php do_action( 'pl8app_after_service_time', 'delivery' ); ?>

  </div>
	<!-- Delivery Time Wrap Ends -->

</div>