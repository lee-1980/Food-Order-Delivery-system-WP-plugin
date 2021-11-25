<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * pl8app_Customer_Reports_Table Class
 *
 * Renders the Customer Reports table
 *
 * @since 1.0.0
 */
class pl8app_Customer_Reports_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $per_page = 30;

	/**
	 * Number of customers found
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $count = 0;

	/**
	 * Total customers
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $total = 0;

	/**
	 * The arguments for the data set
	 *
	 * @var array
	 * @since  1.0.0
	 */
	public $args = array();

	/**
	 * Get things started
	 *
	 * @since 1.0.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => __( 'Customer', 'pl8app' ),
			'plural'   => __( 'Customers', 'pl8app' ),
			'ajax'     => false,
		) );

	}

	/**
	 * Show the search field
	 *
	 * @since 1.0.0
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
		<?php
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'name';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Contains all the data of the customers
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'num_purchases' :
				$value = '<a href="' .
					admin_url( 'admin.php?page=pl8app-payment-history&user=' . urlencode( $item['email'] )
				) . '">' . esc_html( $item['num_purchases'] ) . '</a>';
				break;

			case 'amount_spent' :
				$value = pl8app_currency_filter( pl8app_format_amount( $item[ $column_name ] ) );
				break;

			case 'date_created' :
				$value = date_i18n( get_option( 'date_format' ), strtotime( $item['date_created'] ) );
				break;

			default:
				$value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : null;
				break;
		}
		return apply_filters( 'pl8app_customers_column_' . $column_name, $value, $item['id'] );
	}

	public function column_name( $item ) {
		$name        = '#' . $item['id'] . ' ';
		$name       .= ! empty( $item['name'] ) ? $item['name'] : '<em>' . __( 'Unnamed Customer','pl8app' ) . '</em>';
		$user        = ! empty( $item['user_id'] ) ? $item['user_id'] : $item['email'];
		$view_url    = admin_url( 'admin.php?page=pl8app-customers&view=overview&id=' . $item['id'] );
		$actions     = array(
			'view'   => '<a href="' . $view_url . '">' . __( 'View', 'pl8app' ) . '</a>',
			'delete' => '<a href="' . admin_url( 'admin.php?page=pl8app-customers&view=delete&id=' . $item['id'] ) . '">' . __( 'Delete', 'pl8app' ) . '</a>'
		);

		$customer = new pl8app_Customer( $item['id'] );
		$pending  = pl8app_user_pending_verification( $customer->user_id ) ? ' <em>' . __( '(Pending Verification)', 'pl8app' ) . '</em>' : '';

		return '<a href="' . esc_url( $view_url ) . '">' . $name . '</a>' . $pending . $this->row_actions( $actions );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'name'          => __( 'Name', 'pl8app' ),
			'email'         => __( 'Primary Email', 'pl8app' ),
			'num_purchases' => __( 'Numbers Of Order', 'pl8app' ),
			'amount_spent'  => __( 'Total Order Price', 'pl8app' ),
			'date_created'  => __( 'Date Created', 'pl8app' ),
		);

		return apply_filters( 'pl8app_report_customer_columns', $columns );

	}

	/**
	 * Get the sortable columns
	 *
	 * @since  1.0.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'date_created'  => array( 'date_created', true ),
			'name'          => array( 'name', true ),
			'num_purchases' => array( 'purchase_count', false ),
			'amount_spent'  => array( 'purchase_value', false ),
		);
	}

	/**
	 * Outputs the reporting views
	 *
	 * @since 1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
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
	 * Retrieves the search query string
	 *
	 * @since  1.0.0
	 * @return mixed string If search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Build all the reports data
	 *
	 * @since 1.0
	  * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {
		global $wpdb;

		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$order   = isset( $_GET['order'] )   ? sanitize_text_field( $_GET['order'] )   : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';

		$args    = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby
		);

		if( is_email( $search ) ) {
			$args['email'] = $search;
		} elseif( is_numeric( $search ) ) {
			$args['id']    = $search;
		} elseif( strpos( $search, 'user:' ) !== false ) {
			$args['user_id'] = trim( str_replace( 'user:', '', $search ) );
		} else {
			$args['name']  = $search;
		}

		$this->args = $args;
		$customers  = PL8PRESS()->customers->get_customers( $args );

		if ( $customers ) {

			foreach ( $customers as $customer ) {

				$user_id = ! empty( $customer->user_id ) ? intval( $customer->user_id ) : 0;

				$data[] = array(
					'id'            => $customer->id,
					'user_id'       => $user_id,
					'name'          => $customer->name,
					'email'         => $customer->email,
					'num_purchases' => $customer->purchase_count,
					'amount_spent'  => $customer->purchase_value,
					'date_created'  => $customer->date_created,
				);
			}
		}

		return $data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.0
	 * @uses pl8app_Customer_Reports_Table::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 * @uses pl8app_Customer_Reports_Table::get_pagenum()
	 * @uses pl8app_Customer_Reports_Table::get_total_customers()
	 * @return void
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->reports_data();

		$this->total = pl8app_count_total_customers( $this->args );

		// Add condition to be sure we don't divide by zero.
		// If $this->per_page is 0, then set total pages to 1.
		$total_pages = $this->per_page ? ceil( (int) $this->total / (int) $this->per_page ) : 1;

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => $total_pages,
		) );
	}
}
