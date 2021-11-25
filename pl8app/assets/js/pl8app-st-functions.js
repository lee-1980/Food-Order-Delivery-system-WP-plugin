jQuery(function($) {

  // Get Cookie
  function pl8app_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
    }
    return "";
  }

  $( document.body ).on('opened_service_options', function( event ) {

    //Remove the additional service date dropdown
    if ( pl8app_st_vars.enabled_sevice_type['delivery'] &&  pl8app_st_vars.enabled_sevice_type['delivery'] == 1
        && pl8app_st_vars.enabled_sevice_type['pickup'] && pl8app_st_vars.enabled_sevice_type['pickup'] == 1) {
      $('.delivery-settings-wrapper#nav-pickup .delivery-time-wrapper:eq(0)').remove();
    }

    //Selected service date
    var selectedDate = pl8app_getCookie( 'service_date' );
    var selected = selectedDate !== '' ? selectedDate : pl8app_st_vars.selectedDate;

    $('.pl8app_get_delivery_dates').val( $(".pl8app_get_delivery_dates option:first").val() );

    if( selectedDate !== '' && $('.pl8app_get_delivery_dates option[value="'+ selectedDate +'"]').length > 0 ) {
      $('.pl8app_get_delivery_dates').change();
      $('.pl8app_get_delivery_dates').val( selectedDate );
    }
  });

  $( 'body' ).on('change', '.pl8app_get_delivery_dates', function() {
    var selectedDate = $(this).val();
    var serviceType = $(this).parents('.delivery-settings-wrapper').attr('id');
    $('.pl8app_get_delivery_dates').val(selectedDate);
    $('.pl8app-hrs').html('<option>' + pl8app_scripts.loading +'...</option>');

    var selectedTime = pl8app_getCookie( 'service_time' );

    var data = {
      action        : 'pl8app_st_render_timings',
      selectedDate  : selectedDate,
    };

    $.ajax({
      type      : "POST",
      data      : data,
      dataType  : "json",
      url       : pl8app_st_vars.ajaxurl,
      success: function( response ) {

        if( response.success ) {

          var currentDate = pl8app_st_vars.current_date;
          var pickupHrs = response.data.pickupHrs;
          var deliveryHrs = response.data.deliveryHrs;

          var pickupHtml = $('<select>');
          var deliveryHtml = $('<select>');

          if( pickupHrs !== null ) {
            for ( i = 0; i < pickupHrs.length; i++ ) {
              pickupHtml.append( $('<option></option>').val(pickupHrs[i]).html(pickupHrs[i]) );
            }
            $('.pl8app-pickup-time-wrap select#pl8app-pickup-hours').find('option').remove().end().append(pickupHtml.html());
            $('.pl8app-pickup-time-wrap select#pl8app-pickup-hours').val($(".pl8app-pickup-time-wrap select#pl8app-pickup-hours option:first").val());
            if( selectedTime !== undefined &&  $('select#pl8app-pickup-hours option[value="'+ selectedTime +'"]').length > 0 )
              $('.pl8app-pickup-time-wrap select#pl8app-pickup-hours').val(selectedTime);
          }

          if( deliveryHrs !== null  ) {
            for ( k = 0; k < deliveryHrs.length; k++ ) {
              deliveryHtml.append( $('<option></option>').val(deliveryHrs[k]).html(deliveryHrs[k]) );
            }
            $('.pl8app-delivery-time-wrap select#pl8app-delivery-hours').find('option').remove().end().append(deliveryHtml.html());
            $('.pl8app-delivery-time-wrap select#pl8app-delivery-hours').val($(".pl8app-delivery-time-wrap select#pl8app-delivery-hours option:first").val());
            if( selectedTime !== undefined &&  $('select#pl8app-delivery-hours option[value="'+ selectedTime +'"]').length > 0 )
              $('.pl8app-delivery-time-wrap select#pl8app-delivery-hours').val(selectedTime);
          }
        }
      }
    });
  });

    // Reorder and Re Add to Cart
    $('body').on('click', 'a.button.pl8app_reorder', function (e) {

        e.preventDefault();

        var self = $(this);
        var action = 'pl8app_show_delivery_options';
        var ServiceType = pl8app_getCookie('service_type');
        var ServiceTime = pl8app_getCookie('service_time');
        var payment_id = self.attr('data-payment-id');
        var text = self.html();
        self.text(pl8app_scripts.please_wait);

        var data = {
            action: action,
            security: pl8app_scripts.service_type_nonce
        }

        $('#pl8appModal').addClass('show-service-options');

        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: pla_scripts.ajaxurl,
            success: function (response) {

                self.html(text);
                $('#pl8appModal .modal-title').html(response.data.html_title);
                $('#pl8appModal .modal-body').html(response.data.html);
                $('#pl8appModal .pl8app-delivery-opt-update').attr('data-payment-id', payment_id);
                MicroModal.show('pl8appModal');

                if ($('.pl8app-tabs-wrapper').length) {

                    if (ServiceTime !== '') {
                        $('.pl8app-delivery-wrap').find('select#pl8app-' + ServiceType + '-hours').val(ServiceTime);
                        $('.pl8app-delivery-wrap').find('a#nav-' + ServiceType + '-tab').trigger('click');
                    } else {
                        $('.pl8app-delivery-wrap').find('a#nav-delivery-tab').trigger('click');
                    }

                }

                // Trigger event so themes can refresh other areas.
                $(document.body).trigger('opened_service_options', [response.data]);
            }
        });

    });

});
