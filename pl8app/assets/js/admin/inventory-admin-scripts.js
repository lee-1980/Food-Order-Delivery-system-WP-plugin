jQuery( function( $ ) {

  /**
  * Update Stock Status on Change
  */
  jQuery( document ).on( 'input', 'tr.type-menuitem .pl8app_stock_status', function(e) {
    
    e.preventDefault();
    
    var _self = jQuery( this );
    var selectedStatus = _self.val();
    var item_id = _self.data( 'item-id' );

    if ( selectedStatus == '' ) { selectedStatus = 0; }

      _self.parents( 'td' ).find( '.stock-status-loading' ).addClass( 'disabled' );

     $.ajax({
        url:     pl8appi_admin_params.ajax_url,
        data:    {
          item_id : item_id,
          status  : selectedStatus,
          action  : 'pl8app_inventory_update_stock_status',
        },
        type:    'GET',
        success: function( response ) {
          if ( response.success ) {
            _self.parents( 'td' ).find( '.stock-status-loading' ).removeClass( 'disabled' );
          }
        }
      });

  });


  // it is a copy of the inline edit function
  var wp_inline_edit_function = inlineEditPost.edit;
 
  // we overwrite the it with our own
  inlineEditPost.edit = function( post_id ) {
 
    // let's merge arguments of the original function
    wp_inline_edit_function.apply( this, arguments );
 
    // get the post ID from the argument
    var id = 0;
    if ( typeof( post_id ) == 'object' ) { // if it is object, get the ID number
      id = parseInt( this.getId( post_id ) );
    }
 
    //if post id exists
    if ( id > 0 ) {

      // add rows to variables
      var specific_post_edit_row = $( '#edit-' + id ),
          specific_post_row = $( '#post-' + id ),
          stock_count = $( '.column-stock_value', specific_post_row ).text(); //  remove $ sign
 
      // populate the inputs with column data
      $( ':input[name="qe_pl8app_item_stock"]', specific_post_edit_row ).val( stock_count );
    }
  }
});