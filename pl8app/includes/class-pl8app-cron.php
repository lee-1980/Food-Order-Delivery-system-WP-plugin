<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * pl8app_Cron Class
 *
 * This class handles scheduled events
 *
 * @since  1.0.0
 */
class pl8app_Cron {
	/**
	 * Get things going
	 *
	 * @since  1.0.0
	 * @see pl8app_Cron::weekly_events()
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules'   ) );
		add_action( 'wp',             array( $this, 'schedule_events' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @since  1.0.0
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'pl8app' )
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function schedule_events() {
		$this->weekly_events();
		$this->daily_events();
	}

	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'pl8app_weekly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp', true ), 'weekly', 'pl8app_weekly_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'pl8app_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp', true ), 'daily', 'pl8app_daily_scheduled_events' );
		}
	}

}
$pl8app_cron = new pl8app_Cron;