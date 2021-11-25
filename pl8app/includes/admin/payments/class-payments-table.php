<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * pl8app_Payment_History_Table Class
 *
 * Renders the Order History table on the Order History page
 *
 * @since  1.0.0
 */
class pl8app_Payment_History_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since  1.0.0
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 1.0
	 */
	public $base_url;

	/**
	 * Total number of payments
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $total_count;

	/**
	 * Total number of completed payments
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $completed_count;

	/**
	 * Total number of pending payments
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $pending_count;

	/**
	 * Total number of paid payments
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $paid_count;


    public $order_statues_count = array();

    /**
     * Total number of out for deliver payments
     *
     * @var int
     * @since  1.0.0
     */

	public $out_for_deliver_count;

	/**
	 * Get things started
	 *
	 * @since  1.0.0
	 * @uses pl8app_Payment_History_Table::get_payment_counts()
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

		$this->get_payment_counts();
		$this->process_bulk_action();
		$this->base_url = admin_url( 'admin.php?page=pl8app-payment-history' );

		add_action( 'admin_footer', array( $this, 'order_preview_template' ) );
	}

	public function service_type_filters() { ?>

    	<div class="pl8app-service-type-filters-wrap">
      		<ul class="subsubsub">
	        	<?php $get_service_types = pl8app_get_service_types(); ?>
	        	<li>
	          		<a href="<?php echo admin_url('/admin.php?page=pl8app-payment-history'); ?>">
	          			<?php echo esc_html_e( 'All', 'pl8app' ); ?>
	          		</a> |
	          	</li>

	          	<?php
	          	$i = 0;
	          	foreach( $get_service_types as $service_key => $service_label ) : ?>
	          		<li>
	          			<a href="<?php echo admin_url('/admin.php?page=pl8app-payment-history&service_type='.$service_key); ?>">
	          				<?php echo $service_label; ?>
	          			</a>

	          			<?php if ( $i == 0 ) : ?> | <?php endif; ?>
	          		</li>

	          		<?php $i++;
	          	endforeach; ?>
          	</ul>
    	</div>

    <?php }

	public function advanced_filters() {

		$start_date = isset( $_GET['start-date'] )  ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] )    ? sanitize_text_field( $_GET['end-date'] )   : null;
		$service_date   = isset( $_GET['service-date'] )    ? sanitize_text_field( $_GET['service-date'] )   : null;
		$status     = isset( $_GET['status'] )      ? $_GET['status'] : '';

		$all_gateways     = pl8app_get_payment_gateways();
		$gateways         = array();
		$selected_gateway = isset( $_GET['gateway'] ) ? sanitize_text_field( $_GET['gateway'] ) : 'all';

		if ( ! empty( $all_gateways ) ) {
			$gateways['all'] = __( 'All Gateways', 'pl8app' );

			foreach( $all_gateways as $slug => $admin_label ) {
				$gateways[ $slug ] = $admin_label['admin_label'];
			}
		}

		/**
		 * Allow gateways that aren't registered the standard way to be displayed in the dropdown.
		 *
		 * @since  1.0.0
		 */
		$gateways = apply_filters( 'pl8app_payments_table_gateways', $gateways );
		?>
		<div id="pl8app-payment-filters">
			<span id="pl8app-payment-date-filters">
				<span>
					<label for="start-date"><?php _e( 'Start Date:', 'pl8app' ); ?></label>
					<input type="text" id="start-date" name="start-date" class="pl8app_datepicker" value="<?php echo $start_date; ?>" placeholder="mm/dd/yyyy"/>
				</span>
				<span>
					<label for="end-date"><?php _e( 'End Date:', 'pl8app' ); ?></label>
					<input type="text" id="end-date" name="end-date" class="pl8app_datepicker" value="<?php echo $end_date; ?>" placeholder="mm/dd/yyyy"/>
				</span>
			</span>
			<span id="pl8app-payment-gateway-filter">
				<?php
				if ( ! empty( $gateways ) ) {
					echo PL8PRESS()->html->select( array(
						'options'          => $gateways,
						'name'             => 'gateway',
						'id'               => 'gateway',
						'selected'         => $selected_gateway,
						'show_option_all'  => false,
						'show_option_none' => false
					) );
				}
				?>
			</span>
			<span>/</span>
			<span>
				<label for="service-date-filter"><?php _e( 'Service Date:', 'pl8app' ); ?></label>
				<input type="text" id="service-date-filter" name="service-date" class="pl8app_datepicker" value="<?php echo $service_date; ?>" placeholder="mm/dd/yyyy"/>
			</span>
			<span id="pl8app-payment-after-core-filters">
				<?php do_action( 'pl8app_payment_advanced_filters_after_fields' ); ?>
				<input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'pl8app' ); ?>"/>
			</span>
			<?php if( ! empty( $status ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>"/>
			<?php endif; ?>
			<?php if( ! empty( $service_date ) || ! empty( $start_date ) || ! empty( $end_date ) || 'all' !== $selected_gateway ) : ?>
				<a href="<?php echo admin_url( 'admin.php?page=pl8app-payment-history' ); ?>" class="button-secondary"><?php _e( 'Clear Filter', 'pl8app' ); ?></a>
			<?php endif; ?>
			<?php do_action( 'pl8app_payment_advanced_filters_row' ); ?>
			<?php $this->search_box( __( 'Search', 'pl8app' ), 'pl8app-payments' ); ?>
		</div>

	<?php
	}

	/**
	 * Show the search field
	 *
	 * @since  1.0.0
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<?php do_action( 'pl8app_payment_history_search' ); ?>
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?><br/>
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @since  1.0.0
	 * @return array $views All the views available
	 */
	public function get_views() {

		$current          = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count      = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$completed_count   = '&nbsp;<span class="count">(' . $this->completed_count . ')</span>';
		$pending_count    = '&nbsp;<span class="count">(' . $this->pending_count  . ')</span>';
		$paid_count = '&nbsp;<span class="count">(' . $this->paid_count  . ')</span>';
		$out_for_deliver_count = '&nbsp;<span class="count">(' . $this->out_for_deliver_count . ')</span>';
		$views = array(
			'all'        => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array( 'status', 'paged' ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All','pl8app' ) . $total_count ),
			'pending'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'pending', 'paged' => FALSE ) ), $current === 'pending' ? ' class="current"' : '', __('Pending','pl8app' ) . $pending_count ),
			'paid' => sprintf('<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'publish', 'paged' => FALSE ) ), $current === 'paid' ? ' class="current"' : '', __('Paid','pl8app' ) . $paid_count ),
			'processing' => sprintf('<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'processing', 'paged' => FALSE ) ), $current === 'processing' ? ' class="current"' : '', __('Processing','pl8app' ) . $out_for_deliver_count)
		);

		return apply_filters( 'pl8app_payments_table_views', $views );
	}

	public function get_order_views() {
        $current          = isset( $_GET['order_status'] ) ? $_GET['order_status'] : '';
        $order_statuses = pl8app_get_order_statuses();

        $order_views = array(
            'all'        => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array( 'order_status', 'paged' ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All','pl8app' ) .'&nbsp;<span class="count">('  . (!empty($this->order_statues_count['total'])?$this->order_statues_count['total']: 0) . ')</span>'),
        );
        foreach( $order_statuses as $status_id => $status_label ) {
//            if($this->order_statues_count[$status_id] > 0)
            $order_views[$status_id] = sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'order_status'=> $status_id , 'paged' => FALSE) ), $current === $status_id ? ' class="current"' : '', $status_label . '&nbsp;<span class="count">(' . (!empty($this->order_statues_count[$status_id])? $this->order_statues_count[$status_id] : 0) . ')</span>' );
        }

        return apply_filters( 'pl8app_payments_table_order_statues_views', $order_views );
    }

    /**
     * Displays the list of views available on this table.
     *
     * @since 3.1.0
     */
    public function views() {
        $payment_statue_views = $this->get_views();
        $order_statue_views = $this->get_order_views();
        /**
         * Filters the list of available list table views.
         *
         * The dynamic portion of the hook name, `$this->screen->id`, refers
         * to the ID of the current screen.
         *
         * @since 3.1.0
         *
         * @param string[] $views An array of available list table views.
         */
        $views = apply_filters( "views_{$this->screen->id}", $payment_statue_views );

        if ( empty( $views ) ) {
            return;
        }

        $this->screen->render_screen_reader_content( 'heading_views' );

        echo "<div><ul class='subsubsub'>\n";
        echo '<li>Filter by Payment Status:  </li>';
        foreach ( $payment_statue_views as $class => $view ) {
            $payment_statue_views[ $class ] = "\t<li class='$class'>$view";
        }

        echo implode( " |</li>\n", $payment_statue_views ) . "</li>\n";
        echo '</ul></div>';

        echo "<div style='clear: both;'><ul class='subsubsub'>\n";
        echo '<li>Filter by Order Status:  </li>';
        foreach ( $order_statue_views as $class => $view ) {
            $order_statue_views[ $class ] = "\t<li class='$class'>$view";
        }

        echo implode( " |</li>\n", $order_statue_views ) . "</li>\n";
        echo '</ul></div>';
    }

	/**
	 * Retrieve the table columns
	 *
	 * @since  1.0.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb' 						=> '<input type="checkbox" />', //Render a checkbox instead of text
			'ID' 						=> __( 'Order', 'pl8app' ),
  		'date' 					=> __( 'Order Date', 'pl8app' ),
  		'service_date' 	=> __( 'Service Date', 'pl8app' ),
  		'status' 				=> __( 'Payment Status', 'pl8app' ),
  		'order_status' 	=> __( 'Order Status', 'pl8app' ),
  		'amount' 				=> __( 'Amount', 'pl8app' ),
		);

		return apply_filters( 'pl8app_payments_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @since  1.0.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'ID'     => array( 'ID', true ),
			'amount' => array( 'amount', false ),
			'date'   => array( 'date', false ),
		);
		return apply_filters( 'pl8app_payments_table_sortable_columns', $columns );
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
		return 'ID';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since  1.0.0
	 *
	 * @param array $payment Contains all the data of the payment
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $payment, $column_name ) {

		switch ( $column_name ) {

			case 'amount' :
				$amount  = $payment->total;
        $amount  = ! empty( $amount ) ? $amount : 0;
        $value   = pl8app_currency_filter( pl8app_format_amount( $amount ), pl8app_get_payment_currency_code( $payment->ID ) );
				break;

			case 'date' :
				$date    = strtotime( $payment->date );
        $value   = date_i18n( get_option( 'date_format' ), $date );
				break;

			case 'service_date' :
				$service_date = get_post_meta( $payment->ID, '_pl8app_delivery_date', true );
				$service_date = pl8app_local_date( $service_date );
				$service_time = get_post_meta( $payment->ID, '_pl8app_delivery_time', true );
    		$value   = !empty( $service_time ) ? $service_date . ', ' . $service_time : $service_date;
				break;

			case 'status' :
		  	$status = pl8app_get_payment_status_label( $payment->post_status );
		    $statuses = pl8app_get_payment_statuses();
		    $status_label = '<mark class="payment-status status-' . $payment->post_status . '" >';
		    $status_label .= '<span> ' . $status . '</span>';
		    $status_label .= '</mark>';
		    $value = $status_label;
				break;

			case 'order_status' :
	      $order_statuses = pl8app_get_order_statuses();
	      $current_order_status = pl8app_get_order_status( $payment->ID );
		    $options = '<select data-payment-id="'.$payment->ID.'" data-current-status="'.$current_order_status.'" name="pla_order_status" class="pla_order_status pla_current_status_'.$current_order_status.'">';

		    foreach( $order_statuses as $status_id => $status_label ) {
		    	$options .= '<option value="' . $status_id  . '" ' . pla_selected( $current_order_status, $status_id, false ) . '>' . $status_label . '</option>';
		    }

		    $options .= '</select>';
		    $options .= '<span class="order-status-loading"></span>';
		    $value = $options;
		    break;

			default:
				$value = isset( $payment->$column_name ) ? $payment->$column_name : '';
				break;
		}

		return apply_filters( 'pl8app_payments_table_column', $value, $payment->ID, $column_name );
	}

	/**
	 * Render the Email Column
	 *
	 * @since  1.0.0
	 * @param array $payment Contains all the data of the payment
	 * @return string Data shown in the Email column
	 */
	public function column_email( $payment ) {

		$row_actions = array();

		$email = pl8app_get_payment_user_email( $payment->ID );

		// Add search term string back to base URL
		$search_terms = ( isset( $_GET['s'] ) ? trim( $_GET['s'] ) : '' );
		if ( ! empty( $search_terms ) ) {
			$this->base_url = add_query_arg( 's', $search_terms, $this->base_url );
		}

		if ( pl8app_is_payment_complete( $payment->ID ) && ! empty( $email ) ) {
			$row_actions['email_links'] = '<a href="' . add_query_arg( array( 'pl8app-action' => 'email_links', 'purchase_id' => $payment->ID ), $this->base_url ) . '">' . __( 'Resend Purchase Receipt', 'pl8app' ) . '</a>';
		}

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'pl8app-action' => 'delete_payment', 'purchase_id' => $payment->ID ), $this->base_url ), 'pl8app_payment_nonce') . '">' . __( 'Delete', 'pl8app' ) . '</a>';

		$row_actions = apply_filters( 'pl8app_payment_row_actions', $row_actions, $payment );

		if ( empty( $email ) ) {
			$email = __( '(unknown)', 'pl8app' );
		}

		$value = $email . $this->row_actions( $row_actions );

		return apply_filters( 'pl8app_payments_table_column', $value, $payment->ID, 'email' );
	}

	/**
	 * Render the checkbox column
	 *
	 * @since  1.0.0
	 * @param array $payment Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	public function column_cb( $payment ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'payment',
			$payment->ID
		);
	}

	/**
	 * Render the ID column
	 *
	 * @since  1.0.0
	 * @param array $payment Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	public function column_ID( $payment ) {

		$customer_id = pl8app_get_payment_customer_id( $payment->ID );
		$cust_name = '';

	    if( ! empty( $customer_id ) ) {
	      $customer  = new pl8app_Customer( $customer_id );
	      $cust_name = $customer->name;
	    }

		$payment_meta = $payment->get_meta();

	    $customer_name = is_array( $payment_meta['user_info'] ) ? $payment_meta['user_info']['first_name'] . ' ' . $payment_meta['user_info']['last_name'] : $cust_name;

	    $service_type = pl8app_get_service_type( $payment->ID );

	    $order_preview = '<a href="#" class="order-preview" data-order-id="' . absint( $payment->ID ) . '" title="' . esc_attr( __( 'Preview', 'pl8app' ) ) . '"><span>' . esc_html( __( 'Preview', 'pl8app' ) ) . '</span></a>
	      <a class="" href="' . add_query_arg( 'id', $payment->ID, admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details' ) ) . '">#' . $payment->ID . ' ' . $customer_name . '</a><span class="pl8app-service-type badge-' . $service_type . ' ">' . pl8app_service_label( $service_type ) . '</span>';

	    return $order_preview;
	}

	/**
	 * Render the Customer Column
	 *
	 * @since 2.4
	 * @param array $payment Contains all the data of the payment
	 * @return string Data shown in the User column
	 */
	public function column_customer( $payment ) {

		$customer_id = pl8app_get_payment_customer_id( $payment->ID );

		if( ! empty( $customer_id ) ) {
			$customer    = new pl8app_Customer( $customer_id );
			$value = '<a href="' . esc_url( admin_url( "admin.php?page=pl8app-customers&view=overview&id=$customer_id" ) ) . '">' . $customer->name . '</a>';
		} else {
			$email = pl8app_get_payment_user_email( $payment->ID );
			$value = '<a href="' . esc_url( admin_url( "admin.php?page=pl8app-payment-history&s=$email" ) ) . '">' . __( '(customer missing)', 'pl8app' ) . '</a>';
		}
		return apply_filters( 'pl8app_payments_table_column', $value, $payment->ID, 'user' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since  1.0.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                 				 => __( 'Delete',				'pl8app' ),
			'set-payment-status-pending'     => __( 'Set Payment To Pending',		'pl8app' ),
			'set-payment-status-processing'  => __( 'Set Payment To Processing',	'pl8app' ),
			'set-payment-status-refunded'    => __( 'Set Payment To Refunded',		'pl8app' ),
			'set-payment-status-paid'     	 => __( 'Set Payment To Paid',        'pl8app' ),
			'set-payment-status-failed'      => __( 'Set Payment To Failed',		'pl8app' ),
		);

		$order_statuses = pl8app_get_order_statuses();

		$order_actions = array();

		if ( !empty( $order_statuses ) ) {

			foreach( $order_statuses as $status => $name ) {
				$order_actions[ 'set-order-status-' . $status  ] = sprintf( __( 'Set Order To %s', 'pl8app' ), $name );
			}

		}

		$order_actions['resend-receipt'] = __( 'Resend Email Receipts','pl8app' );

		$actions = array_merge( $actions, $order_actions );


		return apply_filters( 'pl8app_payments_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function process_bulk_action() {

		$ids    = isset( $_GET['payment'] ) ? $_GET['payment'] : false;
		$action = $this->current_action();

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		if( empty( $action ) )
			return;

		foreach ( $ids as $id ) {
			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {
				pl8app_delete_purchase( $id );
			}

			if ( 'set-payment-status-publish' === $this->current_action() ) {
				pl8app_update_payment_status( $id, 'publish' );
			}

			if ( 'set-payment-status-pending' === $this->current_action() ) {
				pl8app_update_payment_status( $id, 'pending' );
			}

			if ( 'set-payment-status-processing' === $this->current_action() ) {
				pl8app_update_payment_status( $id, 'processing' );
			}

			if ( 'set-payment-status-refunded' === $this->current_action() ) {
				pl8app_update_payment_status( $id, 'refunded' );
			}

			if ( 'set-payment-status-paid' === $this->current_action() ) {
				pl8app_update_payment_status( $id, 'publish' );
			}

			if ( 'set-payment-status-failed' === $this->current_action() ) {
				pl8app_update_payment_status( $id, 'failed' );
			}

			if ( 'set-payment-status-abandoned' === $this->current_action() ) {
				pl8app_update_payment_status( $id, 'abandoned' );
			}

			if( 'resend-receipt' === $this->current_action() ) {
				pl8app_email_purchase_receipt( $id, false );
			}

			$order_statuses = pl8app_get_order_statuses();

			$order_actions = array();

			if ( !empty( $order_statuses ) ) {
				$order_status = array_keys( $order_statuses );

				foreach( $order_status as $new_status ) {

					if ( 'set-order-status-'.$new_status === $this->current_action() ) {
						pl8app_update_order_status( $id, $new_status );
					}
				}

			}

			do_action( 'pl8app_payments_table_do_bulk_action', $id, $this->current_action() );
		}

	}

	/**
	 * Retrieve the payment counts
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function get_payment_counts() {

		global $wp_query;

		$args = array();

		if( isset( $_GET['user'] ) ) {
			$args['user'] = urldecode( $_GET['user'] );
		} elseif( isset( $_GET['customer'] ) ) {
			$args['customer'] = absint( $_GET['customer'] );
		} elseif( isset( $_GET['s'] ) ) {

			$is_user  = strpos( $_GET['s'], strtolower( 'user:' ) ) !== false;

			if ( $is_user ) {
				$args['user'] = absint( trim( str_replace( 'user:', '', strtolower( $_GET['s'] ) ) ) );
				unset( $args['s'] );
			} else {
				$args['s'] = sanitize_text_field( $_GET['s'] );
			}
		}

		if ( ! empty( $_GET['start-date'] ) ) {
			$args['start-date'] = urldecode( $_GET['start-date'] );
		}

		if ( ! empty( $_GET['end-date'] ) ) {
			$args['end-date'] = urldecode( $_GET['end-date'] );
		}

		if ( ! empty( $_GET['gateway'] ) && $_GET['gateway'] !== 'all' ) {
			$args['gateway'] = $_GET['gateway'];
		}

		if ( ! empty( $_GET['service-date'] ) ) {
			$args['service-date'] = urldecode( $_GET['service-date'] );
		}

		$payment_count          	= pl8app_count_payments( $args );
		$this->order_statues_count = $payment_count->order_statues_count;
		$this->completed_count   	= (isset($payment_count->completed))? $payment_count->completed : 0;
		$this->pending_count    	=  (isset($payment_count->pending)) ? $payment_count->pending : 0 ;
		$this->paid_count 			=  (isset($payment_count->publish)) ? $payment_count->publish : 0 ;
		$this->out_for_deliver_count   	=  (isset( $payment_count->processing ) ) ? $payment_count->processing : 0 ;

		$this->total_count = intval( $this->completed_count ) + intval( $this->pending_count ) + intval( $this->paid_count ) + intval( $this->out_for_deliver_count );
		// foreach( $payment_count as $count ) {
		// 	$this->total_count += $count;
		// }
	}

	/**
	 * Retrieve all the data for all the payments
	 *
	 * @since  1.0.0
	 * @return array $payment_data Array of all the data for the payments
	 */
	public function payments_data() {

		$per_page   = $this->per_page;
		$orderby    = isset( $_GET['orderby'] )     ? urldecode( $_GET['orderby'] )              : 'ID';
		$order      = isset( $_GET['order'] )       ? $_GET['order']                             : 'DESC';
		$user       = isset( $_GET['user'] )        ? $_GET['user']                              : null;
		$customer   = isset( $_GET['customer'] )    ? $_GET['customer']                          : null;
		$status     = isset( $_GET['status'] )      ? $_GET['status']                            : pl8app_get_payment_status_keys();
		$meta_key   = isset( $_GET['meta_key'] )    ? $_GET['meta_key']                          : null;
		$year       = isset( $_GET['year'] )        ? $_GET['year']                              : null;
		$month      = isset( $_GET['m'] )           ? $_GET['m']                                 : null;
		$day        = isset( $_GET['day'] )         ? $_GET['day']                               : null;
		$search     = isset( $_GET['s'] )           ? sanitize_text_field( $_GET['s'] )          : null;
		$start_date = isset( $_GET['start-date'] )  ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] )    ? sanitize_text_field( $_GET['end-date'] )   : $start_date;
		$gateway    = isset( $_GET['gateway'] )     ? sanitize_text_field( $_GET['gateway'] )    : null;
        $order_statue    = isset( $_GET['order_status'] )     ? $_GET['order_status']    : null;
		$service_date = isset( $_GET['service-date'] )  ? sanitize_text_field( $_GET['service-date'] ) : null;

		/**
		 * Introduced as part of #6063. Allow a gateway to specified based on the context.
		 *
		 * @since  1.0.0
		 *
		 * @param string $gateway
		 */
		$gateway = apply_filters( 'pl8app_payments_table_search_gateway', $gateway );

		if( ! empty( $search ) ) {
			$status = 'any'; // Force all payment statuses when searching
		}

		if ( $gateway === 'all' ) {
			$gateway = null;
		}

		$args = array(
			'output'     => 'payments',
			'number'     => $per_page,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'orderby'    => $orderby,
			'order'      => $order,
			'user'       => $user,
			'customer'   => $customer,
			'status'     => $status,
			'meta_key'   => $meta_key,
			'year'       => $year,
			'month'      => $month,
			'day'        => $day,
			's'          => $search,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'gateway'    => $gateway,
			'service_date' => $service_date,
            'order_statue' => $order_statue,
		);

		if( is_string( $search ) && false !== strpos( $search, 'txn:' ) ) {

			$args['search_in_notes'] = true;
			$args['s'] = trim( str_replace( 'txn:', '', $args['s'] ) );

		}

		$p_query  = new pl8app_Payments_Query( $args );

		return $p_query->get_payments();

	}

	/**
	 * Setup the final data for the table
	 *
	 * @since  1.0.0
	 * @uses pl8app_Payment_History_Table::get_columns()
	 * @uses pl8app_Payment_History_Table::get_sortable_columns()
	 * @uses pl8app_Payment_History_Table::payments_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {

		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();
		$data     = $this->payments_data();
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		$this->_column_headers = array( $columns, $hidden, $sortable );


		switch ( $status ) {
			case 'completed':
				$total_items = $this->completed_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'paid':
				$total_items = $this->paid_count;
			break;
			case 'any':
				$total_items = $this->total_count;
				break;
			default:
				// Retrieve the count of the non-default-pl8app status
				$count       = wp_count_posts( 'pl8app_payment' );
				$total_items = $count->{$status};
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}

	/**
   	 * Get total items in the order
     *
   	 * @since 3.0
     */
	public function pl8app_get_order_total_items( $payment ) {

    	$cart_items = $payment->cart_details;
    	$quantity = 0;
    	$quantity_data = array();

    	if ( is_array( $cart_items ) ) {
      		foreach( $cart_items as $cart_item ) {
        		array_push( $quantity_data, $cart_item['quantity'] );
      		}
    	}

    	$quantity = array_sum( $quantity_data );
    	return $quantity;
  	}

  	public static function get_service_type_count( $service_type = '' ) {
	    global $wpdb;

	    $query_args = array(
	      'post_type'       => 'pl8app_payment',
	      'posts_per_page'  => -1,
	      'meta_query'  => array(
	        array(
	          'key' => '_pl8app_delivery_type',
	          'value' => array( $service_type ),
	        ),
	      ),
	    );


	    $get_total = new WP_Query( $query_args );
	    $totalpost = !empty( $get_total->found_posts ) ? $get_total->found_posts : 0;
	    return $totalpost;
	}

  	/**
     * Get order details by payment id to send to the ajax endpoint for previews.
     *
     * @param  pla_Payment $order Order object.
     * @return array
     */
  	public static function order_preview_get_order_details( $payment ) {

	    if ( ! $payment ) {
	      	return array();
	    }

	    $payment_via = $customer_name = $customer_email = $phone = $flat = $landmark = $customer_location = $order_menuitem = '';

	    $gateway  = $payment->gateway;

	    if ( $gateway ) {
	      	$payment_via = pl8app_get_gateway_admin_label( $gateway );
	    }

	    if ( !empty( $payment->customer_id ) ) {

	    	$customer  = new pl8app_Customer( $payment->customer_id );
	    	$payment_meta = $payment->get_meta();

	    	$customer_name = is_array( $payment_meta['user_info'] ) ? $payment_meta['user_info']['first_name'] . ' ' . $payment_meta['user_info']['last_name'] : $customer->name;
	    	$customer_email = is_array( $payment_meta['user_info'] ) ? $payment_meta['user_info']['email'] : $customer->email;

	    	$delivery_address_meta = get_post_meta( $payment->ID, '_pl8app_delivery_address', true );
		    $phone  = !empty( $payment_meta['phone'] ) ? $payment_meta['phone'] : (!empty( $delivery_address_meta['phone'] ) ? $delivery_address_meta['phone'] :  '');
		    $flat   = !empty( $delivery_address_meta['flat'] ) ? $delivery_address_meta['flat'] : '';
		    $city = !empty( $delivery_address_meta['city'] ) ? $delivery_address_meta['city'] : '';
		    $postcode = !empty( $delivery_address_meta['postcode'] ) ? $delivery_address_meta['postcode'] : '';
		    $customer_address = !empty( $delivery_address_meta['address'] ) ? $delivery_address_meta['address'] : '';

    		$customer_details = array(
				'phone'      => $phone,
				'flat'       => $flat,
				'postcode'   => $postcode,
				'city'       => $city,
				'address'    => $customer_address
    		);
	    }

	    $user_info      = $payment->user_info;
	    $billing_address = isset( $user_info['address'] ) ? $user_info['address'] : '';
	    $service_type = pl8app_get_service_type( $payment->ID );
  		$service_date = $payment->get_meta( '_pl8app_delivery_date' );
  		$service_date = !empty( $service_date ) ? pl8app_local_date( $service_date ) : '';
  		$service_time = $payment->get_meta( '_pl8app_delivery_time' );

	    return apply_filters(
	      	'pl8app_admin_order_preview_get_order_details',
	      	array(
		        'id'                        => $payment->ID,
		        'service_type'              => pl8app_service_label($service_type),
		        'service_type_slug'         => $service_type,
		        'service_date'              => $service_date,
		        'service_time'              => $service_time,
		        'status'                    => pl8app_get_order_status( $payment->ID ),
		        'payment_via'               => $payment_via,
		        'customer_name'             => $customer_name,
		        'customer_email'            => $customer_email,
		        'customer_details'          => $customer_details,
		        'customer_billing_details'  => $user_info,
		        'item_html'                 => self::get_ordered_items( $payment ),
		        'actions_html'              => self::get_order_preview_actions_html( $payment ),
		        'formatted_billing_address' => $billing_address,
	      	), $payment
	    );
  	}



  	/**
     * Get all the item details from the payment object
     *
     * @param  pla_Payment $payment Payment Object.
     * @return html
     */
  	public static function get_ordered_items( $payment ) {

    	$order_items = $payment->cart_details;

    	if ( is_array( $order_items ) &&  !empty( $order_items )  ) {

      		ob_start(); ?>

      	<div class="pl8app-order-preview-table-wrapper">
        	<table cellspacing="0" class="pl8app-order-preview-table">
          		<thead>
            		<tr>
              			<th class="pl8app-order-preview-table__column--product">
                			<?php esc_html_e( 'MenuItem(s)', 'pl8app' ); ?>
              			</th>
              		<th class="pl8app-order-preview-table__column--price-quantity">
                		<?php esc_html_e( 'Price & Quantity', 'pl8app' ); ?>
              		</th>

              		<?php if ( pl8app_use_taxes() ) : ?>
                	<th class="pl8app-order-preview-table__column--tax">
                  		<?php esc_html_e( 'Tax', 'pl8app' ); ?>
                	</th>
              		<?php endif; ?>

              		<th class="pl8app-order-preview-table__column--price">
                		<?php esc_html_e( 'Total', 'pl8app' ); ?>
              		</th>
            	</tr>
          	</thead>
          	<tbody>
            	<?php foreach( $order_items as $menuitems ) :
            		$special_instruction = isset( $menuitems['instruction'] ) ? $menuitems['instruction'] : '';
            		if ( isset( $menuitems['name'] ) ) :
            			$item_tax   = isset( $menuitems['tax'] ) ? $menuitems['tax'] : 0;
            			$price      = isset( $menuitems['price'] ) ? $menuitems['price'] : false; ?>

            		<tr class="pl8app-order-preview-table">
						<td class="pl8app-order-preview-table__column--product">
							<?php echo pl8app_get_cart_item_name($menuitems); ?>
              			</td>
	              		<td class="pl8app-order-preview-table__column--quantity">
	                		<?php echo pl8app_currency_filter( pl8app_format_amount( $menuitems['item_price'] ) ) . ' X ' . $menuitems['quantity']; ?>
						</td>

	              		<?php if ( pl8app_use_taxes() ) : ?>
	                	<td class="pl8app-order-preview-table__column--tax">
	                  		<?php echo pl8app_currency_filter(pl8app_format_amount( $item_tax )); ?>
	                	</td>
	              		<?php endif; ?>

	              		<td class="pl8app-order-preview-table__column--price">
	                		<?php echo pl8app_currency_filter(pl8app_format_amount( $price )); ?>
	              		</td>
            		</tr>

            		<?php if ( !empty( $special_instruction ) ) : ?>
              		<tr class="pl8app-order-preview-table special-instruction">
                		<td colspan="3">
                  			<?php printf( __( 'Special Instruction : %s', 'pla_quick_view'), $special_instruction ); ?>
                		</td>
              		</tr>
            		<?php endif; ?>

            		<?php
            		if ( is_array( $menuitems['item_number']['options'] ) ) :
            			foreach( $menuitems['item_number']['options'] as $addon_items ) :
            				if( is_array( $addon_items ) ) :
			                    $addon_name = $addon_items['addon_item_name'];
			                    $addon_price = $addon_items['price'];
			                    $addon_quantity = $addon_items['quantity'];
                  	?>
                    <tr>
                      	<td>
                        	<?php echo $addon_name; ?>
                      	</td>
                      	<td>
                        	<?php echo pl8app_currency_filter( pl8app_format_amount( $addon_price ), pl8app_get_payment_currency_code( $payment->ID ) ) . ' X ' . $addon_quantity; ?>
                      	</td>

                      	<?php if ( pl8app_use_taxes() ) : ?>
                        <td>
                         	<?php echo pl8app_currency_filter( pl8app_format_amount( '0' )); ?>
                        </td>
                      	<?php endif; ?>
                      	<td>
                        	<?php echo pl8app_currency_filter( pl8app_format_amount( $addon_price )); ?>
                      	</td>
                    </tr>
                    		<?php endif;
                		endforeach;
              		endif;
              	endif;
            endforeach; ?>
	          		</tbody>
	        	</table>
	      	</div>
	      	<?php
	      	$output = ob_get_contents();
	      	ob_clean();
    	}
    	return $output;
  	}

	/**
     * Get actions to display in the preview as HTML.
     *
     * @param  pla_Payment Payment object.
     * @return string
     */
  	public static function get_order_preview_actions_html( $payment ) {

    	$actions        = array();
	    $status_actions = array();

	    $payment_status = pl8app_get_order_status( $payment->ID );

	    if ( $payment_status == 'pending' ) {
	      $status_actions['processing'] = array(
	        'name'        => __( 'Processing', 'pl8app' ),
	        'payment_id'  => $payment->ID,
	        'action'      => 'processing',
	        'url'         => wp_nonce_url( admin_url( 'admin-ajax.php?action=pl8app_update_order_status&status=processing&current_status=' . $payment_status . '&payment_id=' . $payment->ID ), 'pl8app-mark-order-status' ),
	      );
	    }

	    if ( ( $payment_status == 'processing' || $payment_status == 'pending' ) ) {
	      $status_actions['completed'] = array(
	        'name'        => __( 'Completed', 'pl8app' ),
	        'payment_id'  => $payment->ID,
	        'action'      => 'completed',
	        'url'         => wp_nonce_url( admin_url( 'admin-ajax.php?action=pl8app_update_order_status&status=completed&current_status=' . $payment_status. '&payment_id=' . $payment->ID ), 'pl8app-mark-order-status' ),
	      );
	    }

	    if ( $status_actions ) {
	      $actions['status'] = array(
	        'group'   => __( 'Change order status: ', 'pl8app' ),
	        'actions' => $status_actions,
	      );
	    }

    	return pla_render_action_buttons( apply_filters( 'pl8app_admin_order_preview_actions', $actions, $payment ) );
  	}

  	/**
     * Template for order preview.
     *
     * @since 3.0
     */
  	public function order_preview_template() { ?>

  		<script type="text/template" id="tmpl-pl8app-modal-view-order">
      	<div class="pl8app-backbone-modal pl8app-order-preview">
        <div class="pl8app-backbone-modal-content">
          <section class="pl8app-backbone-modal-main" role="main">
            <header class="pl8app-backbone-modal-header">
              <mark class="order-status status-{{ data.status }}"><span>{{ data.status }}</span></mark>

              <?php /* translators: %s: order ID */ ?>
              <h1><?php echo esc_html( sprintf( __( 'Order #%s', 'pl8app' ), '{{ data.id }}' ) ); ?></h1>

              <# if ( data.service_type_slug !== '' ) { #>
                <mark class="service-type badge-{{ data.service_type_slug }}"><span>{{ data.service_type }}</span></mark>
              <# } #>

              <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                <span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'pl8app' ); ?></span>
              </button>
            </header>

            <?php esc_html_e( get_post_status( '{{data.id}}' ) ); ?>

            <article>
              <?php do_action( 'pl8app_admin_order_preview_start' ); ?>
              <div class="pl8app-order-preview-wrapper">
                <div class="pl8app-order-preview">
                  <# if ( data.customer_details.address ) { #>
                    <div class="pl8app-order-preview-address">
                      <h2><?php esc_html_e( sprintf( __( '%s address', 'pl8app' ), '{{ data.service_type }}' ) ); ?></h2>
                        {{ data.customer_details.address }}<br />
                        {{ data.customer_details.flat }}<br />
                        {{ data.customer_details.city }} {{ data.customer_details.postcode }}
                    </div>
                  <# } #>
                  <div class="pl8app-order-preview-customer-details">
                    <h2><?php esc_html_e( 'Customer details', 'pl8app' ); ?></h2>
                    <# if ( data.customer_name ) { #>
                      <strong><?php esc_html_e( 'Customer name', 'pl8app' ); ?></strong>
                    : <span>{{ data.customer_name }}</span>
                      <br/>
                    <# } #>

                    <# if ( data.customer_email ) { #>
                      <strong><?php esc_html_e( 'Email', 'pl8app' ); ?></strong>
                      : <a href="mailto:{{ data.customer_email }}">{{ data.customer_email }}</a>
                      <br/>
                    <# } #>

                    <# if ( data.customer_details.phone ) { #>
                      <strong><?php esc_html_e( 'Phone', 'pl8app' ); ?></strong>
                      : <a href="tel:{{{ data.customer_details.phone }}}">{{{ data.customer_details.phone }}}</a>
                      <br/>
                    <# } #>
                  </div>

                  <div class="pl8app-clear-fix"></div>

                  <div class="order-service-meta">

                    <# if ( data.payment_via ) { #>
                      <span>
                        <strong><?php esc_html_e( 'Payment via', 'pl8app' ); ?></strong> :
                        {{{ data.payment_via }}}
                      </span>
                    <# } #>

                    <# if ( data.service_date ) { #>
                      <span>
                      <strong><?php esc_html_e( 'Service date', 'pl8app' ); ?></strong> :
                      {{{ data.service_date }}}
                      </span>
                    <# } #>

                    <# if ( data.service_time ) { #>
                      <span>
                        <strong><?php esc_html_e( 'Service time', 'pl8app' ); ?></strong> :
                      {{{ data.service_time }}}
                    <# } #>
                    </span>
                  </div>

                </div>
                <?php do_action( 'pl8app_admin_order_preview_before_menuitems' ); ?>
                <br/>
                <# if ( data.item_html ) { #>
                  <div class="menuitems">
                    {{{ data.item_html }}}
                  </div>
                <# } #>

              </div>

              <?php do_action( 'pl8app_admin_order_preview_end' ); ?>
            </article>

            <footer>
              <div class="inner">

                <div class="pl8app-action-button-group">
                 {{{ data.actions_html }}}
                </div>

                <a class="button button-primary button-large" aria-label="<?php esc_attr_e( 'Edit this order', 'pl8app' ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=pl8app-payment-history&view=view-order-details' ) ); ?>&id={{ data.id }}"><?php esc_html_e( 'Edit', 'pl8app' ); ?></a>

              </div>
            </footer>

          </section>
        </div>
      </div>
      <div class="pl8app-backbone-modal-backdrop modal-close"></div>
    	</script>
  	<?php }
}