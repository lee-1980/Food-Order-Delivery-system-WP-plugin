jQuery(document).ready(function ($) {

    //Get cookie value for delivery fee
    function pl8app_fee_getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1);
            if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
        }
        return "";
    }


    //Setup ajax with our variable when delivery location field is visible
    if ($('.pl8app-section').is(':visible')) {

        $(document).ajaxSend(function (ev, xhr, settings) {
            if (settings.data) {
                if (settings.data.indexOf('pl8app_check_service_slot') != -1 || settings.data.indexOf('pl8app_check_service_slot') != -1) {
                    var delivery_zip;
                    var delivery_location;
                    var delivery_latlng;

                    if (DeliveryFeeVars.delivery_fee_method == 'location_based') {
                        delivery_zip = pl8app_fee_getCookie('delivery_zip');
                    }
                    else {
                        delivery_zip = $('#pl8app_delivery_zone').val();
                    }

                    settings.data += '&' + $.param({
                        delivery_zip: delivery_zip,
                        delivery_location: $('#pl8app_delivery_location').val(),
                        delivery_latlng: $('#pl8app_delivery_latllng').val(),
                    });
                }
            }
        });
    }


    //Show google address on checkout page
    if ($('#pl8app-delivery-address').is(':visible')) {

        if (DeliveryFeeVars.delivery_fee_method == 'location_based') {
            initDeliveryAddress('pl8app-delivery-address');
        }

    }

    $(document).on('pl8app_checked_slots pl8app_cart_item_removed pl8app_items_updated pl8app_added_to_cart', function (e, response) {
        if (response.fee != 0 && response.delivery_fee !== undefined) {
            $('ul.pl8app-cart').find('.pl8app-cart-meta.pl8app_delivery_fee').remove();
            var delivery_fee = '<li class="cart_item pl8app-cart-meta pl8app_delivery_fee"> ' + DeliveryFeeVars.fee + ' <span class="pl8app-delivery-fee ' + pl8app_scripts.color + ' ">' + response.delivery_fee + '</span></li>';
            $(delivery_fee).insertBefore('ul.pl8app-cart li.cart_item.pl8app-cart-meta.pl8app_total');
            $('ul.pl8app-cart').find('.pl8app_delivery_fee span').text(response.delivery_fee);
        }
        else {
            $('ul.pl8app-cart').find('.pl8app-cart-meta.pl8app_delivery_fee').hide();
        }
        $('ul.pl8app-cart').find('.cart-total').html(response.total);
        $('ul.pl8app-cart').find('.cart-subtotal').html(response.subtotal);

        var pla_get_delivery_data = pla_get_storage_data();
        if (!pla_get_delivery_data) {
            $('div.delivery-wrap .delivery-change').text('Choose the Service Type, Date and Slot!');
        }
        else{
            $('div.delivery-wrap .delivery-change').text('Change?');
        }
    });


    $(document).ajaxComplete(function (event, xhr, settings) {
        if (settings.data) {
            if (settings.data.indexOf('pl8app_clear_cart') != -1) {
                $('ul.pl8app-cart').find('.pl8app_delivery_fee').css('display', 'none');
            }
        }

    });


    //Set cookie value for delivery location when ajax has been completed
    $(document).ajaxComplete(function (event, xhr, settings) {

        if (settings.data) {
            if (settings.data.indexOf('pl8app_show_delivery_options') != -1) {

                getZipCode = pl8app_fee_getCookie('delivery_zip');
                getLocationAddress = pl8app_fee_getCookie('delivery_location');
                getLocationLatLng = pl8app_fee_getCookie('delivery_latlng');

                if (DeliveryFeeVars.delivery_fee_method == 'location_based') {

                    initDeliveryAddress('pl8app_delivery_location');

                    if (getLocationAddress !== '') {
                        var LocationAddress = getLocationAddress.replace(/[+]+/g, " ").trim();
                        LocationAddress = LocationAddress.replace(/%2C/g, ",");
                        LocationAddress = LocationAddress.replace(/%20/g, ",");
                        $('input#pl8app_delivery_location').val(LocationAddress);
                    }

                    if (getLocationLatLng !== '') {
                        var LocationLatLng = getLocationLatLng.replace(/[+]+/g, " ").trim();
                        LocationLatLng = LocationLatLng.replace(/%2C/g, ",");
                        LocationLatLng = LocationLatLng.replace(/%20/g, ",");
                        $('input#pl8app_delivery_latllng').val(LocationLatLng);
                    }
                }
                else {
                    if (getZipCode !== '') {
                        var zip = getZipCode.replace(/[+]+/g, " ").trim();
                        $('input#pl8app_delivery_zone').val(zip);
                    }
                }
            }
        }

    });
});


