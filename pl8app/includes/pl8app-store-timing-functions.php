<?php
/**
 * pl8app_StoreTiming_Functions
 *
 * @package pl8app_StoreTiming_Functions
 * @since 1.2
 */

defined('ABSPATH') || exit;

class pl8app_StoreTiming_Functions
{

    public function __construct()
    {

        add_filter('pl8app_store_delivery_timings', array($this, 'get_delivery_timings'));

        add_filter('pl8app_store_time_format', array($this, 'set_timestamp_formate'), 10, 2);

        add_filter('pl8app_store_pickup_timings', array($this, 'get_pickup_hrs'));

        add_filter('pl8app_date_format', array($this, 'set_date_format'), 10);

        add_action('pl8app_before_service_time', array($this, 'render_preorder_dates'), 10);

        add_action('wp_enqueue_scripts', array($this, 'pl8app_st_scripts'));

        add_action('wp_ajax_pl8app_st_render_timings', array($this, 'pl8app_st_render_timings'));

        add_action('wp_ajax_nopriv_pl8app_st_render_timings', array($this, 'pl8app_st_render_timings'));

        add_filter('pl8app_timing_for_today', array($this, 'pl8app_store_timings_for'), 10);

        add_action('pl8app_add_email_tags', array($this, 'set_delivery_date_tag'), 10, 1);
    }

    /**
     * Get the store closed and open day numbers
     *
     * @return array List of store closed and open day numbers
     * @since 1.0
     */
    public static function get_store_open_closed_days_number()
    {

        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $store_days = $store_timings['open_day'];
        $enable_days = array_keys($store_days);
        $closed_days = array();
        $open_days = array();

        $days = pl8app_StoreTiming::pl8app_st_get_weekdays();

        foreach ($days as $day) {
            if (!in_array($day, $enable_days)) {
                $closed_days[] = $day;
            } else {
                $open_days[] = $day;
            }
        }

        return $response = array(
            'closed_days' => $closed_days,
            'open_days' => $open_days,
        );

        return $response;
    }


    /**
     * Get Service Dates
     *
     * @return array list of dates
     * @since 1.0
     */
    public function get_service_dates()
    {

        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $preorder_day = $this->get_order_until();

        //If pre-order is disabled or pre-order days not set then we don't need to run this
        if (empty($store_timings['pre_order_range']) || empty($preorder_day)) return array();

        $date_range = $formatted_date = $raw_date = [];
        $holidays = $this->get_holidays();


        $date_format = isset($store_timings['date_format']) ? $store_timings['date_format'] : '';

        if (empty($date_format)) {
            $date_format = get_option('date_format', true);
        }

        //If store is closed today then display dates from tomorrow
        if ($this->is_store_closed()) {
            $current_date = date('Y-m-d', strtotime('+1 days'));
            $preorder_day = $preorder_day + 1;

            $pre_order_date = date('Y-m-d', strtotime('+' . $preorder_day . ' days'));
        } else {
            $current_date = current_time('Y-m-d');
            $pre_order_date = date('Y-m-d', strtotime('+' . $preorder_day . ' days'));
        }


        //Create date range from the selected pre-order range
        $date_range = $this->createDateRange($current_date, $pre_order_date);

        if (is_array($holidays) && !empty($holidays)) {
            $date_range = array_diff($date_range, $holidays);
        }

        //Prepare the range array
        foreach ($date_range as $date) {
            $new_date = pl8app_local_date($date);
            $formatted_date[] = $new_date;
            $raw_date[] = $date;
        }

        //Prepare the date ranges for the dropdown
        $date_ranges = array(
            'formatted_date' => $formatted_date,
            'raw_date' => $raw_date,
        );
        return $date_ranges;
    }


    /**
     * Get date range from start and end date
     *
     * @return array list of array dates
     * @since 1.0
     */
    public function createDateRange($startDate, $endDate, $format = 'Y-m-d')
    {

        //Set the begining and and end date
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);

        //Interval of 1 day
        $interval = new DateInterval('P1D'); // 1 Day
        $date_range = new DatePeriod($begin, $interval, $end);

