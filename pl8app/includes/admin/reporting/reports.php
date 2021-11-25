<?php
/**
 * Admin Reports Page
 *
 * @package     pl8app
 * @subpackage  Admin/Reports
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reports Page
 *
 * Renders the reports page contents.
 *
 * @since 1.0
 * @return void
*/
function pl8app_reports_page() {
	$current_page = admin_url( 'admin.php?page=pl8app-reports' );
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'reports';
	?>
	<div class="wrap">
		<h2><?php _e( 'pl8app Reports', 'pl8app' ); ?></h2>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo add_query_arg( array( 'tab' => 'reports', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Reports', 'pl8app' ); ?></a>
			<?php if ( current_user_can( 'export_shop_reports' ) ) { ?>
				<a href="<?php echo add_query_arg( array( 'tab' => 'export', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Export', 'pl8app' ); ?></a>
			<?php } ?>

			<?php do_action( 'pl8app_reports_tabs' ); ?>
		</h2>

		<?php
		do_action( 'pl8app_reports_page_top' );
		do_action( 'pl8app_reports_tab_' . $active_tab );
		do_action( 'pl8app_reports_page_bottom' );
		?>
	</div><!-- .wrap -->
	<?php
}

/**
 * MenuItem Import/Export Page
 *
 * Renders the MenuItem Import/Export page contents.
 *
 * @since 1.0
 * @return void
 */

function pl8app_menuitem_ex_import(){
    $current_page = admin_url( 'edit.php?post_type=menuitem&page=pl8app-menuitem-export-import' );
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'import';
    ?>
    <div class="wrap">
        <h2><?php _e( 'pl8app Reports', 'pl8app' ); ?></h2>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo add_query_arg( array( 'tab' => 'import', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'import' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Import', 'pl8app' ); ?></a>
            <?php if ( current_user_can( 'export_shop_reports' ) ) { ?>
                <a href="<?php echo add_query_arg( array( 'tab' => 'export', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Export', 'pl8app' ); ?></a>
            <?php } ?>

        </h2>

        <?php
        do_action( 'pl8app_menuitem_exim_tab_' . $active_tab );
        ?>
    </div><!-- .wrap -->
    <?php
}

/**
 * Default Report Views
 *
 * @since  1.0.0
 * @return array $views Report Views
 */
function pl8app_reports_default_views() {
	$views = array(
		'earnings'   => __( 'Earnings', 'pl8app' ),
		'categories' => __( 'Earnings by Category', 'pl8app' ),
		'addons' 	 => __( 'Earnings by Addon', 'pl8app' ),
		'menuitems'  => pl8app_get_label_plural(),
		'gateways'   => __( 'Payment Methods', 'pl8app' ),
		'taxes'      => __( 'Taxes', 'pl8app' ),
	);

	$views = apply_filters( 'pl8app_report_views', $views );

	return $views;
}

/**
 * Default Report Views
 *
 * Checks the $_GET['view'] parameter to ensure it exists within the default allowed views.
 *
 * @param string $default Default view to use.
 *
 * @since  1.0.0.6
 * @return string $view Report View
 *
 */
function pl8app_get_reporting_view( $default = 'earnings' ) {

	if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], array_keys( pl8app_reports_default_views() ) ) ) {
		$view = $default;
	} else {
		$view = $_GET['view'];
	}

	return apply_filters( 'pl8app_get_reporting_view', $view );
}

/**
 * Renders the Reports page
 *
 * @since 1.0
 * @return void
 */
function pl8app_reports_tab_reports() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'pl8app' ), __( 'Error', 'pl8app' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = pl8app_reports_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) )
		$current_view = $_GET['view'];

	do_action( 'pl8app_reports_view_' . $current_view );

}
add_action( 'pl8app_reports_tab_reports', 'pl8app_reports_tab_reports' );

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.0
 * @return void
 */
