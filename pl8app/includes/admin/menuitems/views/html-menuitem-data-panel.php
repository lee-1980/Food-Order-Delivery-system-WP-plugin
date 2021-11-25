<?php
/**
 * Menu Item data meta box.
 *
 * @package pl8app/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="panel-wrap menuitem_data">

	<?php
		self::output_tabs();
		do_action( 'pl8app_menuitem_data_panels' );
	?>
	<div class="clear"></div>
</div>