        $range = [];

        $closed_days = self::get_store_open_closed_days_number();
        $closed_days = $closed_days['closed_days'];

        foreach ($date_range as $date) {
            $formatted_date = $date->format($format);

            if (is_array($closed_days) && !empty($closed_days)) {

                $date_number = date('l', strtotime($formatted_date));
                if (!in_array($date_number, $closed_days)) {
                    $range[] = $formatted_date;
                }
            } else {
                $range[] = $formatted_date;
            }
        }

        return $range;
    }

    /**
     * Get final delivery timings
     *
     * @return array TimeStamps for all holidays
     * @since 1.2
     */
    public function get_delivery_timings($store_times)
    {

        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $options = get_option('pl8app_settings');
        $delcut_timings = isset($options['delivery_timing'])? $options['delivery_timing']: array();

        if (!isset($store_timings['open_day']) || !(is_array($store_timings['open_day']) && count($store_timings['open_day']) > 0))
            return false;

        // if pre-orders is enabled then use the default pre-order method to get the time
        if (!empty($store_timings['pre_order_range'])) {
            $dates = $this->get_service_dates();
            $first_day = $dates['raw_date'][0];

            if (is_null($first_day)) {
                return false;
            }
            $getDaynumber = $this->get_weekday_number($first_day);
            $deliveryHours = $this->get_weekday_delivery_hrs($first_day, $getDaynumber, $format = 'timestamp');
            return $deliveryHours;
        }
        $store_hours = $this->get_store_open_close_hours();
        $close_time = isset($store_hours['close_time']) && is_array($store_hours['close_time']) ? end($store_hours['close_time']) : '';
        $start_time = isset($store_hours['open_time']) && is_array($store_hours['open_time']) ? current($store_hours['close_time']) : '';
        $close_unix_time = strtotime($close_time);
        $current_time = strtotime(current_time('H:i'));
        $current_date = current_time('Y-m-d');

        if ($current_time > $close_unix_time) {

            if (empty($store_timings['pre_order_range'])) return [];
            // If pre-order is not enabled then display the ordering closed.
            $next_open_day = $this->get_next_open_day();

            $next_date = date('Y-m-d', strtotime(' +' . $next_open_day . ' day'));

            $is_holiday = $this->is_holiday_today($next_date);

            if (!$is_holiday) {
                $get_weekday_number = $this->get_weekday_number($next_date);
                $store_times = $this->get_weekday_delivery_hrs($next_date, $get_weekday_number, $format = 'timestamp');
                $get_store_open_hrs = $store_times;
            }

        } else {

            $get_store_open_hrs = $this->get_pickup_hrs($store_timings);
            $day_number = $this->get_weekday_number($current_date);
            $time_interval = self::store_time_interval();
            $cutoff_hours = array();

            if (!isset($store_timings['open_day'][$day_number]))
                return '';

            $deopen_time = isset($delcut_timings['open_time'][$day_number]) ? $delcut_timings['open_time'][$day_number] : '';
            $declose_time = isset($delcut_timings['close_time'][$day_number]) ? $delcut_timings['close_time'][$day_number] : '';

            if (!empty($deopen_time) && !empty($declose_time) && is_array($deopen_time) && is_array($declose_time)) {
                foreach ($deopen_time as $index => $deopen_time_row) {
                    if (!empty($deopen_time_row) && !empty($declose_time[$index])) {
                        $cutoff_hours = array_merge($cutoff_hours, range(strtotime($deopen_time_row), strtotime($declose_time[$index]), $time_interval));
                    }
                }
            }

            if (is_array($cutoff_hours) && !empty($cutoff_hours)) {
                $delivery_hrs = array_diff($get_store_open_hrs, $cutoff_hours);
                $get_store_open_hrs = $delivery_hrs;
            }

            //filter with slot limit option
            if (count($get_store_open_hrs) > 0) {
                foreach ($get_store_open_hrs as $index => $get_store_open_hr) {
                    if (!$this->pl8app_store_timing_slot_limit($get_store_open_hr, $current_date)) {
                        unset($get_store_open_hrs[$index]);
                    }
                }
                $get_store_open_hrs = array_values($get_store_open_hrs);
            }

        }
        return $get_store_open_hrs;
    }

    /**
     * Get Pickup Hours
     *
     * @return array TimeStamps for all the available time
     * @since 1.2
     */
    public function get_pickup_hrs($store_times)
    {

        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();

        if (!isset($store_timings['open_day']) || !(is_array($store_timings['open_day']) && count($store_timings['open_day']) > 0))
            return [];

        if (!empty($store_timings['pre_order_range'])) {
            $dates = $this->get_service_dates();
            $first_day = $dates['raw_date'][0];

            if (is_null($first_day)) {
                return '';
            }

            $getDaynumber = $this->get_weekday_number($first_day);
            $deliveryHours = $this->get_weekday_pickup_hrs($getDaynumber, $format = 'timestamp', $first_day);
            return $deliveryHours;
        }

        $prep_time = !empty(pl8app_get_option('prep_time')) ? pl8app_get_option('prep_time') : 0;
        $prep_time = $prep_time * 60;
        $store_hours = $this->get_store_open_close_hours();
        $store_open_time = isset($store_hours['open_time']) ? $store_hours['open_time'] : '';
        $store_close_time = isset($store_hours['close_time']) ? $store_hours['close_time'] : '';
        $store_final_close_time = is_array($store_close_time) ? end($store_close_time) : '';
        $store_break_hours = isset($store_hours['break_hours']) ? $store_hours['break_hours'] : '';

        $store_close_time_unix = strtotime($store_final_close_time);
        $current_time = strtotime(current_time('H:i'));
        $current_date = current_time('Y-m-d');

        //Add prep time to current time to determine the time to display for the dropdown
        if ($prep_time > 0) {
            $current_time = $current_time + $prep_time;
        }

        if ($current_time > $store_close_time_unix) {

            if (empty($store_timings['pre_order_range'])) return [];
            $next_date = date('Y-m-d', strtotime(' +1 day'));
            $is_holiday = $this->is_holiday_today($next_date);

            if (!$is_holiday) {
                $get_weekday_number = $this->get_weekday_number($next_date);
                $store_times = $this->get_weekday_pickup_hrs($get_weekday_number, $format = 'timestamp');
            }

        } else {

            $day_number = $this->get_weekday_number($current_date);
            $time_interval = self::store_time_interval();
            $store_times = array();
            if (!empty($store_open_time) && !empty($store_close_time) && is_array($store_open_time) && is_array($store_close_time)) {
                foreach ($store_open_time as $index => $open_time_row) {
                    if (!empty($open_time_row) && !empty($store_close_time[$index])) {
                        $store_times = array_merge($store_times, range(strtotime($open_time_row), strtotime($store_close_time[$index]), $time_interval));
                    }
                }
            }
            if (!isset($store_timings['open_day'][$day_number]))
                return '';


            $store_timings = [];
            foreach ($store_times as $store_time) {

                if ($store_time > $current_time)
                    $store_timings[] = $store_time;
            }

            $store_times = $store_timings;

            //filter with slot limit option
            if (count($store_times) > 0) {
                foreach ($store_times as $index => $store_time) {
                    if (!$this->pl8app_store_timing_slot_limit($store_time, $current_date)) {
                        unset($store_times[$index]);
                    }
                }
                $store_times = array_values($store_times);
            }

        }
        return apply_filters('pl8app_st_store_timings', $store_times);
    }

    /**
     * Set date format
     *
     * @return string date format
     * @since 1.9
     */
    public function set_date_format($format)
    {
        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $format = !empty($store_timings['date_format']) ? $store_timings['date_format'] : $format;
        return $format;
    }

    /**
     * Get Break Hours
     *
     * @return array TimeStamps for all the break hours
     * @since 1.2
     */
    public function get_break_hours($break_hours)
    {
        $time_interval = self::store_time_interval();

        if (empty($break_hours)) {
            return;
        }

        if (strpos($break_hours, ',') !== false) {
            $store_break_hours = explode(',', $break_hours);
        } else {
            $store_break_hours = array();
            $store_break_hours[] = $break_hours;
        }

        $breaks = $break_array = array();


        if (is_array($store_break_hours) && !empty($store_break_hours)) {

            foreach ($store_break_hours as $key => $store_break_hour) {
                $store_break_hour = trim($store_break_hour, " ");
                if (!empty($store_break_hour)) {
                    $times = explode('-', $store_break_hour);
                    if (is_array($times) && !empty($times)) {
                        $start_time = isset($times[0]) ? $times[0] : '';
                        $end_time = isset($times[1]) ? $times[1] : '';
                        $break_array[] = (range(strtotime($start_time), strtotime($end_time), $time_interval));
                    }
                }
            }

            if (is_array($break_array) && !empty($break_array)) {
                foreach ($break_array as $key => $val) {
                    foreach ($val as $time) {
                        $breaks[] = $time;
                    }
                }
            }
        }
        return $breaks;
    }

    /**
     * Get all the holidays
     *
     * @return array TimeStamps for all holidays
     * @since 1.2
     */
    public function get_holidays()
    {

        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $holidays_arr = array();

        $date_format = 'Y-m-d';


        if (isset($store_timings['holiday'])) {

            $holidays = $store_timings['holiday'];

            if (is_array($holidays) && !empty($holidays)) {
                foreach ($holidays as $k => $holiday) {
                    $holiday_list = date($date_format, strtotime($holiday));
                    array_push($holidays_arr, $holiday_list);
                }
            }
        }

        return apply_filters('pl8app_get_holidays_lists', $holidays_arr);
    }

    /**
     * Render placeholder for pre order dates
     *
     * @return html
     * @since 1.2
     */
    public function render_preorder_dates($service_type)
    {
        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();

        if (isset($store_timings['open_day']) && is_array($store_timings['open_day']) && count($store_timings['open_day']) > 0 && !empty($store_timings['pre_order_range']))
            pl8app_StoreTiming_Settings::get_template_part('store-time-preorder-form');
    }

    /**
     * Adds necessary css and js for store timing
     *
     */
    public function pl8app_st_scripts()
    {

        wp_register_script('pl8app-st-functions', PL8_PLUGIN_URL . 'assets/js/pl8app-st-functions.js', array('jquery'), PL8_VERSION);
        wp_enqueue_script('pl8app-st-functions');

        wp_register_style('pl8app-st-styles', PL8_PLUGIN_URL . 'assets/css/pl8app-st-styles.css', array(), PL8_VERSION);
        wp_enqueue_style('pl8app-st-styles');

        $pl8app_settings = get_option('pl8app_settings', true);
        $enabled_service = isset($pl8app_settings['enable_service']) ? $pl8app_settings['enable_service'] : array();

        $pre_order_range = $this->get_order_until();
        $current_date = current_time('Y-m-d');
        $pre_order_date = date('Y-m-d', strtotime($current_date . ' + ' . $pre_order_range . ' days'));

        $is_holiday_today = $this->is_holiday_today($current_date) ? 'yes' : 'no';

        $is_store_closed = $this->is_store_closed();
        $selected_date = $current_date;
        $today_open = 'yes';

        if ($is_store_closed) {
            $next_date = date('Y-m-d', strtotime('+1 day'));
            $is_holiday = $this->is_holiday_today($next_date);
            if (!$is_holiday) {
                $selected_date = $next_date;
                $today_open = 'no';
            }
        }

        $params = array(
            'order_until' => $pre_order_date,
            'holidays' => $this->get_holidays(),
            'is_holiday' => $is_holiday_today,
            'is_store_closed' => $is_store_closed,
            'ajaxurl' => pl8app_get_ajax_url(),
            'selectedDate' => $selected_date,
            'day_open' => $today_open,
            'enabled_sevice_type' => $enabled_service,
        );

        wp_localize_script('pl8app-st-functions', 'pl8app_st_vars', $params);
        wp_enqueue_script('pl8app-st-functions');
    }

    /**
     * Get Preorder Range
     *
     * @return string
     * @since 1.2
     */
    public function get_order_until()
    {
        $current_date = date('Y-m-d');
        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $order_until = isset($store_timings['pre_order_range']) ? $store_timings['pre_order_range'] : 0;

        $not_active_days = array();

        if ($order_until > 0) {
            for ($i = 0; $i <= $order_until; $i++) {
                $pre_order_date = date('Y-m-d', strtotime($current_date . ' + ' . $i . ' days'));
                $get_day_number = $this->get_weekday_number($pre_order_date);
                if (!isset($store_timings['open_day'][$get_day_number])) {
                    $not_active_days[] = $get_day_number;
                }
            }
        }
        if (count($not_active_days) > 0) {
            $order_until = intval($order_until) + intval(count($not_active_days));
        }
        return $order_until;
    }


    /**
     * get weekdays number for the selected date
     *
     * @return string
     * @since 1.2
     */
    public function get_weekday_number($date)
    {
        $day_number = date('l', strtotime($date));
        return $day_number;
    }

    /**
     * Get the store closed and open day numbers
     *
     * @return array List of store closed and open day numbers
     * @since 1.0
     */
    public function get_next_open_day()
    {
        $day_number = 1;
        $current_date = current_time('Y-m-d');
        $date_number = date('N', strtotime($current_date));
        $get_open_days = $this->get_store_open_closed_days_number();
        $open_days = $get_open_days['open_days'];
        $array_key = array_search($date_number, $open_days);
        if ($array_key !== false) {
            $next_key = $array_key + 1;
            if ($open_days[$next_key] == null) {
                $next_key = 0;
            }

            $day_number = $open_days[$next_key];

        }

        if ($day_number < $date_number) {
            $next_open_day = (7 - $date_number) + $day_number;
        } else {
            $next_open_day = $date_number - $day_number;
        }

        return $next_open_day;
    }

    /**
     * Get store open, close and break hours for that day
     *
     * @return array open, close and break hours array
     * @since 1.2
     */
    public function get_store_open_close_hours()
    {
        $get_timezone = get_option('timezone_string');

        if ($get_timezone !== '') {
            date_default_timezone_set($get_timezone);
        }

        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();

        $current_date = current_time('Y-m-d');
        $day_number = $this->get_weekday_number($current_date);
        $store_times = array();

        $days = pl8app_StoreTiming::pl8app_st_get_weekdays();

        if (is_array($days)) {
            $open_time = isset($store_timings['open_time'][$day_number]) ? $store_timings['open_time'][$day_number] : '';
            $close_time = isset($store_timings['close_time'][$day_number]) ? $store_timings['close_time'][$day_number] : '';
            $break_hours = isset($store_timings['break_hours'][$day_number]) ? $store_timings['break_hours'][$day_number] : '';

            $store_times['open_time'] = $open_time;
            $store_times['close_time'] = $close_time;
            $store_times['break_hours'] = $break_hours;
        }

        if (isset($store_timings['24hours'][$day_number])) {
            $store_times['open_time'] = array(0 => '00:00');
            $store_times['close_time'] = array(0 => '23:30');
        }
        return $store_times;
    }

    /**
     * Gets all the timestams for the holidays
     *
     * @return array TimeStamps for all holidays
     * @since 1.2
     */
    public function get_all_holidays()
    {
        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $holidays = array();

        if (isset($store_timings['holiday']) && !empty($store_timings['holiday'])) {
            foreach ($store_timings['holiday'] as $key => $holiday) {
                array_push($holidays, strtotime($holiday));
            }
        }
        return $holidays;
    }

    /**
     * Checks whether today is holiday or not
     *
     * @return bool
     * @since 1.2
     */
    public function is_holiday_today($date)
    {
        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();

        $cond = false;

        if (isset($store_timings['open_day']) && is_array($store_timings['open_day']) && count($store_timings['open_day']) > 0
            && !empty($store_timings['pre_order_range'])) {
            $current_date = $date;
            $current_date = strtotime($current_date);
            $get_holidays = $this->get_all_holidays();

            if (is_array($get_holidays) && !empty($get_holidays)) {
                if (in_array($current_date, $get_holidays)) {
                    $cond = true;
                }
            }
        }
        return $cond;
    }


    /**
     * Get store close time
     *
     * @return time
     * @since 1.2
     */
    public function is_store_closed()
    {
        $store_hours = $this->get_store_open_close_hours();
        $close_time = isset($store_hours['close_time']) ? $store_hours['close_time'] : '';

        if (is_array($close_time) && count($close_time) > 0) {
            $close_time = $close_time[count($close_time) - 1];
        } else {
            $close_time = '';
        }

        $close_unix_time = strtotime($close_time);
        $current_time = strtotime(current_time('H:i'));
        $current_date = current_time('Y-m-d');
        $prep_time = pl8app_get_option('prep_time', 0);
        $prep_time = $prep_time * 60;


        //Add prep time to current time to determine the time to display for the dropdown
        if ($prep_time > 0) {
            $current_time = $current_time + $prep_time;
        }
        return $current_time > $close_unix_time;
    }


    /**
     * Checks whether today is holiday or not
     *
     * @return bool
     * @since 1.2
     */
    public function pl8app_st_render_timings()
    {
        $selected_date = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : '';
        $pickupHrs = array();

        if (!empty($selected_date)) {
            $getDaynumber = $this->get_weekday_number($selected_date);
            $pickupHrs = $this->get_weekday_pickup_hrs($getDaynumber, $format = 'normal', $selected_date);
            $deliveryHours = $this->get_weekday_delivery_hrs($selected_date, $getDaynumber, $format = 'normal');
        }
        wp_send_json_success(array(
            'pickupHrs' => $pickupHrs,
            'deliveryHrs' => $deliveryHours,
        ));
        wp_die();
    }

    /**
     * Set store time interval
     *
     * @return bool
     * @since 1.2
     */
    public static function store_time_interval()
    {
        $store_time_interval = apply_filters('pl8app_store_time_interval', 30);
        return $store_time_interval * 60;
    }


    /**
     * Get time format
     *
     * @return time
     * @since 1.2
     */
    public function pl8app_time_format()
    {

        $store_time_format = pl8app_get_option('store_time_format');
        $time_format = !empty($store_time_format) && $store_time_format == '24hrs' ? 'H:i' : 'H:i';
        return $time_format;
    }


    /**
     * Get delivery hours for weekdays
     *
     * @return bool
     * @since 1.2
     */
    protected function get_weekday_delivery_hrs($selected_date, $day_number, $format)
    {
        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $options = get_option('pl8app_settings');
        $delcut_timings = isset($options['delivery_timing'])?$options['delivery_timing']:array();

        $prep_time = !empty(pl8app_get_option('prep_time')) ? pl8app_get_option('prep_time') : 0;
        $prep_time = $prep_time * 60;
        if (!isset($store_timings['24hours'][$day_number])) {
            $open_time = isset($store_timings['open_time'][$day_number]) ? $store_timings['open_time'][$day_number] : '';
            $close_time = isset($store_timings['close_time'][$day_number]) ? $store_timings['close_time'][$day_number] : '';
        } else {
            $open_time = array(0 => '00:00');
            $close_time = array(0 => '23:59');
        }

//        $break_hours = isset($store_timings['break_hours'][$day_number]) ? $store_timings['break_hours'][$day_number] : '';
        $store_times = $store_times_array = $delivery_hrs = array();

        $service_date = $selected_date;
        $selected_date = strtotime($selected_date);
        $current_date = strtotime(current_time('Y-m-d'));

        $current_time = strtotime(current_time('H:i'));

        $time_format = $this->pl8app_time_format();

        //Add prep time to current time to determine the time to display for the dropdown
        if ($prep_time > 0) {
            $current_time = $current_time + $prep_time;
        }

        $time_interval = self::store_time_interval();

        $cutoff_hours = array();

        $deopen_time = isset($delcut_timings['open_time'][$day_number]) ? $delcut_timings['open_time'][$day_number] : '';
        $declose_time = isset($delcut_timings['close_time'][$day_number]) ? $delcut_timings['close_time'][$day_number] : '';

        if (!empty($deopen_time) && !empty($declose_time) && is_array($deopen_time) && is_array($declose_time)) {

            foreach ($deopen_time as $index => $deopen_time_row) {
                if (!empty($deopen_time_row) && !empty($declose_time[$index])) {
                    $cutoff_hours = array_merge($cutoff_hours, range(strtotime($deopen_time_row), strtotime($declose_time[$index]), $time_interval));
                }
            }
        }

        if (!empty($open_time) && !empty($close_time) && is_array($open_time) && is_array($close_time)) {
            foreach ($open_time as $index => $open_time_row) {
                if (!empty($open_time_row) && !empty($close_time[$index])) {
                    $store_times = array_merge($store_times, range(strtotime($open_time_row), strtotime($close_time[$index]), $time_interval));
                }
            }
        }

        if (is_array($cutoff_hours) && !empty($cutoff_hours)) {
            $delivery_hrs = array_diff($store_times, $cutoff_hours);
        } else {
            $delivery_hrs = $store_times;
        }

        if ($selected_date == $current_date) {

            $store_timings = [];
            foreach ($delivery_hrs as $store_time) {
                if ($store_time > $current_time)
                    $store_timings[] = $store_time;
            }
            $delivery_hrs = $store_timings;
        }


        if ($format == 'normal') {
            if (is_array($delivery_hrs) && !empty($delivery_hrs)) {
                foreach ($delivery_hrs as $time) {
                    $store_time = date($time_format, $time);
                    array_push($store_times_array, $store_time);
                }
            }
        }

        if ($format == 'timestamp') {
            $store_times_array = $delivery_hrs;
        }

        //filter with slot limit option
        if (count($store_times_array) > 0) {
            foreach ($store_times_array as $index => $store_times_row) {
                if (!$this->pl8app_store_timing_slot_limit($store_times_row, $service_date)) {
                    unset($store_times_array[$index]);
                }
            }
            $store_times_array = array_values($store_times_array);
        }

        return $store_times_array;
    }


    /**
     * Get pickup hours for weekdays
     *
     * @return bool
     * @since 1.2
     */
    protected function get_weekday_pickup_hrs($day_number, $format, $selected_date = '')
    {

        $store_timings = pl8app_StoreTiming_Settings::pl8app_timing_options();
        $prep_time = !empty(pl8app_get_option('prep_time')) ? pl8app_get_option('prep_time') : 0;
        $prep_time = $prep_time * 60;
        if (!isset($store_timings['24hours'][$day_number])) {
            $open_time = isset($store_timings['open_time'][$day_number]) ? $store_timings['open_time'][$day_number] : '';
            $close_time = isset($store_timings['close_time'][$day_number]) ? $store_timings['close_time'][$day_number] : '';
        } else {
            $open_time = array(0 => '00:00');
            $close_time = array(0 => '23:59');
        }


        $store_times = $store_times_array = array();
        $time_interval = self::store_time_interval();

        $date_format = $this->pl8app_time_format();

        $current_time = strtotime(current_time('H:i'));

        //Add prep time to current time to determine the time to display for the dropdown
        if ($prep_time > 0) {
            $current_time = $current_time + $prep_time;
        }

        $final_store_times = array();



        if (!empty($open_time) && !empty($close_time) && is_array($open_time) && is_array($close_time)) {

            $store_times = array();
            foreach ($open_time as $index => $open_time_row) {
                if (!empty($open_time_row) && !empty($close_time[$index])) {
                    $store_times = array_merge($store_times, range(strtotime($open_time_row), strtotime($close_time[$index]), $time_interval));

                }
            }

            $service_date = $selected_date;
            $selected_date = strtotime($selected_date);
            $current_date = strtotime(current_time('Y-m-d'));

            if ($selected_date == $current_date) {
                $store_timings = [];
                foreach ($store_times as $store_time) {
                    if ($store_time > $current_time)
                        $store_timings[] = $store_time;
                }
                $store_times = $store_timings;
            }

            if ($format == 'normal') {
                if (is_array($store_times)) {
                    foreach ($store_times as $time) {
                        $store_time = date($date_format, $time);
                        array_push($store_times_array, $store_time);
                    }
                }
                $final_store_times = $store_times_array;
            }

            if ($format == 'timestamp') {
                $final_store_times = $store_times;
            }
        }

        //filter with slot limit option
        if (count($final_store_times) > 0) {
            foreach ($final_store_times as $index => $final_store_time) {
                if (!$this->pl8app_store_timing_slot_limit($final_store_time, $service_date)) {
                    unset($final_store_times[$index]);
                }
            }
            $final_store_times = array_values($final_store_times);
        }

        return $final_store_times;
    }


    /**
     * Get store timing for today
     *
     * @return bool
     * @since 1.2
     */
    public function pl8app_store_timings_for()
    {
        $store_hours = $this->get_store_open_close_hours();
        $store_close_time = isset($store_hours['close_time']) ? $store_hours['close_time'] : '';
        if (is_array($store_close_time) && count($store_close_time) > 0) {
            $store_close_time = $store_close_time[count($store_close_time) - 1];
        } else {
            $store_close_time = '';
        }
        $store_close_time_unix = strtotime($store_close_time);

        $store_timings_for = true;

        $current_time = strtotime(current_time('Y-m-d H:i'));
        $today = current_time('Y-m-d');

        $is_holiday = $this->is_holiday_today($today);

        if ($is_holiday || $current_time > $store_close_time_unix) {
            $store_timings_for = false;
        }
        return $store_timings_for;
    }

    /**
     * Set delivery_date tag in the email tags list
     *
     * @return html
     * @since 1.3
     */
    public function set_delivery_date_tag($payment_id)
    {
        $email_tag = 'delivery_date';
        $tag_description = esc_html('Selected delivery date by customer', 'pl8app-store-timing');

        pl8app_add_email_tag($email_tag, $tag_description, array($this, 'pl8app_delivery_date_tag'));
    }

    /**
     * Gets the email tag value from the payment
     *
     * @return html
     * @since 1.3
     */
    public function pl8app_delivery_date_tag($payment_id)
    {
        if ($payment_id) {
            $service_date = get_post_meta($payment_id, '_pl8app_delivery_date', true);
            return pl8app_local_date($service_date);
        }
    }

    /**
     * Check the timeing slot limit
     *
     * @param $service_type
     * @param $service_time
     * @param $service_date
     */
    public function pl8app_store_timing_slot_limit($service_time, $service_date)
    {

        $otil_settings = new Order_Time_Interval_Limit_Settings();
        $otil_settings = $otil_settings->options;
        if (is_integer($service_time) || !strpos($service_time, ':')) $service_time = date('H:i', $service_time);
        $orders_per_interval = !empty($otil_settings['orders_per_total_interval']) ? intval($otil_settings['orders_per_total_interval']) : '';
        $orders_per_delivery_interval = !empty($otil_settings['orders_per_delivery_interval']) ? intval($otil_settings['orders_per_delivery_interval']) : '';
        $orders_per_pickup_interval = !empty($otil_settings['orders_per_pickup_interval']) ? intval($otil_settings['orders_per_pickup_interval']) : '';

        if (!empty($service_date) && !empty($service_time)) {
            //Count all the orders
            $otil_functions = new pl8app_Order_Time_intervals_Limit_Functions();
            $delivery_count = $otil_functions->get_orders_count($service_date, $service_time, 'delivery');
            $pickup_count = $otil_functions->get_orders_count($service_date, $service_time, 'pickup');
            if ((!empty($orders_per_delivery_interval) && $delivery_count >= $orders_per_delivery_interval)
                || (!empty($orders_per_pickup_interval) && $pickup_count >= $orders_per_pickup_interval)
                || ($delivery_count + $pickup_count >= $orders_per_interval)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $timeformat
     * @param $storetimeformat
     */
    public function set_timestamp_formate($timeformat, $storetimeformat)
    {
        return 'H:i';
    }

}

new pl8app_StoreTiming_Functions();