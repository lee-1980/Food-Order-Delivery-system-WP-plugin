jQuery( function($) {

	function toggle_method_settings( method ) {
		if( method == 'location_based' ) {
			$('.settings-row.location-based-settings').show();
			$('.settings-row.zip-based-settings').hide();
		}
		else {
			$('.settings-row.location-based-settings').hide();
			$('.settings-row.zip-based-settings').show();
		}
	}

	$('body').on( 'change', '.pl8app-delivery-method input[type=radio]', function() {
		var method = $(this).val();
		toggle_method_settings(method);
	});

	var selected_method = _rpDeliveryFee.delivery_fee_method;

	toggle_method_settings( selected_method );

	$( 'body' ).on( 'click', '.add-delivery-location', function(e) {
		e.preventDefault();
		var unix_time = Date.now();

		var CustomHtml = '<tr data-row-id="'+unix_time+'">';
				CustomHtml += '<td>';
				CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_location_fee]['+unix_time+'][fee_amount]">';
				CustomHtml += '</td>';
				CustomHtml += '<td>';
				CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_location_fee]['+unix_time+'][distance]" placeholder="'+_rpDeliveryFee.distance_example+'">';
				CustomHtml += '</td>';
			    CustomHtml += '<td>';
			    CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_location_fee]['+unix_time+'][order_amount]">';
			    CustomHtml += '</td>';
			    CustomHtml += '<td>';
			    CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_location_fee]['+unix_time+'][set_min_order_amount]">';
			    CustomHtml += '</td>';
				CustomHtml += '<td>';
				CustomHtml += '<a href="void(0)" data-row-id="'+unix_time+'" class="pl8app-delivery-fee-remove"></a>';
				CustomHtml += '</td>';
				CustomHtml += '</tr>';


		var Selected = $(this);

		var tableBody = Selected.parents('table#pl8app_delivery_location_fees').find('tbody');

		if ( tableBody.children().length == 0 ) {
			$( tableBody ).append( CustomHtml );
		}
		else {
			Selected.parents('table#pl8app_delivery_location_fees').find('tbody tr').last().after( CustomHtml );
		}

	});


	$( 'body' ).on( 'click', '.pl8app-add-delivery-fee-data', function(e) {

		e.preventDefault();

		var unix_time = Date.now();

		var CustomHtml = '<tr data-row-id="'+unix_time+'">';
                CustomHtml += '<td>';
                CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_fee]['+unix_time+'][driver_group]">';
                CustomHtml += '</td>';
				CustomHtml += '<td>';
				CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_fee]['+unix_time+'][fee_amount]">';
				CustomHtml += '</td>';
				CustomHtml += '<td>';
				CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_fee]['+unix_time+'][zip_code]">';
				CustomHtml += '</td>';
		        CustomHtml += '<td>';
		        CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_fee]['+unix_time+'][order_amount]">';
		        CustomHtml += '</td>';
		        CustomHtml += '<td>';
		        CustomHtml += '<input type="text" name="pl8app_delivery_fee[delivery_fee]['+unix_time+'][set_min_order_amount]">';
		        CustomHtml += '</td>';
				CustomHtml += '<td>';
				CustomHtml += '<a href="void(0)" data-row-id="'+unix_time+'" class="pl8app-delivery-fee-remove"></a>';
				CustomHtml += '</td>';
				CustomHtml += '</tr>';


		var Selected = $(this);

		var tableBody = Selected.parents('table#pl8app_delivery_fees').find('tbody');

		if ( tableBody.children().length == 0 ) {
			$( tableBody ).append( CustomHtml );
		}
		else {
			Selected.parents('table#pl8app_delivery_fees').find('tbody tr').last().after( CustomHtml );
		}

	});

    $('table#pl8app_delivery_fees tr').each( function() {
        $( 'body' ).on( 'click', '.pl8app-delivery-fee-remove',  function(e) {
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    });

	$('body').on('input', 'table#pl8app_delivery_fees input[name*="driver_group"]', function() {
		var current_focused_value = $(this).val();
		$('table#pl8app_delivery_fees input[name*="driver_group"]').not(this).each((i, e) => {
            if(current_focused_value == $(e).val()){
                $('table#pl8app_delivery_fees tfoot .error_msg').html('<p>Delivery Group Name: "<b style="color: red;">' + current_focused_value.trim() +'</b>" is duplicated name, Please try another name.</p>');
                $('#submit').attr('disabled', 'disabled');
                $('table#pl8app_delivery_fees input[name*="zip_code"]').attr('disabled', 'disabled');
			}
			else{
                $('table#pl8app_delivery_fees tfoot .error_msg').html('');
                $('#submit').removeAttr('disabled');
                $('table#pl8app_delivery_fees input[name*="zip_code"]').removeAttr('disabled');
			}
		})
	});

    $('body').on('input', 'table#pl8app_delivery_fees input[name*="zip_code"]', function() {

        var current_focused_value = $(this).val();
        var current_postcode_array = current_focused_value.split(',').map(function (value) {
            return value.trim();
        });;

        $('table#pl8app_delivery_fees input[name*="zip_code"]').not(this).each((i, e) => {
        	if($(e).val()) {
                var postcode_array = $(e).val().split(',').map(function (value) {
                    return value.trim();
                });
                var duplicated_postcode;

                var duplicated = postcode_array.some(item => {
                	if(current_postcode_array.includes(item)) {
                		duplicated_postcode = item;
                		return true;
                	}

                    var same_area = current_postcode_array.some( code => {
                		if(item.slice(-1) == '*'){
                			if(/\s/g.test(code)){
                                var samecity_postcode_array = code.split(' ');
                                if(item.slice(0, -1) == samecity_postcode_array[0]){
                                    duplicated_postcode = code;
                                    return true;
                                }
							}
							else{
                                if(item.slice(0, -1) == code){
                                    duplicated_postcode = code;
                                    return true;
                                }
							}

						}
						else if(code.slice(-1) == '*'){
							if(/\s/g.test(item)){
                                var samecity_postcode_array = item.split(' ');
                                if(code.slice(0, -1) == samecity_postcode_array[0]){
                                    duplicated_postcode = code;
                                    return true;
                                }
							}
							else{
                                if(code.slice(0, -1) == item){
                                    duplicated_postcode = code;
                                    return true;
                                }
							}

						}
                    });
                	return same_area;
                });

				if(duplicated){
                    $('table#pl8app_delivery_fees tfoot .error_msg').html('<p>PostCode: "<b style="color: red;">' + duplicated_postcode +'</b>" is duplicated!.</p>');
                    $('#submit').attr('disabled', 'disabled');
                    $('table#pl8app_delivery_fees input[name*="driver_group"]').attr('disabled', 'disabled');
				}
				else{
                    $('table#pl8app_delivery_fees tfoot .error_msg').html('');
                    $('#submit').removeAttr('disabled');
                    $('table#pl8app_delivery_fees input[name*="driver_group"]').removeAttr('disabled');
				}
			}
        })
    });

  if ( jQuery('#pl8app-address').is(':visible') ) {
    initStoreAddress('pl8app-address');
  }
});

