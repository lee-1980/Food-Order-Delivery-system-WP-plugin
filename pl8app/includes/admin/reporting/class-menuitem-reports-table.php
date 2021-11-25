<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * pl8app_Menuitem_Reports_Table Class
 *
 * Renders the Order Reports table
 *
 * @since 1.0
 */
class pl8app_Menuitem_Reports_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * @var object Query results
	 * @since 1.0
	 */
	private $products;

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

		add_action( 'pl8app_report_view_actions', array( $this, 'category_filter' ) );

		$this->query();

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
		return 'title';
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
		switch( $column_name ){
			case 'earnings' :
				return pl8app_currency_filter( pl8app_format_amount( $item[ $column_name ] ) );
			case 'average_sales' :
				return round( $item[ $column_name ] );
			case 'average_earnings' :
				return pl8app_currency_filter( pl8app_format_amount( $item[ $column_name ] ) );
			case 'details' :
				return '<a href="' . admin_url( 'admin.php?page=pl8app-reports&view=menuitems&menuitem-id=' . $item['ID'] ) . '">' . __( 'View Detailed Report', 'pl8app' ) . '</a>';
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'title'            => pl8app_get_label_singular(),
			'sales'            => __( 'Sales', 'pl8app' ),
			'earnings'         => __( 'Earnings', 'pl8app' ),
			'average_sales'    => __( 'Monthly Average Sales', 'pl8app' ),
			'average_earnings' => __( 'Monthly Average Earnings', 'pl8app' ),
			'details'          => __( 'Detailed Report', 'pl8app' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @since  1.0.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'title'    => array( 'title', true ),
			'sales'    => array( 'sales', false ),
			'earnings' => array( 'earnings', false ),
		);
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
	 * Retrieve the category being viewed
	 *
	 * @since 1.0
	 * @return int Category ID
	 */
	public function get_category() {
		return isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0;
	}


	/**
	 * Retrieve the total number of menuitems
	 *
	 * @since 1.0
	 * @return int $total Total number of menuitems
	 */
	public function get_total_menuitems() {
		$total  = 0;
		$counts = wp_count_posts( 'menuitem', 'readable' );
		foreach( $counts as $status => $count ) {
			$total += $count;
		}
		return $total;
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
	 * Attaches the category filter to the log views
	 *
	 * @since 1.0
	 * @return void
	 */
	public function category_filter() {
		if( get_terms( 'addon_category' ) ) {
			echo PL8PRESS()->html->category_dropdown( 'category', $this->get_category() );
		}
	}


	/**
	 * Performs the products query
	 *
	 * @since 1.0
	 * @return void
	 */
	public function query() {

		$orderby  = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
		$order    = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$category = $this->get_category();

		$args = array(
			'post_type'        => 'menuitem',
			'post_status'      => 'publish',
			'order'            => $order,
			'fields'           => 'ids',
			'posts_per_page'   => $this->per_page,
			'paged'            => $this->get_paged(),
			'suppress_filters' => true,
		);

		if( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'addon_category',
					'terms'    => $category,
				)
			);
		}

		switch ( $orderby ) :
			case 'title' :
				$args['orderby'] = 'title';
				break;

			case 'sales' :
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = '_pl8app_menuitem_sales';
				break;

			case 'earnings' :
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = '_pl8app_menuitem_earnings';
				break;
		endswitch;

		$args = apply_filters( 'pl8app_menuitem_reports_prepare_items_args', $args, $this );

		$this->products = new WP_Query( $args );

	}

	/**
	 * Build all the reports data
	 *
	 * @since 1.0
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {
		$reports_data = array();

		$menuitems = $this->products->posts;

		if ( $menuitems ) {
			foreach ( $menuitems as $menuitem ) {
				$reports_data[] = array(
					'ID'               => $menuitem,
					'title'            => get_the_title( $menuitem ),
					'sales'            => pl8app_get_menuitem_sales_stats( $menuitem ),
					'earnings'         => pl8app_get_menuitem_earnings_stats( $menuitem ),
					'average_sales'    => pl8app_get_average_monthly_menuitem_sales( $menuitem ),
					'average_earnings' => pl8app_get_average_monthly_menuitem_earnings( $menuitem ),
				);
			}
		}

		return $reports_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @since 1.0
	 * @uses pl8app_Menuitem_Reports_Table::get_columns()
	 * @uses pl8app_Menuitem_Reports_Table::get_sortable_columns()
	 * @uses pl8app_Menuitem_Reports_Table::reports_data()
	 * @uses pl8app_Menuitem_Reports_Table::get_pagenum()
	 * @uses pl8app_Menuitem_Reports_Table::get_total_menuitems()
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->reports_data();

		$total_items = $this->get_total_menuitems();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
