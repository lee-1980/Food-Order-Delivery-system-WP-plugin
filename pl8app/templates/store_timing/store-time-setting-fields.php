<?php
/**
 * The Template for displaying Store Time Settings
 *
 * @package pl8app_StoreTiming/Templates
 * @version 1.2
 */

defined( 'ABSPATH' ) || exit;

$store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();

?>
<div class="pl8app-wrapper">
  <h3>
    <?php esc_html_e( 'Store Timing Settings', 'pl8app-store-timing' ); ?>
  </h3>
  <!-- Enable Store Timings Starts Here -->
  <span class="pl8app-st-description">
      <?php
      echo sprintf( __( 'The store timing depends on your WordPress timezone. To review your timezone settings ', 'pl8app-store-timing' ));
      ?>
      <select id="timezone_string" name="timezone_string" aria-describedby="timezone-description">

          <?php

          $current_offset = get_option( 'gmt_offset' );
          $tzstring       = get_option( 'timezone_string' );

          $check_zone_info = true;

          // Remove old Etc mappings. Fallback to gmt_offset.
          if ( false !== strpos( $tzstring, 'Etc/GMT' ) ) {
              $tzstring = '';
          }

          if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists.
              $check_zone_info = false;
              if ( 0 == $current_offset ) {
                  $tzstring = 'UTC+0';
              } elseif ( $current_offset < 0 ) {
                  $tzstring = 'UTC' . $current_offset;
              } else {
                  $tzstring = 'UTC+' . $current_offset;
              }
          }

          echo wp_timezone_choice($tzstring, get_user_locale());

          ?>
      </select>
  </span>
  <!-- Enable Store Timings Ends Here -->

  <!-- Set Time Format Starts Here -->
  <?php
    $date_format = isset( $store_timings['date_format'] ) ? $store_timings['date_format'] : '';
  ?>

  <h4 class="pl8app-label-section"><?php esc_html_e( '--- Date Format ---', 'pl8app-store-timing' ); ?></h4>
  <fieldset>
    <label>
        <input type="radio" name="pl8app_store_timing[date_format]" value="F j, Y" <?php echo $date_format == 'F j, Y'? "checked":""; ?>>
        <span class="date-time-text format-i18n" style="display: inline-block;">February 27, 2021</span>
        <code>F j, Y</code>
    </label>
    <br>
    <label>
        <input type="radio" name="pl8app_store_timing[date_format]" value="Y-m-d" <?php echo $date_format == 'Y-m-d'? "checked":""; ?>>
        <span class="date-time-text format-i18n" style="display: inline-block;">2021-02-27</span>
        <code>Y-m-d</code>
    </label>
    <br>
    <label>
        <input type="radio" name="pl8app_store_timing[date_format]" value="m/d/Y" <?php echo $date_format == 'm/d/Y'? "checked":""; ?>>
        <span class="date-time-text format-i18n" style="display: inline-block;">02/27/2021</span>
        <code>m/d/Y</code>
    </label>
    <br>
    <label>
        <input type="radio" name="pl8app_store_timing[date_format]" value="d/m/Y" <?php echo $date_format == 'd/m/Y'? "checked":""; ?>>
        <span class="date-time-text format-i18n" style="display: inline-block;">27/02/2021</span>
        <code>d/m/Y</code>
    </label>
    </fieldset>
  <!-- Set Time Format Ends Here -->

  <!-- Store Days Open Starts Here -->
  <h4 class="pl8app-label-section"><?php esc_html_e( '--- Store open/close days ---', 'pl8app-store-timing' ); ?></h4>
  <span><?php esc_html_e( 'Tick the days on which the store is open and then enter the open/close and break time for that day.' ); ?></span>
  <span class="pl8app-notice"><?php esc_html_e( 'This will override the default store open/close time under service options.', 'pl8app-store-timing' ); ?></span>

  <?php
    $days = pl8app_StoreTiming::pl8app_st_get_weekdays();
  ?>
  <div id="pl8app_store_timings" class="pl8app_store_timings store_open_time">
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
                                   name="<?php echo "pl8app_store_timing[open_day][$day]" ?>" checked="checked"
                                   value="enable">
                        <?php else : ?>
                            <input type="checkbox" class="st_checkbox"
                                   name="<?php echo "pl8app_store_timing[open_day][$day]" ?>" id="<?php echo $day; ?>"
                                   >
                        <?php endif; ?>
                        <span class="st_checkbox_slider round"></span>
                    </label>
                    <span class="checkbox_stat_label open <?php echo isset($store_timings['open_day'][$day])?"":"hidden"?>">Open</span>
                    <span class="checkbox_stat_label closed <?php echo isset($store_timings['open_day'][$day])?"hidden":""?>">Closed</span>

                </div>
                <?PHP
                if (!empty($store_timings['open_day'][$day])) :
                    ?>
                    <div class="st_day_timetable_wrapper" data-day="<?PHP echo $day;?>" >
                    <span class="checkbox_stat_label 24hours"><input type="checkbox" name="<?php echo "pl8app_store_timing[24hours][$day]" ?>" <?PHP echo isset($store_timings['24hours'][$day])? 'checked="checked" value="enabled"':''?>> 24Hours-Open</span>
                    <div class="st_day_timetable <?PHP echo isset($store_timings['24hours'][$day])? "hidden":""?>" >
                    <?php
                    if(isset($store_timings['open_time'][$day]) && is_array($store_timings['open_time'][$day]) && count($store_timings['open_time'][$day]) > 0) {
                        $length = count($store_timings['open_time'][$day]);
                        $i = 0;
                        foreach ($store_timings['open_time'][$day] as $index => $time) {
                        ?>
                        <div class="st_day_time">
                            <div class="pl8app_st_open_time">
                                <?php
                                // Open Time
                                if (!empty($time)) : ?>
                                    <input type="text" class="pl8app storetime"
                                           name="<?php echo "pl8app_store_timing[open_time][$day][$i]" ?>"
                                           value="<?php echo $time; ?>">
                                <?php else: ?>
                                    <input type="text" class="pl8app storetime"
                                           name="<?php echo "pl8app_store_timing[open_time][$day][$i]; " ?>">
                                <?php endif; ?>
                            </div>
                            <div class="pass_stion">-</div>
                            <div class="pl8app_st_close_time">
                                <?php if (!empty($store_timings['close_time'][$day][$i])) : ?>
                                    <input type="text" class="pl8app storetime"
                                           name="<?php echo "pl8app_store_timing[close_time][$day][$i]" ?>"
                                           value="<?php echo $store_timings['close_time'][$day][$i]; ?>">
                                <?php else: ?>
                                    <input type="text" class="pl8app storetime"
                                           name="<?php echo "pl8app_store_timing[close_time][$day][$i];" ?>">
                                <?php endif; ?>
                            </div>
                            <div class="st_day_time_remove <?php echo $length - 1 >= $i?'':'hidden'; ?>">
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
                    <?php $i++; } } ?>
                        </div>
                        <div class="st_day_time_add <?PHP echo isset($store_timings['24hours'][$day])? "hidden":""; ?>">
                            <span class="st_day_time_add_new_line"><span>Add hours</span></span>
                        </div>
                        </div>
                    <?php else : ?>
                    <div class="st_day_timetable_wrapper hidden" data-day="<?PHP echo $day;?>">
                        <span class="checkbox_stat_label 24hours"><input type="checkbox" name="<?php echo "pl8app_store_timing[24hours][$day]" ?>"> 24Hours-Open</span>
                        <div class="st_day_timetable">
                            <div class="st_day_time">
                                <div class="pl8app_st_open_time">
                                    <input type="text" class="pl8app storetime"
                                           name="<?php echo "pl8app_store_timing[open_time][$day][0]"; ?>">
                                </div>
                                <div class="pass_stion">-</div>
                                <div class="pl8app_st_close_time">
                                    <input type="text" class="pl8app storetime"
                                           name="<?php echo "pl8app_store_timing[close_time][$day][0]"; ?>">
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
    <!-- Store Days Open Ends Here -->

    <!-- Holiday Settings Starts Here -->
    <div class="pl8app-holidays settings-row">
      <h4 class="pl8app-label-section"><?php esc_html_e( '--- Holidays ---', 'pl8app-store-timing' ); ?></h4>
      <div class="holidays-wrap">
        <div class="holidays-lists-single-wrap child">
        <?php if( isset( $store_timings['holiday'] ) ) : ?>
          <?php if( is_array( $store_timings['holiday'] ) ) : ?>

            <?php foreach( $store_timings['holiday'] as $key => $store_holiday ) :
            ?>
              <div class="holidays-single-list" data-row="<?php echo $key; ?>">
                <input type="text" class="pl8app-holiday-date" data-name="pl8app_store_timing[holiday][<?php echo $key; ?>]" name="pl8app_store_timing[holiday][<?php echo $key; ?>]" value="<?php echo $store_timings['holiday'][$key]; ?>">
                <button class="button button-primary pl8app-remove-holiday"><?php esc_html_e( 'Remove', 'pl8app-store-timing' ); ?></button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        <?php endif; ?>
        </div>
        <button class="button button-primary pl8app-add-holiday"><?php esc_html_e( 'Add Holiday', 'pl8app-store-timing' ); ?></button>
      </div>
    </div>
    <!-- Holiday Settings Ends Here -->

  </div>
