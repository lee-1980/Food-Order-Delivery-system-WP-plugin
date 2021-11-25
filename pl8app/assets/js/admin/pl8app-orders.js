
var auto_print_processing = false;

if (pla_orders_params.order_auto_print_enable == 1) {
    var WinPrint = window.open('', 'pl8app_auto_print', 'width=1,height=1');
}
jQuery( function( $ ) {

  /**
   * PL8OrdersTable class.
   */
  var PL8OrdersTable = function() {
    $( document.body )
      .on( 'click', '.order-preview:not(.disabled)', this.onPreview );
  };

    /**
     *  Async print process
     */
    var async_print = (payment_id) => {
      return new Promise((resolve, reject) => {
          jQuery('#print-display-area-' + payment_id).load( ajax_url + '?action=pl8app_print_payment_data&payment_id=' + payment_id,function(){

            try{
                var printContent = document.getElementById('print-display-area-' + payment_id);

                WinPrint.document.write(printContent.innerHTML);
                WinPrint.document.close();

                setTimeout(function () {
                    WinPrint.focus();
                    WinPrint.print();
                    resolve();
                }, 200);
            }
            catch (e) {
                reject();
                console.log(e.message);
            }

          });
      });
    };

  /**
  * Order status change by dropdown
  */
  jQuery( document ).on( 'change', '#pl8app-payments-filter .pla_order_status', function(e) {
    e.preventDefault();
    var _self           = jQuery( this );
    var selectedStatus  = _self.val();
    var currentStatus   = _self.attr( 'data-current-status' );
    var payment_id      = _self.attr( 'data-payment-id' );

    if ( selectedStatus !== '' ) {

      _self.removeClass( 'pla_current_status_' + currentStatus );
      _self.addClass( 'pla_current_status_' + selectedStatus );
      _self.attr( 'data-current-status', selectedStatus );
      _self.parent( 'td' ).find( '.order-status-loading' ).addClass( 'disabled' );

     $.ajax({
        url:     pla_orders_params.ajax_url,
        data:    {
          payment_id : payment_id,
          status  : selectedStatus,
          action  : 'pl8app_update_order_status',
        },
        type:    'GET',
        success: async function( response ) {
          if ( response ) {
            // location.reload();
              try{
                  if (pla_orders_params.order_auto_print_enable && pla_orders_params.auto_print_per_order
                      && pla_orders_params.auto_print_per_order[selectedStatus].status == 1
                      && pla_orders_params.auto_print_per_order[selectedStatus].copies) {

                      auto_print_processing = true;
                      for (var i = 0; i < pla_orders_params.auto_print_per_order[selectedStatus].copies; i++) {
                          await async_print(payment_id);
                      }

                      auto_print_processing = false;

                  }
                  _self.parent( 'td' ).find( '.order-status-loading' ).removeClass( 'disabled' );
              }
              catch (e) {
                  auto_print_processing = false;
                  _self.parent( 'td' ).find( '.order-status-loading' ).removeClass( 'disabled' );
              }

          }
        }
      });
    }
  });


  /**
  * Preview an order
  */
  PL8OrdersTable.prototype.onPreview = function() {
    var $previewButton    = $( this ),
      $order_id         = $previewButton.data( 'order-id' ); 

    if ( $previewButton.data( 'order-data' ) ) {
      $( this ).RPBackboneModal({
        template: 'pl8app-modal-view-order',
        variable : $previewButton.data( 'order-data' )
      });
    } else {
      $previewButton.addClass( 'disabled' );

      $.ajax({
        url:     pla_orders_params.ajax_url,
        data:    {
          order_id: $order_id,
          action  : 'pl8app_get_order_details',
          security: pla_orders_params.preview_nonce
        },
        type:    'GET',
        success: function( response ) {
          $( '.order-preview' ).removeClass( 'disabled' );

          if ( response.success ) {
            $previewButton.data( 'order-data', response.data );

            $( this ).RPBackboneModal({
              template: 'pl8app-modal-view-order',
              variable : response.data
            });
          }
        }
      });
    }
    return false;

  };

  /**
   * Init PL8OrdersTable.
   */
  new PL8OrdersTable();

    window.setTimeout(function () {
        if(!auto_print_processing){
            window.location.reload();
        }
    }, 60000);
});