function pl8app_report_views() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	$views        = pl8app_reports_default_views();
	$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'earnings';
	?>
	<form id="pl8app-reports-filter" method="get">
		<select id="pl8app-reports-view" name="view">
			<option value="-1"><?php _e( 'Report Type', 'pl8app' ); ?></option>
			<?php foreach ( $views as $view_id => $label ) : ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>

		<?php do_action( 'pl8app_report_view_actions' ); ?>

		<input type="hidden" name="page" value="pl8app-reports"/>

		<?php submit_button( __( 'Show', 'pl8app' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
	do_action( 'pl8app_report_view_actions_after' );
}

/**
 * Renders the Reports pl8app Table
 *
 * @since 1.0
 * @uses pl8app_Menuitem_Reports_Table::prepare_items()
 * @uses pl8app_Menuitem_Reports_Table::display()
 * @return void
 */
function pl8app_reports_menuitems_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if( isset( $_GET['menuitem-id'] ) )
		return;

	include( dirname( __FILE__ ) . '/class-menuitem-reports-table.php' );

	$menuitems_table = new pl8app_Menuitem_Reports_Table();
	$menuitems_table->prepare_items();
	$menuitems_table->display();
}
add_action( 'pl8app_reports_view_menuitems', 'pl8app_reports_menuitems_table' );

/**
 * Renders the detailed report for a specific product
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_reports_menuitem_details() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if( ! isset( $_GET['menuitem-id'] ) )
		return;
?>
	<div class="tablenav top">
		<div class="actions bulkactions">
			<div class="alignleft">
				<?php pl8app_report_views(); ?>
			</div>&nbsp;
			<button onclick="history.go(-1);" class="button-secondary"><?php _e( 'Go Back', 'pl8app' ); ?></button>
		</div>
	</div>
<?php
	pl8app_reports_graph_of_menuitem( absint( $_GET['menuitem-id'] ) );
}
add_action( 'pl8app_reports_view_menuitems', 'pl8app_reports_menuitem_details' );


/**
 * Renders the Gateways Table
 *
 * @since 1.0
 * @uses pl8app_Gateawy_Reports_Table::prepare_items()
 * @uses pl8app_Gateawy_Reports_Table::display()
 * @return void
 */
function pl8app_reports_gateways_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-gateways-reports-table.php' );

	$menuitems_table = new pl8app_Gateawy_Reports_Table();
	$menuitems_table->prepare_items();
	$menuitems_table->display();
}
add_action( 'pl8app_reports_view_gateways', 'pl8app_reports_gateways_table' );


/**
 * Renders the Reports Earnings Graphs
 *
 * @since 1.0
 * @return void
 */
function pl8app_reports_earnings() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}
	?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php pl8app_report_views(); ?></div>
	</div>
	<?php
	pl8app_reports_graph();
}
add_action( 'pl8app_reports_view_earnings', 'pl8app_reports_earnings' );


/**
 * Renders the Reports Earnings By Category Table & Graphs
 *
 * @since 1.0
 */
function pl8app_reports_categories() {
	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-categories-reports-table.php' );
	?>
			<div class="inside">
				<?php

				$categories_table = new pl8app_Categories_Reports_Table();
				$categories_table->prepare_items();
				$categories_table->display();
				?>

				<?php echo $categories_table->load_scripts(); ?>

				<div class="pl8app-mix-totals">
					<div class="pl8app-mix-chart">
						<strong><?php _e( 'Category Sales Mix: ', 'pl8app' ); ?></strong>
						<?php $categories_table->output_sales_graph(); ?>
					</div>
					<div class="pl8app-mix-chart">
						<strong><?php _e( 'Category Earnings Mix: ', 'pl8app' ); ?></strong>
						<?php $categories_table->output_earnings_graph(); ?>
					</div>
				</div>

				<?php do_action( 'pl8app_reports_graph_additional_stats' ); ?>

				<p class="pl8app-graph-notes">
					<span>
						<em><sup>&dagger;</sup> <?php _e( 'All Parent categories include sales and earnings stats from child categories.', 'pl8app' ); ?></em>
					</span>
					<span>
						<em><?php _e( 'Stats include all sales and earnings for the lifetime of the store.', 'pl8app' ); ?></em>
					</span>
				</p>

			</div>
	<?php
}
add_action( 'pl8app_reports_view_categories', 'pl8app_reports_categories' );

/**
 * Renders the Reports Earnings By Addon Table & Graphs
 *
 * @since 1.0
 */
