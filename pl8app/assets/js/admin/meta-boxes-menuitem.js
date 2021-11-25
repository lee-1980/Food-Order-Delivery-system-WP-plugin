/* Menu Item metabox scripts */
jQuery( function( $ ) {

	//show the toast message
	function pl8app_toast(heading, message, type){
		$.toast({
		  heading : heading,
		  text    : message,
		  showHideTransition: 'slide',
		  icon    : type,
		  position: { top: '36px', right: '0px' },
		  stack   : false
		});
	}

	$('.pla_add_category').click(function(){
		$('.pl8app-add-category').toggle();
	});

	$('#_variable_pricing').change(function(){
		if( $(this).is(':checked') ){
			$('.pl8app-variable-prices').slideDown();
			$('.pl8app_price_field').slideUp();
		} else {
			$('.pl8app-variable-prices').slideUp();
			$('.pl8app_price_field').slideDown();
		}
	});

  	//Remove Row
  	$( 'body' ).on( 'click', '.remove_row.delete', function(e) {
    	e.preventDefault();
    	if( window.confirm( menuitem_meta_boxes.delete_pricing ) ) {
      		$( this ).parents( '.pl8app-metabox' ).remove();
    	}
    });

  	$( 'body' ).on( 'click', '.remove.pl8app-addon-cat', function(e) {

    	e.preventDefault();
    	if( window.confirm( menuitem_meta_boxes.delete_new_category ) ) {
      		$( this ).parents( '.pl8app-addon.create-new-addon' ).remove();
    	}
  	});

  	//Addon Category Name
  	$( 'body' ).on( 'input keypress', '.pl8app-input.addon-category-name', function(event) {

    	var _self = $( this );
    	var category_name = _self.val();

    	if( event.currentTarget.value.length >= 1 ) {
      		if( category_name !== '' ) {
        		_self.parents( '.pl8app-metabox.create-new-addon' ).find( '.addon_category_name' ).text( category_name );
      		}
    	} else {
      		_self.parents( '.pl8app-metabox.create-new-addon' ).find( '.addon_category_name' ).text( 'Addon category Name' );
      	}
  	});

  	//Variable Price
  	$( '.pl8app-input-variable-name' ).on( 'input keypress', function(event) {

    	var _self = $( this );
    	var option_name = _self.val();

    	if( event.currentTarget.value.length >= 1 ) {
      		if( option_name !== '' ) {
        		_self.parents( '.pl8app-metabox.variable-price' ).find( '.price_name' ).text( option_name );
      		}
    	} else {
      		_self.parents( '.pl8app-metabox.variable-price' ).find( '.price_name' ).text( 'Option Name' );
    	}
  	});

  	// Addon multiple rows
  	$( 'body' ).on( 'click', '.add-new-addon.add-addon-multiple-item', function(e) {

    	e.preventDefault();
    	var SeletedRow = $(this).parents('.pl8app-metabox-content').find('tr.addon-items-row');
    	var ParentRow = SeletedRow.first().clone(true);

    	ParentRow.find( 'input' ).each( function(){
      		$(this).val('');
    	});
    	var LastRow = SeletedRow.last();
    	$( ParentRow ).insertAfter( LastRow );
  	});

	// Add rows.
	$( 'button.add-new-price' ).on( 'click', function() {
		var size     = $( '.pl8app-variable-prices .variable-price' ).length;
		var $wrapper = $( this ).closest( '.pricing' );
		var $prices  = $wrapper.find( '.pl8app-variable-prices' );
		var data     = {
			action   : 'pl8app_add_price',
			i        : size,
			security : menuitem_meta_boxes.add_price_nonce
		};

		$wrapper.block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		$.post( menuitem_meta_boxes.ajax_url, data, function( response ) {
			$prices.find('.add-new-price').before( response );
			$wrapper.unblock();
			$( document.body ).trigger( 'pl8app_added_price' );
		});
		return false;
	});

	// Add new category.
	$( 'button.add-category' ).on( 'click', function() {

        var $wrapper  = $( this ).closest( '.pl8app-category' );
		var name      = $wrapper.find('#pl8app-category-name').val();
		var parent    = $wrapper.find('#pl8app-parent-category').val();

		if( name == '' ){
		  $( this ).parent().find('#pl8app-category-name').focus();
			return;
		}
		var data = {
			action   : 'pl8app_add_category',
			name     : name,
			parent   : parent,
			security : menuitem_meta_boxes.add_category_nonce
		};

		$wrapper.block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		$.post( menuitem_meta_boxes.ajax_url, data, function( response ) {

			if( undefined !== response.term_id ){
				// Create a DOM Option and pre-select by default
				var newOption = new Option( name, response.term_id, true, true );
				$('.pl8app-category-select,#pl8app-parent-category').append( newOption ).trigger('change');
				$wrapper.find('#pl8app-category-name,#pl8app-parent-category').val('').trigger('change');
			}

			$wrapper.unblock();

			$( document.body ).trigger( 'pl8app_added_category' );
		});

		return false;
	});

	$( 'button.add-new-addon,button.create-addon' ).on( 'click', function(e) {

        var isCreate = $( e.target ).hasClass( 'create-addon' );
		var size     = Math.round( (new Date()).getTime() / 1000 );
		var $wrapper = $( this ).closest( '#addons_menuitem_data' );
		var $addons  = $wrapper.find( '.pl8app-addons' );
        var item_id  = $(this).attr('data-item-id');

		var data     = {
			action   : 'pl8app_add_addon',
            item_id  : item_id,
			i        : size,
			iscreate : isCreate,
			security : menuitem_meta_boxes.add_addon_nonce
		};

		$wrapper.block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		$.post( menuitem_meta_boxes.ajax_url, data, function( response ) {
			$addons.append( response );
			$wrapper.unblock();
			$( document.body ).trigger( 'pl8app_added_addon' );
		});
		return false;
	});

	$( '#addons_menuitem_data' ).on( 'click', 'button.load-addon', function(e) {

	    var _self    = $(this);
	  	var parent   = _self.parent( '.addon-category' ).find( 'select' ).val();
        var menuitem = _self.attr('data-item-id');

	    if( parent == '' ) {
	    	pl8app_toast('Error',menuitem_meta_boxes.select_addon_category, 'error');
	    	return false;
	    } else if( $( '.addon-category select option:checked[value="' + parent +'"]' ).length > 1 ) {
	    	pl8app_toast('Error',menuitem_meta_boxes.addon_category_already_selected, 'error');
	    	return false;
	    }

	  	var size     = _self.parents('.addon-category').find('select').attr('data-row-id');
	  	var $wrapper = _self.closest( '.pl8app-metabox-content' );
	  	var $addons  = $wrapper.find( '.addon-items' );
		var data   	 = {
			action  : 'pl8app_load_addon_child',
			parent  : parent,
            item_id : menuitem,
			i       : size,
			security: menuitem_meta_boxes.load_addon_nonce
		};

		$wrapper.block({
			message: null,
			overlayCSS: {
			  background: '#fff',
			  opacity: 0.6
			}
		});

		$.post( menuitem_meta_boxes.ajax_url, data, function( response ) {
			$addons.html( response );
			$wrapper.unblock();
			$( document.body ).trigger( 'pl8app_loaded_addon' );
		});

		return false;

	});
});