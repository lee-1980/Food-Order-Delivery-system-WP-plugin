<?php
/**
 * Gateways Reports Table Class
 *
 * @package     pl8app
 * @subpackage  Admin/Reports
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * pl8app_Gateawy_Reports_Table Class
 *
 * Renders the Order Reports table
 *
 * @since 1.0
 */
class pl8app_Gateawy_Reports_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.0
	 */
	public $per_page = 30;


	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => pl8app_get_label_singular(),
			'plural'   => pl8app_get_label_plural(),
			'ajax'     => false,
		) );

	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'label';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.0
	 *
	 * @param array $item Contains all the data of the menuitems
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'label'          => __( 'Gateway', 'pl8app' ),
			'complete_sales' => __( 'Complete Sales', 'pl8app' ),
			'pending_sales'  => __( 'Pending / Failed Sales', 'pl8app' ),
			'total_sales'    => __( 'Total Sales', 'pl8app' ),
		);

		return $columns;
	}


	/**
	 * Retrieve the current page number
	 *
	 * @since 1.0
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Outputs the reporting views
	 *
	 * @since 1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		pl8app_report_views();
	}


	/**
	 * Build all the reports data
	 *
	 * @since 1.0
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {

		$reports_data = array();
		$gateways     = pl8app_get_payment_gateways();

		foreach ( $gateways as $gateway_id => $gateway ) {

			$complete_count = pl8app_count_sales_by_gateway( $gateway_id, 'publish' );
			$pending_count  = pl8app_count_sales_by_gateway( $gateway_id, array( 'pending', 'failed' ) );

			$reports_data[] = array(
				'ID'             => $gateway_id,
				'label'          => $gateway['admin_label'],
				'complete_sales' => pl8app_format_amount( $complete_count, false ),
				'pending_sales'  => pl8app_format_amount( $pending_count, false ),
				'total_sales'    => pl8app_format_amount( $complete_count + $pending_count, false ),
			);
		}

		return $reports_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @since 1.0
	 * @uses pl8app_Gateawy_Reports_Table::get_columns()
	 * @uses pl8app_Gateawy_Reports_Table::get_sortable_columns()
	 * @uses pl8app_Gateawy_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->reports_data();

	}
}