function initStoreAddress( $selector ) {
  store_address = new google.maps.places.Autocomplete(document.getElementById($selector));

  store_address.addListener('place_changed', function() {
    
    let selectedPlace = document.getElementById($selector).value;
    document.getElementById('pl8app-address').value = selectedPlace;
    
    let place = this.getPlace();
    var lat = place.geometry.location.lat();
    var lng = place.geometry.location.lng();

    document.getElementById('pl8app_map_latlng').value = lat+','+lng;
  });
}

var geocoder;
var map;
var marker;
var markers = [];
var infowindow;


//Init GoogleMap
function initMap() {

  var lat_postion = Number(_rpDeliveryFee.store_lat_position);

  var lng_position = Number(_rpDeliveryFee.store_lng_position);

  var default_location = { lat: lat_postion, lng: lng_position };

  map = new google.maps.Map(
    document.getElementById('pl8app_map_canvas'), {zoom: 16, center: default_location }
  );

  infowindow = new google.maps.InfoWindow({ content: '' });

  marker = new google.maps.Marker({
    position    : default_location, 
    map         : map,
    draggable   : true
  });

  markers.push(marker);
  
  google.maps.event.addListener( marker, 'dragend', function (evt) {
    document.getElementById('pl8app_map_latlng').value = evt.latLng.lat() + ',' + evt.latLng.lng();;
    geocodePosition( marker.getPosition() );
  });
  
  geocoder = new google.maps.Geocoder();

  document.getElementById('pl8app-location-submit').addEventListener('click', function() {
  	geocodeAddress(geocoder, map);
  });
  
}

function geocodePosition(pos) {
  geocoder.geocode( { latLng: pos }, function(responses) {
    if ( responses && responses.length > 0 ) {
      marker.formatted_address = responses[0].formatted_address;
    } 
    else {
      marker.formatted_address = 'Cannot determine address at this location.';
    }

    infowindow.setContent(marker.formatted_address+"<br>coordinates: "+marker.getPosition().toUrlValue(6));
    infowindow.open(map, marker);

  });
}


function geocodeAddress(geocoder, resultsMap) {

  deleteMarkers();

	var address = document.getElementById('pl8app-address').value;
  
  geocoder.geocode({'address': address}, function(results, status) {
  	if ( status === 'OK' ) {
  		resultsMap.setCenter( results[0].geometry.location );
            
	  	var marker = new google.maps.Marker({
	    	map 			: resultsMap,
	    	position	: results[0].geometry.location,
	    	draggable : true
	  	});

      markers.push(marker);

    	google.maps.event.addListener(marker, 'dragend', function (evt) {
    		document.getElementById('pl8app_map_latlng').value = evt.latLng.lat() + ',' + evt.latLng.lng();
        //geocodePosition( marker.getPosition() );
    	});

  	} 
  	else {
  		alert( 'Geocode was not successful for the following reason: ' + status );
  	}
  });

}

// Sets the map on all markers in the array.
function setMapOnAll(map) {
  for (var i = 0; i < markers.length; i++) {
    markers[i].setMap(map);
  }
}

// Removes the markers from the map, but keeps them in the array.
function clearMarkers() {
  setMapOnAll(null);
}

// Shows any markers currently in the array.
function showMarkers() {
  setMapOnAll(map);
}

// Deletes all markers in the array by removing references to them.
function deleteMarkers() {
  clearMarkers();
  markers = [];
}