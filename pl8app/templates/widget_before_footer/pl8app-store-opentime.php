<?php

$store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
$days = pl8app_StoreTiming::pl8app_st_get_weekdays();
?>


    <h3 class="widget-title">Opening Hours</h3>
    <div class="pl8app-store-timings">
        <table class="pl8app-store-timings">
            <tbody>
            <?php
            if ( is_array( $days ) && !empty( $days ) ) :

            foreach( $days as $key => $day ) : ?>
                <tr>
                    <th><?PHP echo $day;?></th>
                    <td>
                        <?php
                        // Open Day Checkbox
                        if (isset($store_timings['open_day'][$day])) :
                            if(isset($store_timings['24hours'][$day])) : ?>
                            <p>24Hours-Open</p>
                            <?php else :
                            if(isset($store_timings['open_time'][$day]) && is_array($store_timings['open_time'][$day]) && count($store_timings['open_time'][$day]) > 0) {
                            foreach ($store_timings['open_time'][$day] as $index => $time) {
                                ?>
                                <p>
                                    <?php
                                    // Open Time
                                    if (!empty($time)) : ?>
                                        <?php echo $time; ?>
                                    <?php else: ?>
                                        Undefined
                                    <?php endif; ?>
                                    -
                                    <?php
                                    // Close Time
                                    if (!empty($store_timings['close_time'][$day][$index])) : ?>
                                        <?php echo $store_timings['close_time'][$day][$index]; ?>
                                    <?php else: ?>
                                        Undefined
                                    <?php endif; ?>
                                </p>
                            <?php }
                            }
                            else{
                                ?>
                                <p>Closed</p>
                                <?PHP
                            }
                            endif;
                            else : ?>
                            <p>Closed</p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
