/**
 * Developer Notice: The contents of this JavaScript file are not to be relied on in any future versions of pl8app
 */
jQuery(document).ready(function ($) {

	// Adjust location of setting labels for settings in the new containers created below (back compat)
	$( document.body ).find( '.pl8app-custom-price-option-sections .pl8app-legacy-setting-label' ).each(function() {
		$(this).prependTo($(this).nextAll('span:not(:has(>.pl8app-legacy-setting-label))').first());
	});

	// Build HTML containers for existing price option settings (back compat)
	$( document.body ).find( '.pl8app-custom-price-option-sections' ).each(function() {
		$(this).find('[class*="purchase_limit"]').wrapAll( '<div class="pl8app-purchase-limit-price-option-settings-legacy pl8app-custom-price-option-section"></div>' );
		$(this).find('[class*="shipping"]').wrapAll( '<div class="pl8app-simple-shipping-price-option-settings-legacy pl8app-custom-price-option-section" style="display: none;"></div>' );
		$(this).find('[class*="sl-"]').wrapAll( '<div class="pl8app-sl-price-option-settings-legacy pl8app-custom-price-option-section"></div>' );
		$(this).find('[class*="pl8app-recurring-"]').wrapAll( '<div class="pl8app-recurring-price-option-settings-legacy pl8app-custom-price-option-section"></div>' );
	});

	// only display Simple Shipping/Software Licensing sections if enabled (back compat)
	$( document.body ).find( '#pl8app_enable_shipping', '#pl8app_license_enabled' ).each(function() {
		var variable_pricing = $('#pl8app_variable_pricing').is( ':checked' );
		var ss_checked       = $( '#pl8app_enable_shipping' ).is( ':checked' );
		var ss_section       = $( '.pl8app-simple-shipping-price-option-settings-legacy' );
		var sl_checked       = $( '#pl8app_license_enabled' ).is( ':checked' );
		var sl_section       = $( '.pl8app-sl-price-option-settings-legacy' );
		if ( variable_pricing ) {
			if ( ss_checked ) {
				ss_section.show();
			} else {
				ss_section.hide();
			}
			if ( sl_checked ) {
				sl_section.show();
			} else {
				sl_section.hide();
			}
		}
	});
	$( '#pl8app_enable_shipping' ).on( 'change', function() {
		var enabled  = $(this).is( ':checked' );
		var section  = $( '.pl8app-simple-shipping-price-option-settings-legacy' );
		if ( enabled ) {
			section.show();
		} else {
			section.hide();
		}
	});
	$( '#pl8app_license_enabled' ).on( 'change', function() {
		var enabled  = $(this).is( ':checked' );
		var section  = $( '.pl8app-sl-price-option-settings-legacy' );
		if ( enabled ) {
			section.show();
		} else {
			section.hide();
		}
	});

	// Create section titles for newly created HTML containers (back compat)
	$( document.body ).find( '.pl8app-purchase-limit-price-option-settings-legacy' ).each(function() {
		$(this).prepend( '<span class="pl8app-custom-price-option-section-title">' + pl8app_backcompat_vars.purchase_limit_settings + '</span>' );
	});
	$( document.body ).find( '.pl8app-simple-shipping-price-option-settings-legacy' ).each(function() {
		$(this).prepend( '<span class="pl8app-custom-price-option-section-title">' + pl8app_backcompat_vars.simple_shipping_settings + '</span>' );
	});
	$( document.body ).find( '.pl8app-sl-price-option-settings-legacy' ).each(function() {
		$(this).prepend( '<span class="pl8app-custom-price-option-section-title">' + pl8app_backcompat_vars.software_licensing_settings + '</span>' );
	});
	$( document.body ).find( '.pl8app-recurring-price-option-settings-legacy' ).each(function() {
		$(this).prepend( '<span class="pl8app-custom-price-option-section-title">' + pl8app_backcompat_vars.recurring_payments_settings + '</span>' );
	});

});