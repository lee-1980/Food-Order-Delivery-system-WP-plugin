jQuery(document).ready(function ($) {
  
  //Add fee
  function pl8app_add_fee( tip, type ) {
    
    var data = {
      action: 'pl8app_add_tips',
      tip: tip,
      type: type
    };
    
    $.ajax({
      type: "POST",
      data: data,
      dataType: "json",
      url: tips_script.ajaxurl,
      success: function (tips_response) {
        
        $('#pl8app_cart_fee_tip').remove();
        
        if ( tips_response.response == 'success' ) {
          
          if ( $('.pl8app_cart_fee').length > 0 ) {
            $(tips_response.html).insertAfter(".pl8app_cart_fee").last();
          }
          else {
            $('.pl8app_cart_fee').remove();
            $('.pl8app-cart tbody').append(tips_response.html);
          }

          $('.pl8app_cart_amount').each( function() {
            // Format tip amount for display.
            $( this ).text( tips_response.total );
            // Set data attribute to new (unformatted) tip amount.
            $( this ).data( 'total', tips_response.total_plan );
          } );

          var html = $('<li class="remove_tip"><a href="#" class="pl8app-input pl8app-remove-tip">' + tips_script.remove_tip_label + '</a></li>');

          $('.pl8app-tips').find('.tip_percentage').text( ' ' + tips_response.tip_value );
          $('.tip-wrapper').find('li.remove_tip').remove();
          
          html.insertBefore( ".tip-wrapper li:first-child" );

        }
      }
    }).fail(function (response) {
      if ( window.console && window.console.log ) {
        console.log( response );
      }
    }).done(function (response) {});
  }

  //Add tips
  $( document ).on('click', '.tips', function (e) {
    e.preventDefault();
    $('.tip-wrapper a').removeClass('active');
    $(this).addClass('active');
    $('#manual_tip_value').val('');
    var Tip = $(this).data('tip');

    if( Tip == 0 ) {
      pl8app_remove_tips();
    }
    else {
      pl8app_add_fee( Tip, $(this).data('type') );
    }
  });

  //Manual tip add
  $('body').on('click', '.pl8app_tips_custom_amount', function (e) {
    
    $('.tips').removeClass('active');

    var Tip = $('#manual_tip_value').val();
    if ( Tip == 0 ) {
      pl8app_remove_tips();
    }
    else {
      pl8app_add_fee( Tip, 'manual_tip_value' );
    }
    
  });

  //Remove tips through ajax
  function pl8app_remove_tips() {
    var data = {
      action: 'pl8app_remove_tips',
    };
    
    $.ajax({
      type: "POST",
      data: data,
      dataType: "json",
      url: tips_script.ajaxurl,
      success: function (tips_response) {
        if ( tips_response.response == 'success' ) {
          if ( $('.pl8app_cart_fee').length > 0 ) {
            $('#pl8app_cart_fee_tip').remove();
          }
          else {
            $('.pl8app_cart_fee').remove();
          }

          $('.pl8app_cart_amount').each( function() {
            // Format tip amount for display.
            $( this ).text( tips_response.total );
          } );

          $('.pl8app-tips').find('.tip_percentage').text( ' 0 ' + tips_script.tip_type_symbol );
          //$('.tip-wrapper').find('li.remove_tip').remove();
          $('.tip-wrapper').find('#manual_tip_value').val('');
          $('#manual_tip_value').val('');
        }
      }
    }).fail(function (response) {
      if ( window.console && window.console.log ) {
        console.log( response );
      }
    }).done(function (response) {});
  }


  //Remove tips on button click
  $('body').on('click', '.pl8app-remove-tip', function(e) {
    e.preventDefault();
    $('.tip-wrapper a').removeClass('active');
    $(this).addClass('active');
    pl8app_remove_tips();
  });

});