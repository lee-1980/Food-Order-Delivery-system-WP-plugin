<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * pl8app_API_Keys_Table Class
 *
 * Renders the API Keys table
 *
 * @since 1.0.0
 */
class pl8app_API_Keys_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.0.0
	 */
	public $per_page = 30;

	/**
	 * @var object Query results
	 * @since 1.0.0
	 */
	private $keys;

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
			'singular'  => __( 'API Key', 'pl8app' ),
			'plural'    => __( 'API Keys', 'pl8app' ),
			'ajax'      => false,
		) );

		$this->query();
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
		return 'user';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Displays the public key rows
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_key( $item ) {
		return '<input readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item[ 'key' ] ) . '"/>';
	}

	/**
	 * Displays the token rows
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_token( $item ) {
		return '<input readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item[ 'token' ] ) . '"/>';
	}

	/**
	 * Displays the secret key rows
	 *
	 * @since 1.0.0
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_secret( $item ) {
		return '<input readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item[ 'secret' ] ) . '"/>';
	}

	/**
	 * Renders the column for the user field
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function column_user( $item ) {

		$actions = array();

		// if( apply_filters( 'pl8app_api_log_requests', true ) ) {
		// 	$actions['view'] = sprintf(
		// 		'<a href="%s">%s</a>',
		// 		esc_url( add_query_arg( array( 'page' => 'pl8app-reports', 'view' => 'api_requests' 'tab' => 'logs', 's' => $item['email'] ), 'admin.php' ) ),
		// 		__( 'View API Log', 'pl8app' )
		// 	);
		// }

		$actions['reissue'] = sprintf(
			'<a href="%s" class="pl8app-regenerate-api-key">%s</a>',
			esc_url( wp_nonce_url( add_query_arg( array( 'user_id' => $item['id'], 'pl8app_action' => 'process_api_key', 'pl8app_api_process' => 'regenerate' ) ), 'pl8app-api-nonce' ) ),
			__( 'Reissue', 'pl8app' )
		);
		$actions['revoke'] = sprintf(
			'<a href="%s" class="pl8app-revoke-api-key pl8app-delete">%s</a>',
			esc_url( wp_nonce_url( add_query_arg( array( 'user_id' => $item['id'], 'pl8app_action' => 'process_api_key', 'pl8app_api_process' => 'revoke' ) ), 'pl8app-api-nonce' ) ),
			__( 'Revoke', 'pl8app' )
		);

		$actions = apply_filters( 'pl8app_api_row_actions', array_filter( $actions ) );

		return sprintf('%1$s %2$s', $item['user'], $this->row_actions( $actions ) );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.0.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'user'   => __( 'Username', 'pl8app' ),
			'key'    => __( 'Public Key', 'pl8app' ),
			'token'  => __( 'Token', 'pl8app' ),
			'secret' => __( 'Secret Key', 'pl8app' ),
		);

		return $columns;
	}

	/**
	 * Display the key generation form
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		static $pl8app_api_is_bottom;

		if( $pl8app_api_is_bottom ) {
			return;
		}
		?>
		<form id="api-key-generate-form" method="post" action="<?php echo admin_url( 'admin.php?page=pl8app-tools&tab=api_keys' ); ?>">
			<input type="hidden" name="pl8app_action" value="process_api_key" />
			<input type="hidden" name="pl8app_api_process" value="generate" />
			<?php wp_nonce_field( 'pl8app-api-nonce' ); ?>
			<?php echo PL8PRESS()->html->ajax_user_search(); ?>
			<?php submit_button( __( 'Generate New API Keys', 'pl8app' ), 'secondary', 'submit', false ); ?>
		</form>
		<?php
		$pl8app_api_is_bottom = true;
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 1.0.0
	 * @access protected
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
?>
	<div class="tablenav <?php echo esc_attr( $which ); ?>">

		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
<?php
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>

		<br class="clear" />
	</div>
<?php
	}

	/**
	 * Retrieve the current page number
	 *
	 * @since 1.0.0
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Performs the key query
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function query() {
		$users    = get_users( array(
			'meta_value' => 'pl8app_user_secret_key',
			'number'     => $this->per_page,
			'offset'     => $this->per_page * ( $this->get_paged() - 1 ),
		) );
		$keys     = array();

		foreach( $users as $user ) {
			$keys[$user->ID]['id']     = $user->ID;
			$keys[$user->ID]['email']  = $user->user_email;
			$keys[$user->ID]['user']   = '<a href="' . add_query_arg( 'user_id', $user->ID, 'user-edit.php' ) . '"><strong>' . $user->user_login . '</strong></a>';

			$keys[$user->ID]['key']    = PL8PRESS()->api->get_user_public_key( $user->ID );
			$keys[$user->ID]['secret'] = PL8PRESS()->api->get_user_secret_key( $user->ID );
			$keys[$user->ID]['token']  = PL8PRESS()->api->get_token( $user->ID );
		}

		return $keys;
	}



	/**
	 * Retrieve count of total users with keys
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function total_items() {
		global $wpdb;

		if( ! get_transient( 'pl8app_total_api_keys' ) ) {
			$total_items = $wpdb->get_var( "SELECT count(user_id) FROM $wpdb->usermeta WHERE meta_value='pl8app_user_secret_key'" );

			set_transient( 'pl8app_total_api_keys', $total_items, 60 * 60 );
		}

		return get_transient( 'pl8app_total_api_keys' );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns
		$sortable = array(); // Not sortable... for now

		$this->_column_headers = array( $columns, $hidden, $sortable, 'user' );

		$data = $this->query();

		$total_items = $this->total_items();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