function pl8app_reports_addons() {
	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-addons-reports-table.php' );
	?>
			<div class="inside">
				<?php

				$categories_table = new pl8app_Addons_Reports_Table();
				$categories_table->prepare_items();
				$categories_table->display();
				?>

				<?php echo $categories_table->load_scripts(); ?>

				<div class="pl8app-mix-totals">
					<div class="pl8app-mix-chart">
						<strong><?php _e( 'Category Sales Mix: ', 'pl8app' ); ?></strong>
						<?php $categories_table->output_sales_graph(); ?>
					</div>
					<div class="pl8app-mix-chart">
						<strong><?php _e( 'Category Earnings Mix: ', 'pl8app' ); ?></strong>
						<?php $categories_table->output_earnings_graph(); ?>
					</div>
				</div>

				<?php do_action( 'pl8app_reports_graph_additional_stats' ); ?>

				<p class="pl8app-graph-notes">
					<span>
						<em><sup>&dagger;</sup> <?php _e( 'All Parent categories include sales and earnings stats from child categories.', 'pl8app' ); ?></em>
					</span>
					<span>
						<em><?php _e( 'Stats include all sales and earnings for the lifetime of the store.', 'pl8app' ); ?></em>
					</span>
				</p>

			</div>
	<?php
}
add_action( 'pl8app_reports_view_addons', 'pl8app_reports_addons' );

/**
 * Renders the Tax Reports
 *
 * @since 1.0.0
 * @return void
 */