//Init delivery field
function initDeliveryAddress($selector) {

    delivery_fee_location = new google.maps.places.Autocomplete(document.getElementById($selector));

    if (DeliveryFeeVars.store_country.length > 0 && typeof DeliveryFeeVars.store_country.length !== 'undefined') {
        delivery_fee_location.setComponentRestrictions({'country': DeliveryFeeVars.store_country});
    }

    var zip_code = '';
    delivery_fee_location.addListener('place_changed', function () {

        let selectedPlace = document.getElementById($selector).value;

        if (selectedPlace !== '') {
            pl8app_fee_setCookie('delivery_location', selectedPlace, 1);
        }


        let place = this.getPlace();
        var lat = place.geometry.location.lat();
        var lng = place.geometry.location.lng();

        /**
         *Deleting cookies and flush the previous value
         **/
        pl8appFeeDeleteCookie(['delivery_zip', 'city', 'street_address', 'flat']);

        jQuery('input#pl8app-postcode').val('');
        jQuery('input#pl8app-city').val('');
        jQuery('input#pl8app-street-address').val('');
        jQuery('input#pl8app-apt-suite').val('');

        /**
         *Set the cookies  value and input value
         **/
        for (var i = 0; i < place.address_components.length; i++) {
            var addressType = place.address_components[i].types[0];

            //Set zip code
            if (addressType == 'postal_code') {
                zip_code = place.address_components[i]['long_name'];
                jQuery('input#pl8app-postcode').val(zip_code);
                pl8app_fee_setCookie('delivery_zip', zip_code, 1);
            }

            //Set city
            if (addressType == 'locality') {
                var City = place.address_components[i]['long_name'];
                if (City !== '') {
                    jQuery('input#pl8app-city').val(City);
                    pl8app_fee_setCookie('city', City, 1);
                }
            }

            var streetAddress;

            if (addressType == 'route') {
                streetAddress = place.address_components[i]['long_name'];
                if (typeof streetAddress !== 'undefined') {
                    jQuery('input#pl8app-street-address').val(streetAddress);
                    pl8app_fee_setCookie('street_address', streetAddress, 1);
                }
            }


            if (addressType == 'street_number' && streetAddress == '') {
                streetAddress = place.address_components[i]['long_name'];
                if (typeof streetAddress !== 'undefined') {
                    jQuery('input#pl8app-street-address').val(streetAddress);
                    pl8app_fee_setCookie('street_address', streetAddress, 1);
                }
            }
            if (addressType == 'street_number') {
                streetAddress = place.address_components[i]['long_name'];
                if (typeof streetAddress !== 'undefined') {
                    jQuery('input#pl8app-apt-suite').val(streetAddress);
                    pl8app_fee_setCookie('flat', streetAddress, 1);
                }
            }
        }

        var position = lat + ',' + lng;

        if (position !== '') {
            jQuery('#pl8app_delivery_latllng').val(position);
            pl8app_fee_setCookie('delivery_latlng', position, 1);
        }

    });
}

//Set cookie for delivery fee
function pl8app_fee_setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires + ";path=/";
}

/**
 *Deleting cookies value
 */
const pl8appFeeDeleteCookie = (cnames = Array()) => {
    for (var i = 0; i < cnames.length; i++) {
        let cname = cnames[i];
        document.cookie = `${cname}='';expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
    }

}