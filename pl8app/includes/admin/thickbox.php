<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds an "Insert Download" button above the TinyMCE Editor on add/edit screens.
 *
 * @since 1.0
 * @return string "Insert Download" Button
 */
function pl8app_media_button() {
	global $pagenow, $typenow;
	$output = '';

	/** Only run in post/page creation and edit screens */
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && $typenow != 'menuitem' ) {

		$img = '<span class="wp-media-buttons-icon dashicons dashicons-menuitem" id="pl8app-media-button"></span>';
		$output = '<a href="#TB_inline?width=640&inlineId=choose-menuitem" class="thickbox button pl8app-thickbox" style="padding-left: .4em;">' . $img . sprintf( __( 'Insert %s', 'pl8app' ), strtolower( pl8app_get_label_singular() ) ) . '</a>';

	}

	echo $output;
}
add_action( 'media_buttons', 'pl8app_media_button', 11 );

/**
 * Admin Footer For Thickbox
 *
 * Prints the footer code needed for the Insert Download
 * TinyMCE button.
 *
 * @since 1.0
 * @global $pagenow
 * @global $typenow
 * @return void
 */
function pl8app_admin_footer_for_thickbox() {
	global $pagenow, $typenow;

    ?>

    <?php
	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && $typenow != 'menuitem' ) { ?>
		<script type="text/javascript">
			function insertDownload() {
				var id = jQuery('#products').val(),
					direct = jQuery('#select-pl8app-direct').val(),
					style = jQuery('#select-pl8app-style').val(),
					color = jQuery('#select-pl8app-color').is(':visible') ? jQuery('#select-pl8app-color').val() : '',
					text = jQuery('#pl8app-text').val() || '<?php _e( "Purchase", "pl8app" ); ?>';

				// Return early if no menuitem is selected
				if ('' === id) {
					alert('<?php _e( "You must choose a menuitem", "pl8app" ); ?>');
					return;
				}

				if( '2' == direct ) {
					direct = ' direct="true"';
				} else {
					direct = '';
				}

				// Send the shortcode to the editor
				window.send_to_editor('[purchase_link id="' + id + '" style="' + style + '" color="' + color + '" text="' + text + '"' + direct +']');
			}
			jQuery(document).ready(function ($) {
				$('#select-pl8app-style').change(function () {
					if ($(this).val() === 'button') {
						$('#pl8app-color-choice').slideDown();
					} else {
						$('#pl8app-color-choice').slideUp();
					}
				});
			});
		</script>

		<div id="choose-menuitem" style="display: none;">
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<p><?php echo sprintf( __( 'Use the form below to insert the shortcode for purchasing a %s', 'pl8app' ), pl8app_get_label_singular() ); ?></p>
				<div>
					<?php echo PL8PRESS()->html->product_dropdown( array( 'chosen' => true )); ?>
				</div>
				<?php if( pl8app_shop_supports_buy_now() ) : ?>
					<div>
						<select id="select-pl8app-direct" style="clear: both; display: block; margin-bottom: 1em; margin-top: 1em;">
							<option value="0"><?php _e( 'Choose the button behavior', 'pl8app' ); ?></option>
							<option value="1"><?php _e( 'Add to Cart', 'pl8app' ); ?></option>
							<option value="2"><?php _e( 'Direct Purchase Link', 'pl8app' ); ?></option>
						</select>
					</div>
				<?php endif; ?>
				<div>
					<select id="select-pl8app-style" style="clear: both; display: block; margin-bottom: 1em; margin-top: 1em;">
						<option value=""><?php _e( 'Choose a style', 'pl8app' ); ?></option>
						<?php
							$styles = array( 'button', 'text link' );
							foreach ( $styles as $style ) {
								echo '<option value="' . $style . '">' . $style . '</option>';
							}
						?>
					</select>
				</div>
				<?php
				$colors = pl8app_get_button_colors();
				if( $colors ) { ?>
				<div id="pl8app-color-choice" style="display: none;">
					<select id="select-pl8app-color" style="clear: both; display: block; margin-bottom: 1em;">
						<option value=""><?php _e('Choose a button color','pl8app' ); ?></option>
						<?php
							foreach ( $colors as $key => $color ) {
								echo '<option value="' . str_replace( ' ', '_', $key ) . '">' . $color['label'] . '</option>';
							}
						?>
					</select>
				</div>
				<?php } ?>
				<div>
					<input type="text" class="regular-text" id="pl8app-text" value="" placeholder="<?php _e( 'Link text . . .', 'pl8app' ); ?>"/>
				</div>
				<p class="submit">
					<input type="button" id="pl8app-insert-menuitem" class="button-primary" value="<?php echo sprintf( __( 'Insert %s', 'pl8app' ), pl8app_get_label_singular() ); ?>" onclick="insertDownload();" />
					<a id="pl8app-cancel-menuitem-insert" class="button-secondary" onclick="tb_remove();"><?php _e( 'Cancel', 'pl8app' ); ?></a>
				</p>
			</div>
		</div>
	<?php
	}

}
add_action( 'admin_footer', 'pl8app_admin_footer_for_thickbox' );