function pl8app_reports_taxes() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	$year = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : date( 'Y' );
	?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php pl8app_report_views(); ?></div>
	</div>

	<div class="metabox-holder" style="padding-top: 0;">
		<div class="postbox">
			<h3><span><?php _e('Tax Report','pl8app' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'This report shows the total amount collected in sales tax for the given year.', 'pl8app' ); ?></p>
				<form method="get" action="<?php echo admin_url( 'admin.php' ); ?>">
					<span><?php echo $year; ?></span>: <strong><?php pl8app_sales_tax_for_year( $year ); ?></strong>&nbsp;&mdash;&nbsp;
					<select name="year">
						<?php for ( $i = 2009; $i <= date( 'Y' ); $i++ ) : ?>
						<option value="<?php echo $i; ?>"<?php selected( $year, $i ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>

					<input type="hidden" name="page" value="pl8app-reports" />
					<input type="hidden" name="view" value="taxes" />

					<?php submit_button( __( 'Submit', 'pl8app' ), 'secondary', 'submit', false ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- .metabox-holder -->
	<?php
}
add_action( 'pl8app_reports_view_taxes', 'pl8app_reports_taxes' );

/**
 * Renders the 'Export' tab on the Reports Page
 *
 * @since 1.0
 * @return void
 */
function pl8app_reports_tab_export() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}
	?>
	<div id="pl8app-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">

					<?php do_action( 'pl8app_reports_tab_export_content_top' ); ?>

					<div class="postbox pl8app-export-earnings-report">
						<h3><span><?php _e( 'Export Earnings Report', 'pl8app' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV giving a detailed look into earnings over time.', 'pl8app' ); ?></p>
							<form id="pl8app-export-earnings" class="pl8app-export-form pl8app-import-export-form" method="post">
								<?php echo PL8PRESS()->html->month_dropdown( 'start_month' ); ?>
								<?php echo PL8PRESS()->html->year_dropdown( 'start_year' ); ?>
								<?php echo _x( 'to', 'Date one to date two', 'pl8app' ); ?>
								<?php echo PL8PRESS()->html->month_dropdown( 'end_month' ); ?>
								<?php echo PL8PRESS()->html->year_dropdown( 'end_year' ); ?>
								<?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>
								<input type="hidden" name="pl8app-export-class" value="pl8app_Batch_Earnings_Report_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'pl8app' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox pl8app-export-payment-history">
						<h3><span><?php _e('Export Payment History','pl8app' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all payments recorded.', 'pl8app' ); ?></p>

							<form id="pl8app-export-payments" class="pl8app-export-form pl8app-import-export-form" method="post">
								<?php echo PL8PRESS()->html->date_field( array( 'id' => 'pl8app-payment-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'pl8app' ) )); ?>
								<?php echo PL8PRESS()->html->date_field( array( 'id' => 'pl8app-payment-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'pl8app' ) )); ?>
								<select name="status">
									<option value="any"><?php _e( 'All Statuses', 'pl8app' ); ?></option>
									<?php
									$statuses = pl8app_get_payment_statuses();
									foreach( $statuses as $status => $label ) {
										echo '<option value="' . $status . '">' . $label . '</option>';
									}
									?>
								</select>
								<?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>
								<input type="hidden" name="pl8app-export-class" value="pl8app_Batch_Payments_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'pl8app' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>

						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox pl8app-export-customers">
						<h3><span><?php _e('Export Customers in CSV','pl8app' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of Customers.', 'pl8app' ); ?></p>
							<form id="pl8app-export-customers" class="pl8app-export-form pl8app-import-export-form" method="post">
<!--								--><?php //echo PL8PRESS()->html->product_dropdown( array( 'name' => 'menuitem', 'id' => 'pl8app_customer_export_download', 'chosen' => true ) ); ?>
								<?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>
								<input type="hidden" name="pl8app-export-class" value="pl8app_Batch_Customers_Export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'pl8app' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox pl8app-export-menuitem-history">
						<h3><span><?php _e('Export Order History in CSV','pl8app' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of Menu Item Orders. To download a CSV for all Menu Items, leave "Choose a Menu Item" as it is.', 'pl8app' ); ?></p>
							<form id="pl8app-export-file-menuitems" class="pl8app-export-form pl8app-import-export-form" method="post">
								<?php echo PL8PRESS()->html->product_dropdown( array( 'name' => 'menuitem_id', 'id' => 'pl8app_file_menuitem_export_menuitem', 'chosen' => true ) ); ?>
								<?php echo PL8PRESS()->html->date_field( array( 'id' => 'pl8app-file-menuitem-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'pl8app' ) )); ?>
								<?php echo PL8PRESS()->html->date_field( array( 'id' => 'pl8app-file-menuitem-export-end', 'name' => 'end', 'placeholder' => __( 'Choose end date', 'pl8app' ) )); ?>
								<?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>
								<input type="hidden" name="pl8app-export-class" value="pl8app_Batch_File_Orders_Export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'pl8app' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox pl8app-export-payment-history">
						<h3><span><?php _e('Export Sales', 'pl8app' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all sales.', 'pl8app' ); ?></p>

							<form id="pl8app-export-sales" class="pl8app-export-form pl8app-import-export-form" method="post">
								<?php echo PL8PRESS()->html->product_dropdown( array( 'name' => 'menuitem_id', 'id' => 'pl8app_sales_export_menuitem', 'chosen' => true ) ); ?>
								<?php echo PL8PRESS()->html->date_field( array( 'id' => 'pl8app-sales-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'pl8app' ) )); ?>
								<?php echo PL8PRESS()->html->date_field( array( 'id' => 'pl8app-sales-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'pl8app' ) )); ?>
								<?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>
								<input type="hidden" name="pl8app-export-class" value="pl8app_Batch_Sales_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'pl8app' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>

						</div><!-- .inside -->
					</div><!-- .postbox -->

					<?php do_action( 'pl8app_reports_tab_export_content_bottom' ); ?>

				</div><!-- .post-body-content -->
			</div><!-- .post-body -->
		</div><!-- .metabox-holder -->
	</div><!-- #pl8app-dashboard-widgets-wrap -->
	<?php
}
add_action( 'pl8app_reports_tab_export', 'pl8app_reports_tab_export' );

/**
 * Renders the 'Export' tab on the MenuItem Export/Import Page
 *
 * @since 1.0
 * @return void
 */
function pl8app_menuitem_exim_tab_export(){
    if( ! current_user_can( 'manage_shop_settings' ) ) {
        return;
    }

    ?>
    <div id="pl8app-dashboard-widgets-wrap">
        <div class="metabox-holder">
            <div id="post-body">
                <div id="post-body-content">

                    <div class="postbox pl8app-export-menuitems">
                        <h3><span><?php _e('Export MenuItems in CSV','pl8app' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of Menu Items.', 'pl8app' ); ?></p>
                            <form id="pl8app-export-file-menuitems" class="pl8app-export-form pl8app-import-export-form" method="post">
                                <?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>
                                <input type="hidden" name="pl8app-export-class" value="pl8app_Batch_pl8app_Export"/>
                                <input type="submit" value="<?php _e( 'Generate CSV', 'pl8app' ); ?>" class="button-secondary"/>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox pl8app-export-categories">
                        <h3><span><?php _e('Export Categories in CSV','pl8app' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of Categories.', 'pl8app' ); ?></p>
                            <form id="pl8app-export-file-categories" class="pl8app-export-form pl8app-import-export-form" method="post">
                                <?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>
                                <input type="hidden" name="pl8app-export-class" value="pla8pp_Batch_pl8app_category_Export"/>
                                <input type="submit" value="<?php _e( 'Generate CSV', 'pl8app' ); ?>" class="button-secondary"/>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox pl8app-export-addons">
                        <h3><span><?php _e('Export Options and Upgrades in CSV','pl8app' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV of Options and Upgrades.', 'pl8app' ); ?></p>
                            <form id="pl8app-export-file-addons" class="pl8app-export-form pl8app-import-export-form" method="post">
                                <?php wp_nonce_field( 'pl8app_ajax_export', 'pl8app_ajax_export' ); ?>
                                <input type="hidden" name="pl8app-export-class" value="pla8pp_Batch_pl8app_addon_Export"/>
                                <input type="submit" value="<?php _e( 'Generate CSV', 'pl8app' ); ?>" class="button-secondary"/>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                </div>
            </div>
        </div>
    </div>
    <?php

}
add_action('pl8app_menuitem_exim_tab_export','pl8app_menuitem_exim_tab_export');

/**
 * Renders the 'Import' tab on the MenuItem Export/Import Page
 *
 * @since 1.0
 * @return void
 */
function pl8app_menuitem_exim_tab_import(){
    if( ! current_user_can( 'manage_shop_settings' ) ) {
        return;
    }

    ?>
    <div id="pl8app-dashboard-widgets-wrap">
        <div class="metabox-holder">
            <div id="post-body">
                <div id="post-body-content">


                    <div class="postbox pl8app-import-menuitems">
                        <h3><span><?php _e( 'Import Menu Items', 'pl8app' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Import a CSV file of Menu Items.', 'pl8app' ); ?></p>
                            <form id="pl8app-import-menuitems" class="pl8app-import-form pl8app-import-export-form" action="<?php echo esc_url( add_query_arg( 'pl8app_action', 'upload_import_file', admin_url() ) ); ?>" method="post" enctype="multipart/form-data">

                                <div class="pl8app-import-file-wrap">
                                    <?php wp_nonce_field( 'pl8app_ajax_import', 'pl8app_ajax_import' ); ?>
                                    <input type="hidden" name="pl8app-import-class" value="pl8app_Batch_MenuItems_Import"/>
                                    <p>
                                        <input name="pl8app-import-file" id="pl8app-menuitems-import-file" type="file" />
                                    </p>
                                    <span>
                                        <input type="submit" value="<?php _e( 'Import CSV', 'pl8app' ); ?>" class="button-secondary"/>
                                        <span class="spinner"></span>
                                    </span>
                                </div>

                                <div class="pl8app-import-options" id="pl8app-import-menuitems-options" style="display:none;">

                                    <p>
                                        <?php
                                        printf(
                                            __( 'Each column loaded from the CSV needs to be mapped to a Menu Item field. Select the column that should be mapped to each field below. Any columns not needed can be ignored.', 'pl8app' )
                                        );
                                        ?>
                                    </p>

                                    <table class="widefat pl8app_repeatable_table striped" width="100%" cellpadding="0" cellspacing="0">
                                        <thead>
                                        <tr>
                                            <th><strong><?php _e( 'Product Field', 'pl8app' ); ?></strong></th>
                                            <th><strong><?php _e( 'CSV Column', 'pl8app' ); ?></strong></th>
                                            <th><strong><?php _e( 'Data Preview', 'pl8app' ); ?></strong></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php _e( 'Product  Id', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[post_id]" class="pl8app-import-csv-column" data-field="ID">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Author', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[post_author]" class="pl8app-import-csv-column" data-field="Author">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Categories', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[categories]" class="pl8app-import-csv-column" data-field="Categories">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Addons', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[addons]" class="pl8app-import-csv-column" data-field="Options and Upgrades">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Creation Date', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[post_date]" class="pl8app-import-csv-column" data-field="Date Created">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Description', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[post_content]" class="pl8app-import-csv-column" data-field="Description">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Excerpt', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[post_excerpt]" class="pl8app-import-csv-column" data-field="Excerpt">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Image', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[featured_image]" class="pl8app-import-csv-column" data-field="Featured Image">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Notes', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[notes]" class="pl8app-import-csv-column" data-field="Notes">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Price(s)', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[price]" class="pl8app-import-csv-column" data-field="Price">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product SKU', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[sku]" class="pl8app-import-csv-column" data-field="SKU">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Slug', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[post_name]" class="pl8app-import-csv-column" data-field="Slug">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Status', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[post_status]" class="pl8app-import-csv-column" data-field="Status">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Title', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[post_title]" class="pl8app-import-csv-column" data-field="Name">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Download Files', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[files]" class="pl8app-import-csv-column" data-field="Files">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'File Download Limit', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[download_limit]" class="pl8app-import-csv-column" data-field="File Download Limit">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Sale Count', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[sales]" class="pl8app-import-csv-column" data-field="Sales">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Total Earnings', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[earnings]" class="pl8app-import-csv-column" data-field="Earnings">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Product Type', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[product_type]" class="pl8app-import-csv-column" data-field="Item Type">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Bundled Products', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[bundled_products]" class="pl8app-import-csv-column" data-field="Bundled Items">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <p class="submit">
                                        <button class="pl8app-import-proceed button-primary"><?php _e( 'Process Import', 'pl8app' ); ?></button>
                                    </p>
                                </div>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox pl8app-import-categories">
                        <h3><span><?php _e( 'Import Categories', 'pl8app' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Import a CSV file of Categories.', 'pl8app' ); ?></p>
                            <form id="pl8app-import-categories" class="pl8app-import-form pl8app-import-export-form" action="<?php echo esc_url( add_query_arg( 'pl8app_action', 'upload_import_file', admin_url() ) ); ?>" method="post" enctype="multipart/form-data">

                                <div class="pl8app-import-file-wrap">
                                    <?php wp_nonce_field( 'pl8app_ajax_import', 'pl8app_ajax_import' ); ?>
                                    <input type="hidden" name="pl8app-import-class" value="pl8app_Batch_Categories_Import"/>
                                    <p>
                                        <input name="pl8app-import-file" id="pl8app-categories-import-file" type="file" />
                                    </p>
                                    <span>
                                        <input type="submit" value="<?php _e( 'Import CSV', 'pl8app' ); ?>" class="button-secondary"/>
                                        <span class="spinner"></span>
                                    </span>
                                </div>

                                <div class="pl8app-import-options" id="pl8app-import-categories-options" style="display:none;">

                                    <p>
                                        <?php
                                        printf(
                                            __( 'Each column loaded from the CSV needs to be mapped to a Menu Item field. Select the column that should be mapped to each field below. Any columns not needed can be ignored.', 'pl8app' )
                                        );
                                        ?>
                                    </p>

                                    <table class="widefat pl8app_repeatable_table striped" width="100%" cellpadding="0" cellspacing="0">
                                        <thead>
                                        <tr>
                                            <th><strong><?php _e( 'Product Field', 'pl8app' ); ?></strong></th>
                                            <th><strong><?php _e( 'CSV Column', 'pl8app' ); ?></strong></th>
                                            <th><strong><?php _e( 'Data Preview', 'pl8app' ); ?></strong></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php _e( 'Taxonomy', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[term_taxonomy]" class="pl8app-import-csv-column" data-field="Taxonomy">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Slug', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[term_slug]" class="pl8app-import-csv-column" data-field="Slug">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Parent', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[term_parent]" class="pl8app-import-csv-column" data-field="Parent">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Name', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[term_name]" class="pl8app-import-csv-column" data-field="Name">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>

                                        </tbody>
                                    </table>
                                    <p class="submit">
                                        <button class="pl8app-import-proceed button-primary"><?php _e( 'Process Import', 'pl8app' ); ?></button>
                                    </p>
                                </div>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                    <div class="postbox pl8app-import-addons">
                        <h3><span><?php _e( 'Import Option and Upgrades', 'pl8app' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Import a CSV file of Option and Upgrades.', 'pl8app' ); ?></p>
                            <form id="pl8app-import-addons" class="pl8app-import-form pl8app-import-export-form" action="<?php echo esc_url( add_query_arg( 'pl8app_action', 'upload_import_file', admin_url() ) ); ?>" method="post" enctype="multipart/form-data">

                                <div class="pl8app-import-file-wrap">
                                    <?php wp_nonce_field( 'pl8app_ajax_import', 'pl8app_ajax_import' ); ?>
                                    <input type="hidden" name="pl8app-import-class" value="pl8app_Batch_Addons_Import"/>
                                    <p>
                                        <input name="pl8app-import-file" id="pl8app-addons-import-file" type="file" />
                                    </p>
                                    <span>
                                        <input type="submit" value="<?php _e( 'Import CSV', 'pl8app' ); ?>" class="button-secondary"/>
                                        <span class="spinner"></span>
                                    </span>
                                </div>

                                <div class="pl8app-import-options" id="pl8app-import-addons-options" style="display:none;">

                                    <p>
                                        <?php
                                        printf(
                                            __( 'Each column loaded from the CSV needs to be mapped to a Menu Item field. Select the column that should be mapped to each field below. Any columns not needed can be ignored.', 'pl8app' )
                                        );
                                        ?>
                                    </p>

                                    <table class="widefat pl8app_repeatable_table striped" width="100%" cellpadding="0" cellspacing="0">
                                        <thead>
                                        <tr>
                                            <th><strong><?php _e( 'Product Field', 'pl8app' ); ?></strong></th>
                                            <th><strong><?php _e( 'CSV Column', 'pl8app' ); ?></strong></th>
                                            <th><strong><?php _e( 'Data Preview', 'pl8app' ); ?></strong></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?php _e( 'Taxonomy', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[term_taxonomy]" class="pl8app-import-csv-column" data-field="Taxonomy">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Slug', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[term_slug]" class="pl8app-import-csv-column" data-field="Slug">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Parent', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[term_parent]" class="pl8app-import-csv-column" data-field="Parent">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?php _e( 'Name', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[term_name]" class="pl8app-import-csv-column" data-field="Name">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>

                                        <tr>
                                            <td><?php _e( 'Option and Upgrade Item Select Type', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[use_it_like]" class="pl8app-import-csv-column" data-field="Selection Type">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>

                                        <tr>
                                            <td><?php _e( 'Price', 'pl8app' ); ?></td>
                                            <td>
                                                <select name="pl8app-import-field[price]" class="pl8app-import-csv-column" data-field="Price">
                                                    <option value=""><?php _e( '- Ignore this field -', 'pl8app' ); ?></option>
                                                </select>
                                            </td>
                                            <td class="pl8app-import-preview-field"><?php _e( '- select field to preview data -', 'pl8app' ); ?></td>
                                        </tr>

                                        </tbody>
                                    </table>
                                    <p class="submit">
                                        <button class="pl8app-import-proceed button-primary"><?php _e( 'Process Import', 'pl8app' ); ?></button>
                                    </p>
                                </div>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->

                </div>
            </div>
        </div>
    </div>
    <?php

}
add_action('pl8app_menuitem_exim_tab_import','pl8app_menuitem_exim_tab_import');

/**
 * Retrieves estimated monthly earnings and sales
 *
 * @since 1.0
 *
 * @param bool  $include_taxes If the estimated earnings should include taxes
 * @return array
 */
function pl8app_estimated_monthly_stats( $include_taxes = true ) {

	$estimated = get_transient( 'pl8app_estimated_monthly_stats' . $include_taxes );

	if ( false === $estimated ) {

		$estimated = array(
			'earnings' => 0,
			'sales'    => 0
		);

		$stats = new pl8app_Payment_Stats;

		$to_date_earnings = $stats->get_earnings( 0, 'this_month', null, $include_taxes );
		$to_date_sales    = $stats->get_sales( 0, 'this_month' );

		$current_day      = date( 'd', current_time( 'timestamp' ) );
		$current_month    = date( 'n', current_time( 'timestamp' ) );
		$current_year     = date( 'Y', current_time( 'timestamp' ) );
		$days_in_month    = cal_days_in_month( CAL_GREGORIAN, $current_month, $current_year );

		$estimated['earnings'] = ( $to_date_earnings / $current_day ) * $days_in_month;
		$estimated['sales']    = ( $to_date_sales / $current_day ) * $days_in_month;

		// Cache for one day
		set_transient( 'pl8app_estimated_monthly_stats' . $include_taxes, $estimated, 86400 );
	}

	return maybe_unserialize( $estimated );
}
