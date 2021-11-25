<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders the Discount Pages Admin Page
 *
 * @since 1.4
 * @author Magnigenie
 * @return void
*/
function pl8app_discounts_page() {
	if ( isset( $_GET['pl8app-action'] ) && $_GET['pl8app-action'] == 'edit_discount' ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/discounts/edit-discount.php';
	} elseif ( isset( $_GET['pl8app-action'] ) && $_GET['pl8app-action'] == 'add_discount' ) {
		require_once PL8_PLUGIN_DIR . 'includes/admin/discounts/add-discount.php';
	} else {
		require_once PL8_PLUGIN_DIR . 'includes/admin/discounts/class-discount-codes-table.php';
		$discount_codes_table = new pl8app_Discount_Codes_Table();
		$discount_codes_table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php _e( 'Discount Codes', 'pl8app' ); ?><a href="<?php echo esc_url( add_query_arg( array( 'pl8app-action' => 'add_discount' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'pl8app' ); ?></a></h1>
		<?php do_action( 'pl8app_discounts_page_top' ); ?>
		<form id="pl8app-discounts-filter" method="get" action="<?php echo admin_url( 'admin.php?page=pl8app-discounts' ); ?>">
			<?php $discount_codes_table->search_box( __( 'Search', 'restr-press' ), 'pl8app-discounts' ); ?>
			<input type="hidden" name="page" value="pl8app-discounts" />
			<?php $discount_codes_table->views() ?>
			<?php $discount_codes_table->display() ?>
		</form>
		<?php do_action( 'pl8app_discounts_page_bottom' ); ?>
	</div>
<?php
	}
}
