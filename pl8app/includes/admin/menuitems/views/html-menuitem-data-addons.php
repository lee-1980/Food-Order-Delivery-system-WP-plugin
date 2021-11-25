<?php
/**
 * Menu Item Addons data panel.
 *
 * @package pl8app/Admin
 */

defined( 'ABSPATH' ) || exit;

$addon_categories 	= pl8app_get_addons();
$addon_types		= pl8app_get_addon_types();
$post_id  			= get_the_ID();

?>

<div id="addons_menuitem_data" class="panel pl8app-metaboxes-wrapper pl8app_options_panel">
	<div class="pl8app-metabox-container">
		<div class="toolbar toolbar-top">
			<span class="pl8app-toolbar-title">
				<?php esc_html_e( 'Options and Upgrades', 'pl8app' ); ?>
			</span>
			<button type="button" class="button create-addon alignright">
        		<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Create New Options and Upgrades', 'pl8app' ); ?>
			</button>
		</div>

		<div class="pl8app-addons pl8app-metaboxes">
			<?php include 'html-menuitem-addon.php'; ?>
		</div>

		<div class="toolbar toolbar-bottom">
			<button type="button" data-item-id="<?php echo $post_id; ?>" class="button button-primary add-new-addon alignright">
        		<span class="dashicons dashicons-plus"></span>
				<?php esc_html_e( 'Add New', 'pl8app' ); ?>
			</button>
		</div>
	</div>
	<?php do_action( 'pl8app_menuitem_options_addons_data' ); ?>
</div>