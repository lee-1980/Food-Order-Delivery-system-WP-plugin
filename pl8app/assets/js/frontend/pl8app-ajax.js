var pl8app_scripts;

jQuery(document).ready(function($) {

  // Hide un-necessary elements. These are things that are required in case JS breaks or isn't present
  $('.pl8app-no-js').hide();

  //Hide delivery error when switch tabs
  $('body').on('click', '.pl8app-delivery-options li.nav-item', function(e) {
    e.preventDefault();
    $(this).parents('.pl8app-delivery-wrap').find('.pl8app-order-time-error').addClass('hide');
  });

  // Show the login form on the checkout page
  $('#pl8app_checkout_form_wrap').on('click', '.pl8app_checkout_register_login', function() {

    var $this = $(this),
    payment_form = $('#pl8app_payment_mode_select_wrap,#pl8app_purchase_form_wrap');
    ajax_loader = '<span class="pl8app-loading-ajax pl8app-loading"></span>';
    data = {
      action: $this.data('action')
    };
    payment_form.hide();

    // Show the ajax loader
    $this.html($this.html() + ajax_loader);

    $.post(pl8app_scripts.ajaxurl, data, function(checkout_response) {

      $('#pl8app_checkout_login_register').html(pl8app_scripts.loading);
      $('#pl8app_checkout_login_register').html(checkout_response);

      // Hide the ajax loader
      $('.pl8app-cart-ajax').hide();

      //Show the payment form
      if( data.action == 'pl8app_checkout_register' )
        payment_form.show();
    });
    return false;
  });

  // Process the login form via ajax
  $(document).on('click', '#pl8app_purchase_form #pl8app_login_fields input[type=submit]', function(e) {

    e.preventDefault();

    var complete_purchase_val = $(this).val();

    $(this).val(pl8app_global_vars.purchase_loading);

    $(this).after('<span class="pl8app-loading-ajax pl8app-loading"></span>');

    var data = {
      action: 'pl8app_process_checkout_login',
      pl8app_ajax: 1,
      pl8app_user_login: $('#pl8app_login_fields #pl8app_user_login').val(),
      pl8app_user_pass: $('#pl8app_login_fields #pl8app_user_pass').val()
    };

    $.post(pl8app_global_vars.ajaxurl, data, function(data) {

      if ( $.trim(data) == 'success' ) {
        $('.pl8app_errors').remove();
        window.location = pl8app_scripts.checkout_page;
      }
      else {
        $('#pl8app_login_fields input[type=submit]').val(complete_purchase_val);
        $('.pl8app-loading-ajax').remove();
        $('.pl8app_errors').remove();
        $('#pl8app-user-login-submit').before(data);
      }
    });

  });

  // Load the fields for the $this payment method
  $('select#pl8app-gateway, input.pl8app-gateway').change(function(e) {

    var payment_mode = $('#pl8app-gateway option:selected, input.pl8app-gateway:checked').val();

    if (payment_mode == '0') {
      return false;
    }

    pl8app_load_gateway(payment_mode);

    return false;
  });

  // Auto load first payment gateway
  if (pl8app_scripts.is_checkout == '1') {

    var chosen_gateway = false;
    var ajax_needed = false;

    if ($('select#pl8app-gateway, input.pl8app-gateway').length) {
      chosen_gateway = $("meta[name='pl8app-chosen-gateway']").attr('content');
      ajax_needed = true;
    }

    if (!chosen_gateway) {
      chosen_gateway = pl8app_scripts.default_gateway;
    }

    if ( ajax_needed ) {

      // If we need to ajax in a gateway form, send the requests for the POST.
      setTimeout(function() {
          pl8app_load_gateway(chosen_gateway);
      }, 200);

    }
    else {

      // The form is already on page, just trigger that the gateway is loaded so further action can be taken.
      $('body').trigger('pl8app_gateway_loaded', [chosen_gateway]);

    }
  }

  // Process checkout
  $(document).on('click', '#pl8app_purchase_form #pl8app_purchase_submit [type=submit]', function(e) {

      var pl8appPurchaseform = document.getElementById('pl8app_purchase_form');

      if (typeof pl8appPurchaseform.checkValidity === "function" && false === pl8appPurchaseform.checkValidity()) {
          return;
      }

      e.preventDefault();

      var complete_purchase_val = $(this).val();

      $(this).val(pl8app_global_vars.purchase_loading);

      $(this).prop('disabled', true);

      $(this).after('<span class="pl8app-loading-ajax pl8app-loading"></span>');

      $.post(pl8app_global_vars.ajaxurl, $('#pl8app_purchase_form').serialize() + '&action=pl8app_process_checkout&pl8app_ajax=true', function(data) {

        if ( $.trim(data) == 'success' ) {
          $('.pl8app_errors').remove();
          $('.pl8app-error').hide();
          $(pl8appPurchaseform).submit();
        }
        else {
          $('#pl8app-purchase-button').val(complete_purchase_val);
          $('.pl8app-loading-ajax').remove();
          $('.pl8app_errors').remove();
          $('.pl8app-error').hide();
          $(pl8app_global_vars.checkout_error_anchor).before(data);
          $('#pl8app-purchase-button').prop('disabled', false);

          $(document.body).trigger('pl8app_checkout_error', [data]);
        }
      });

  });

  // Update state field
  $( document.body ).on( 'change', '#pl8app_cc_address input.card_state, #pl8app_cc_address select, #pl8app_address_country', update_state_field );

  function update_state_field() {

    var $this = $(this);
    var $form;
    var is_checkout = typeof pl8app_global_vars !== 'undefined';
    var field_name = 'card_state';

    if ($(this).attr('id') == 'pl8app_address_country') {
      field_name = 'pl8app_address_state';
    }

    if ('card_state' != $this.attr('id')) {

      // If the country field has changed, we need to update the state/province field
      var postData = {
        action: 'pl8app_get_states',
        country: $this.val(),
        field_name: field_name,
      };

      $.ajax({
        type  : "POST",
        data  : postData,
        url   : pl8app_scripts.ajaxurl,
        xhrFields: {
          withCredentials: true
        },

        success: function(response) {
          if (is_checkout) {
            $form = $("#pl8app_purchase_form");
          }
          else {
            $form = $this.closest("form");
          }

          var state_inputs = 'input[name="card_state"], select[name="card_state"], input[name="pl8app_address_state"], select[name="pl8app_address_state"]';

          if ('nostates' == $.trim(response)) {
            var text_field = '<input type="text" name="card_state" class="card-state pl8app-input required" value=""/>';
            $form.find(state_inputs).replaceWith(text_field);
          }
          else {
            $form.find(state_inputs).replaceWith(response);
          }

          if (is_checkout) {
            $(document.body).trigger('pl8app_cart_billing_address_updated', [response]);
          }

        }
      }).fail(function(data) {
        if (window.console && window.console.log) {
          console.log(data);
        }
      }).done(function(data) {
        if (is_checkout) {
          recalculate_taxes();
        }
      });
    }
    else {
      if (is_checkout) {
        recalculate_taxes();
      }
    }

    return false;
  }

  // If is_checkout, recalculate sales tax on postalCode change.
  $( document.body ).on( 'change', '#pl8app_cc_address input[name=card_zip]', function() {
    if ( typeof pl8app_global_vars !== 'undefined' ) {
      recalculate_taxes();
    }
  });

  $("#pl8appModal").on('hide.bs.modal', function(){
    $('.modal-backdrop.in').remove();
  });

});


// Load a payment gateway
function pl8app_load_gateway(payment_mode) {

  // Show the ajax loader
  jQuery('.pl8app-cart-ajax').show();
  jQuery('#pl8app_purchase_form_wrap').html('<span class="pl8app-loading-ajax pl8app-loading"></span>');

  var url = pl8app_scripts.ajaxurl;

  if (url.indexOf('?') > 0) {
    url = url + '&';
  } else {
    url = url + '?';
  }

  url = url + 'payment-mode=' + payment_mode;

  jQuery.post(url, {
    action              : 'pl8app_load_gateway',
    pl8app_payment_mode : payment_mode
  },
  function(response) {
    jQuery('#pl8app_purchase_form_wrap').html(response);
    jQuery('.pl8app-no-js').hide();
    jQuery('body').trigger('pl8app_gateway_loaded', [payment_mode]);
  });
}


