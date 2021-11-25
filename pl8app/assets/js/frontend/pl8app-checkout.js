window.pl8app_Checkout = (function($) {
	'use strict';

	var $body,
		$form,
		$pl8app_cart_amount,
		before_discount,
		$checkout_form_wrap;

	function init() {
		$body = $(document.body);
		$form = $("#pl8app_purchase_form");
		$pl8app_cart_amount = $('.pl8app_cart_amount');
		before_discount = $pl8app_cart_amount.text();
		$checkout_form_wrap = $('#pl8app_checkout_form_wrap');

		$body.on('pl8app_gateway_loaded', function( e ) {
			pl8app_format_card_number( $form );
		});

		$body.on('keyup change', '.pl8app-do-validate .card-number', function() {
			pl8app_validate_card( $(this) );
		});

		$body.on('blur change', '.card-name', function() {
			var name_field = $(this);

			name_field.validateCreditCard(function(result) {
				if(result.card_type != null) {
					name_field.removeClass('valid').addClass('error');
					$('#pl8app-purchase-button').attr('disabled', 'disabled');
				} else {
					name_field.removeClass('error').addClass('valid');
					$('#pl8app-purchase-button').removeAttr('disabled');
				}
			});
		});

		// Make sure a gateway is selected
		$body.on('submit', '#pl8app_payment_mode', function() {
			var gateway = $('#pl8app-gateway option:selected').val();
			if( gateway == 0 ) {
				alert( pl8app_global_vars.no_gateway );
				return false;
			}
		});

		// Add a class to the currently selected gateway on click
		$body.on('click', '#pl8app_payment_mode_select input', function() {
			$('#pl8app_payment_mode_select label.pl8app-gateway-option-selected').removeClass( 'pl8app-gateway-option-selected' );
			$('#pl8app_payment_mode_select input:checked').parent().addClass( 'pl8app-gateway-option-selected' );
		});

		// Validate and apply a discount
		$checkout_form_wrap.on('click', '.pl8app-apply-discount', apply_discount);

		// Prevent the checkout form from submitting when hitting Enter in the discount field
		$checkout_form_wrap.on('keypress', '#pl8app-discount', function (event) {
			if (event.keyCode == '13') {
				return false;
			}
		});

		// Apply the discount when hitting Enter in the discount field instead
		$checkout_form_wrap.on('keyup', '#pl8app-discount', function (event) {
			if (event.keyCode == '13') {
				$checkout_form_wrap.find('.pl8app-apply-discount').trigger('click');
			}
		});

		// Remove a discount
		$body.on('click', '.pl8app_discount_remove', remove_discount);

		// When discount link is clicked, hide the link, then show the discount input and set focus.
		$body.on('click', '.pl8app_discount_link', function(e) {
			e.preventDefault();
			$('.pl8app_discount_link').parent().hide();
			$('#pl8app-discount-code-wrap').show().find('#pl8app-discount').focus();
		});

		// Hide / show discount fields for browsers without javascript enabled
		$body.find('#pl8app-discount-code-wrap').hide();
		$body.find('#pl8app_show_discount').show();

		// Update the checkout when item quantities are updated
		$body.on('change', '.pl8app-item-quantity', update_item_quantities);

		$body.on('click', '.pl8app-amazon-logout #Logout', function(e) {
			e.preventDefault();
			amazon.Login.logout();
			window.location = pl8app_amazon.checkoutUri;
		});

	}

	function pl8app_validate_card(field) {
		var card_field = field;
		card_field.validateCreditCard(function(result) {
			var $card_type = $('.card-type');

			if(result.card_type == null) {
				$card_type.removeClass().addClass('off card-type');
				card_field.removeClass('valid');
				card_field.addClass('error');
			} else {
				$card_type.removeClass('off');
				$card_type.addClass( result.card_type.name );
				if (result.length_valid && result.luhn_valid) {
					card_field.addClass('valid');
					card_field.removeClass('error');
				} else {
					card_field.removeClass('valid');
					card_field.addClass('error');
				}
			}
		});
	}

	function pl8app_format_card_number( form ) {
		var card_number = form.find('.card-number'),
			card_cvc = form.find('.card-cvc'),
			card_expiry = form.find('.card-expiry');

		if ( card_number.length && 'function' === typeof card_number.payment ) {
			card_number.payment('formatCardNumber');
			card_cvc.payment('formatCardCVC');
			card_expiry.payment('formatCardExpiry');
		}
	}

	function apply_discount(event) {

		event.preventDefault();

		var $this = $(this),
			discount_code = $('#pl8app-discount').val(),
			pl8app_discount_loader = $('#pl8app-discount-loader');

		if (discount_code == '' || discount_code == pl8app_global_vars.enter_discount ) {
			return false;
		}

		var postData = {
			action: 'pl8app_apply_discount',
			code: discount_code,
			form: $( '#pl8app_purchase_form' ).serialize()
		};

		$('#pl8app-discount-error-wrap').html('').hide();
		pl8app_discount_loader.show();

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: pl8app_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {
				if( discount_response ) {
					if (discount_response.msg == 'valid') {
						$('.pl8app_cart_discount').html(discount_response.html);
						$('.pl8app_cart_discount_row').show();

						$( '.pl8app_cart_amount' ).each( function() {
							// Format discounted amount for display.
							$( this ).text( discount_response.total );
							// Set data attribute to new (unformatted) discounted amount.'
							$( this ).data( 'total', discount_response.total_plain );
						} );

						$('#pl8app-discount', $checkout_form_wrap ).val('');

						recalculate_taxes();

						var inputs = $('#pl8app_cc_fields .pl8app-input, #pl8app_cc_fields .pl8app-select,#pl8app_cc_address .pl8app-input, #pl8app_cc_address .pl8app-select,#pl8app_payment_mode_select .pl8app-input, #pl8app_payment_mode_select .pl8app-select');

						if( '0.00' == discount_response.total_plain ) {

							$('#pl8app_cc_fields,#pl8app_cc_address,#pl8app_payment_mode_select').slideUp();
							inputs.removeAttr('required');
							$('input[name="pl8app-gateway"]').val( 'manual' );

						} else {

							if (!inputs.is('.card-address-2')) {
								inputs.attr('required','required');
							}
							$('#pl8app_cc_fields,#pl8app_cc_address').slideDown();

						}

						$body.trigger('pl8app_discount_applied', [ discount_response ]);

					} else {
						$('#pl8app-discount-error-wrap').html( '<span class="pl8app_error">' + discount_response.msg + '</span>' );
						$('#pl8app-discount-error-wrap').show();
						$body.trigger('pl8app_discount_invalid', [ discount_response ]);
					}
				} else {
					if ( window.console && window.console.log ) {
						console.log( discount_response );
					}
					$body.trigger('pl8app_discount_failed', [ discount_response ]);
				}
				pl8app_discount_loader.hide();
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	};

	function remove_discount(event) {

		var $this = $(this), postData = {
			action: 'pl8app_remove_discount',
			code: $this.data('code')
		};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: pl8app_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (discount_response) {

				var zero = '0' + pl8app_global_vars.decimal_separator + '00';

				$('.pl8app_cart_amount').each(function() {
					if( pl8app_global_vars.currency_sign + zero == $(this).text() || zero + pl8app_global_vars.currency_sign == $(this).text() ) {
						// We're removing a 100% discount code so we need to force the payment gateway to reload
						window.location.reload();
					}

					// Format discounted amount for display.
					$( this ).text( discount_response.total );
					// Set data attribute to new (unformatted) discounted amount.'
					$( this ).data( 'total', discount_response.total_plain );
				});

				$('.pl8app_cart_discount').html(discount_response.html);

				if( ! discount_response.discounts ) {

					$('.pl8app_cart_discount_row').hide();

				}

				recalculate_taxes();

				$('#pl8app_cc_fields,#pl8app_cc_address').slideDown();

				$body.trigger('pl8app_discount_removed', [ discount_response ]);

			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	}

	function update_item_quantities(event) {

		var $this = $(this),
			quantity = $this.val(),
			key = $this.data('key'),
			menuitem_id = $this.closest('.pl8app_cart_item').data('menuitem-id'),
			options = $this.parent().find('input[name="pl8app-cart-menuitem-' + key + '-options"]').val();

		var pl8app_cc_address = $('#pl8app_cc_address');
		var billing_country = pl8app_cc_address.find('#billing_country').val(),
			card_state      = pl8app_cc_address.find('#card_state').val();

		var postData = {
			action: 'pl8app_update_quantity',
			quantity: quantity,
			menuitem_id: menuitem_id,
			options: options,
			billing_country: billing_country,
			card_state: card_state,
		};

		$.ajax({
			type: "POST",
			data: postData,
			dataType: "json",
			url: pl8app_global_vars.ajaxurl,
			xhrFields: {
				withCredentials: true
			},
			success: function (response) {

				$('.pl8app_cart_subtotal_amount').each(function() {
					$(this).text(response.subtotal);
				});

				$('.pl8app_cart_tax_amount').each(function() {
					$(this).text(response.taxes);
				});

				$('.pl8app_cart_amount').each(function() {
					$(this).text(response.total);
					$body.trigger('pl8app_quantity_updated', [ response ]);
				});
			}
		}).fail(function (data) {
			if ( window.console && window.console.log ) {
				console.log( data );
			}
		});

		return false;
	}

	// Expose some functions or variables to window.pl8app_Checkout object
	return {
		'init': init,
		'recalculate_taxes': recalculate_taxes
	}

})(window.jQuery);

// init on document.ready
window.jQuery(document).ready(pl8app_Checkout.init);

var ajax_tax_count = 0;
function recalculate_taxes(state) {

	if( '1' != pl8app_global_vars.taxes_enabled )
		return; // Taxes not enabled

	var $pl8app_cc_address = jQuery('#pl8app_cc_address');

	if( ! state ) {
		state = $pl8app_cc_address.find('#card_state').val();
	}

	var postData = {
		action: 'pl8app_recalculate_taxes',
		billing_country: $pl8app_cc_address.find('#billing_country').val(),
		state: state,
		card_zip: $pl8app_cc_address.find('input[name=card_zip]').val()
	};

	var current_ajax_count = ++ajax_tax_count;
	jQuery.ajax({
		type: "POST",
		data: postData,
		dataType: "json",
		url: pl8app_global_vars.ajaxurl,
		xhrFields: {
			withCredentials: true
		},
		success: function (tax_response) {
			// Only update tax info if this response is the most recent ajax call.
			// Avoids bug with form autocomplete firing multiple ajax calls at the same time and not
			// being able to predict the call response order.
			if (current_ajax_count === ajax_tax_count) {
				jQuery('#pl8app_checkout_cart_form').replaceWith(tax_response.html);
				jQuery('.pl8app_cart_amount').html(tax_response.total);
				var tax_data = new Object();
				tax_data.postdata = postData;
				tax_data.response = tax_response;
				jQuery('body').trigger('pl8app_taxes_recalculated', [ tax_data ]);
			}
		}
	}).fail(function (data) {
		if ( window.console && window.console.log ) {
			if (current_ajax_count === ajax_tax_count) {
				jQuery('body').trigger('pl8app_taxes_recalculated', [ tax_data ]);
			}
		}
	});
}
