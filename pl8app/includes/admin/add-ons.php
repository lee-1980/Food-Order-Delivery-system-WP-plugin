<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @since 1.0
 * @return void
 */
function pl8app_extensions_page() {
	ob_start(); ?>
	<div class="wrap" id="pl8app-add-ons">
		<hr class="wp-header-end">
		<!-- pl8app Addons Starts Here-->
		<div class="pl8app-about-body">
			<h2>
				<?php _e( 'Extending the Possibilities', 'pl8app' ); ?>
			</h2>
			<div class="about-text"><?php _e('pl8app has some basic features for menu ordering system. If you want more exciting premium features then we have some addons to boost your pl8app powered ordering system.', 'pl8app');?></div>
		</div>
		<!-- pl8app Addons Ends Here -->
		<div class="pl8app-add-ons-view-wrapper">
			<?php echo pl8app_add_ons_get_feed(); ?>
		</div>

	</div>
	<?php
	echo ob_get_clean();
}

/**
 * Add-ons Get Feed
 *
 * Gets the add-ons page feed.
 *
 * @since 1.0
 * @return void
 */
function pl8app_add_ons_get_feed() {

	$items = get_transient( 'pl8app_add_ons_feed' );

	if( ! $items ) {
		$items = pl8app_fetch_items();
	}

	$data = '';

	if( is_array($items) && !empty($items) ) {
		$data = '<div class="pl8app-addons-all">';

		foreach( $items as $key => $item ) {

			$class = 'inactive';

			$class_name = trim($item->class_name);

			if( class_exists($class_name) ) {
				$class = 'installed';
			}

			$updated_class = '';
			$deactive_class = 'hide';

			if( get_option($item->license_string.'_status') == 'valid' ) {
				$updated_class = 'pl8app-updated';
				$deactive_class = 'show';
			}

			$item_link = isset($item->link) ? $item->link : '';
			ob_start();
			?>
			<div class="row pl8app-addon-item <?php echo $class; ?>">
				<!-- Addons Image Starts Here -->
				<div class="pl8app-col-xs-12 pl8app-col-sm-6 pl8app-col-md-5 pl8app-col-lg-5 pl8app-addon-img-wrap">
					<img alt="<?php echo $item->title; ?>" src="<?php echo $item->product_image; ?>">
				</div>
				<!-- Addons Image Ends Here -->

				<!-- Addons Price and Details Starts Here -->
				<div class="pl8app-col-xs-12 pl8app-col-sm-6 pl8app-col-md-5 pl8app-col-lg-5 pl8app-addon-img-wrap">
					<div class="inside">
						<h3><?php echo $item->title; ?></h3>
						<small class="pl8app-addon-item-pricing"><?php echo __('from', 'pl8app'). ' $' . $item->price_range; ?></small>

						<!-- Addons price wrap starts here -->
						<div class="pl8app-btn-group pl8app-purchase-section">
							<span class="button-secondary">$<?php echo $item->price_range; ?></span>
							<a class="button button-medium button-primary " target="_blank" href="<?php echo $item_link . '?utm_source=plugin&utm_medium=addon_page&utm_campaign=promote_addon' ?>" ><?php esc_html_e( 'Details', 'pl8app')?></a>
						</div>
						<!-- Addons price wrap ends here -->

						<div class="pl8app-installed-wrap">

						<!-- Addons Installed Starts Here -->

						<!-- Addons Installed Ends Here -->

						<!-- Addon Details Starts Here -->
						<div class="pl8app-btn-group pl8app-addon-details-section pull-left">
							<a class="button button-medium button-primary " target="_blank" href="<?php echo $item_link . '?utm_source=plugin&utm_medium=addon_page&utm_campaign=promote_addon' ?>" ><?php echo __('Details', 'pl8app')?></a>
						</div>
						<!-- Addon Details Ends Here -->

						</div>

						<div class="pl8app-purchased-wrap">
							<span><?php echo $item->short_content; ?></span>

							<div class="pl8app-license-wrapper <?php echo $updated_class; ?>">
								<input type="hidden" class="pl8app_license_string" name="pl8app_license" value="<?php echo $item->license_string; ?>">
								<input type="text" data-license-key="" placeholder="<?php echo __('Enter your license key here'); ?>" data-item-name="<?php echo $item->title; ?>" data-item-id="<?php echo $item->id; ?>" class="pl8app-license-field pull-left" name="pl8app-license">
								<button data-action="pl8app_activate_addon_license" class="button button-medium button-primary pull-right pl8app-validate-license"><?php echo __('Activate License', 'pl8app'); ?></button>
								<div class="clear"></div>

							</div><!-- .pl8app-license-wrapper-->

							<!-- License Deactivate Starts Here -->
							<div class="clear"></div>
							<div class="pl8app-license-deactivate-wrapper <?php echo $deactive_class; ?>">
								<button data-action="pl8app_deactivate_addon_license" class="button  pull-left pl8app-deactivate-license"><?php echo __('Deactivate License', 'pl8app'); ?></button>
							</div>
							<!-- License Deactiave Ends Here -->

						</div>

					</div>
				</div>
				<!-- Addons Price and Details Ends Here -->
			</div>

			<?php
		}
	} else { ?>
		<div class="pl8app-addons-all">
			<span><?php esc_html_e( 'Something went wrong. Please try after sometime..', 'pl8app' ); ?>
			</span>
		</div>;
	<?php }
	echo ob_get_clean();
}

function pl8app_fetch_items() {

	$url = 'https://www.pl8app.com/wp-json/pl8app-server/';
	$version = '1.0';
	$remote_url = $url . 'v' . $version;

	$feed = wp_remote_get( esc_url_raw( $remote_url ), array( 'sslverify' => false ) );
	$items = array();

	if ( ! is_wp_error( $feed ) ) {
		if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$items = wp_remote_retrieve_body( $feed );
			$items = json_decode($items);
			set_transient( 'pl8app_add_ons_feed', $items, 3600 );
		}
	} else {
		$items = '<div class="error"><p>' . __( 'There was an error retrieving the extensions list from the server. Please try again later.', 'pl8app' ) . '</div>';
	}
	return $items;
}