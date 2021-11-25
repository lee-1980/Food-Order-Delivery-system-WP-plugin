jQuery( function ( $ ) {

	// Run tipTip
	function runTipTip() {
		// Remove any lingering tooltips
		$( '#tiptip_holder' ).removeAttr( 'style' );
		$( '#tiptip_arrow' ).removeAttr( 'style' );
		$( '.tips' ).tipTip({
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		});
	}

	runTipTip();

	$( '.pl8app-metaboxes-wrapper' ).on( 'click', '.pl8app-metabox > h3', function() {
		$( this ).parent( '.pl8app-metabox' ).toggleClass( 'closed' ).toggleClass( 'open' );
	});

	$( '.pl8app-select2' ).select2();

	// Tabbed Panels
	$( document.body ).on( 'pl8app-init-tabbed-panels', function() {
		$( 'ul.pl8app-tabs' ).show();
		$( 'ul.pl8app-tabs a' ).click( function( e ) {
			e.preventDefault();
			var panel_wrap = $( this ).closest( 'div.panel-wrap' );
			$( 'ul.pl8app-tabs li', panel_wrap ).removeClass( 'active' );
			$( this ).parent().addClass( 'active' );
			$( 'div.panel', panel_wrap ).hide();
			$( $( this ).attr( 'href' ) ).show();
		});
		$( 'div.panel-wrap' ).each( function() {
			$( this ).find( 'ul.pl8app-tabs li' ).eq( 0 ).find( 'a' ).click();
		});
	}).trigger( 'pl8app-init-tabbed-panels' );


	// Date Picker
	$( document.body ).on( 'pl8app-init-datepickers', function() {
		$( '.date-picker-field, .date-picker' ).datepicker({
			dateFormat: 'yy-mm-dd',
			numberOfMonths: 1,
			showButtonPanel: true
		});
	}).trigger( 'pl8app-init-datepickers' );

	// Meta-Boxes - Open/close
	$( '.pl8app-metaboxes-wrapper' ).on( 'click', '.pl8app-metabox h3', function( event ) {
		// If the user clicks on some form input inside the h3, like a select list (for variations), the box should not be toggled
		if ( $( event.target ).filter( ':input, option, .sort' ).length ) {
			return;
		}

		$( this ).next( '.pl8app-metabox-content' ).stop().slideToggle();
	})
	.on( 'click', '.expand_all', function() {
		$( this ).closest( '.pl8app-metaboxes-wrapper' ).find( '.pl8app-metabox > .pl8app-metabox-content' ).show();
		return false;
	})
	.on( 'click', '.close_all', function() {
		$( this ).closest( '.pl8app-metaboxes-wrapper' ).find( '.pl8app-metabox > .pl8app-metabox-content' ).hide();
		return false;
	});
	$( '.pl8app-metabox.closed' ).each( function() {
		$( this ).find( '.pl8app-metabox-content' ).hide();
	});
});