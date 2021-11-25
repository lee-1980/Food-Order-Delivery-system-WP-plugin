<div class="tab-pane fade delivery-settings-wrapper" id="nav-pickup" role="tabpanel" aria-labelledby="nav-pickup-tab">

  <!-- Pickup Time Wrap -->
  <div class="pl8app-pickup-time-wrap pl8app-time-wrap">

    <?php do_action( 'pl8app_before_service_time', 'pickup' ); ?>

    <?php

    if ( pl8app_is_service_enabled( 'pickup' ) ) :

      $store_times        = pla_get_store_timings();
      $store_timings      = apply_filters( 'pl8app_store_pickup_timings', $store_times );
      $store_time_format  = pl8app_get_option( 'store_time_format' );
      $time_format        = ! empty( $store_time_format ) && $store_time_format == '24hrs' ? 'H:i' : 'h:ia';
      $time_format        = apply_filters( 'pl8app_store_time_format', $time_format, $store_time_format );
      ?>

      <div class="pickup-time-text">
        <?php echo apply_filters( 'pl8app_pickup_time_string', esc_html_e( 'Select a pickup time', 'pl8app' ) ); ?>
      </div>

      <select class="pl8app-pickup pl8app-allowed-pickup-hrs pl8app-hrs pl8app-form-control" id="pl8app-pickup-hours" name="pl8app_allowed_hours">
        <?php
        if( is_array( $store_timings ) ) :
          foreach( $store_timings as $time ) :
            $loop_time = date( $time_format, $time ); ?>
            <option value='<?php echo $loop_time; ?>'><?php echo $loop_time; ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>

    <?php endif; ?>

    <?php do_action( 'pl8app_after_service_time', 'pickup' ); ?>

	</div>
	<!-- Pickup Time Wrap Ends -->

</div>