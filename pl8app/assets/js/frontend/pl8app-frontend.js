/* Get pl8app Cookie */
function pla_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1);
        if (c.indexOf(name) != -1) return c.substring(name.length, c.length);
    }
    return "";
}

/* Set pl8app Cookie */
function pla_setCookie(cname, cvalue, ex_time) {
    var d = new Date();
    d.setTime(d.getTime() + (ex_time * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires + ";path=/";
}

/* Get pl8app Storage Data */
function pla_get_storage_data() {

    var serviceType = pla_getCookie('service_type');
    var serviceTime = pla_getCookie('service_time');

    if (typeof serviceType == undefined || serviceType == '') {
        return false;
    } else {
        return true;
    }
}

/* Display Dynamic Addon Price Based on Selected Variation */
function show_dymanic_pricing(container, ele) {
    var price_key = ele.val();
    if (price_key !== 'undefined') {
        jQuery('#' + container + ' .pl8app-addons-data-wrapper .menu-item-list').removeClass('active');
        jQuery('#' + container + ' .pl8app-addons-data-wrapper .menu-item-list.list_' + price_key).addClass('active');
    }
}

/* Calculate Live Price On Click */
function update_modal_live_price(menuitem_container) {

    var single_price = 0;
    var quantity = parseInt(jQuery('input[name=quantity]').val());

    /* Act on the variations */
    jQuery('#' + menuitem_container + ' .pl8app-variable-price-wrapper .menu-item-list').each(function () {

        var element = jQuery(this).find('input');

        if (element.is(':checked')) {

            // Dynamic addon Price
            show_dymanic_pricing(menuitem_container, element);

            var attrs = element.attr('data-value');
            var attrs_arr = attrs.split('|');
            var price = attrs_arr[2];

            single_price = parseFloat(price);
        }
    });

    /* Act on the addons */
    jQuery('#' + menuitem_container + ' .pl8app-addons-data-wrapper .menu-item-list.active').each(function () {

        var element = jQuery(this).find('input');

        // element.prop("type") == 'radio'
        if (element.is(':checked')) {

            var attrs = element.val();
            var attrs_arr = attrs.split('|');
            var price = attrs_arr[2];

            if (price != '') {
                single_price = parseFloat(single_price) + parseFloat(price);
            }
        }
    });


    /* Check if Bundled Products are listed and consider the price of them into total price     */
    var item_type = jQuery('#pl8appModal .cart-item-price').attr('data-item-type');
    var bundled_items = jQuery('.bundled-menu-item-content > div.bundled-menu-item-wrap').length;
    if(item_type == 'bundle' && bundled_items > 0){

        jQuery('.bundled-menu-item-content > div.bundled-menu-item-wrap').each(function(i, e){
            var sub_price = jQuery(this).data('price');
            if (sub_price != '') {
                single_price = parseFloat(single_price) + parseFloat(sub_price);
            }
            /* Add the Sub bundled Addon price        */
            jQuery(e).find('.pl8app-addons-data-wrapper .menu-item-list.active').each(function () {
                var element = jQuery(this).find('input');

                // element.prop("type") == 'radio'
                if (element.is(':checked')) {

                    var attrs = element.val();
                    var attrs_arr = attrs.split('|');
                    var price = attrs_arr[2];

                    if (price != '') {
                        single_price = parseFloat(single_price) + parseFloat(price);
                    }
                };
            })
        });

        single_price = single_price - parseFloat(jQuery('#pl8appModal .cart-item-price').attr('data-price'));
    }
    else{
        single_price += parseFloat(jQuery('#pl8appModal .cart-item-price').attr('data-price'));
    }
    /* Updating as per current quantity */
    total_price = single_price * quantity;

    /* Update the price in Submit Button */
    jQuery('#pl8appModal .cart-item-price').html(pla_scripts.currency_sign + total_price.toFixed(2));
    jQuery('#pl8appModal .cart-item-price').attr('data-current', single_price.toFixed(2));
}

/* Add bundled items to Cart*/
function add_bundle_to_cart(container, data) {

    jQuery(container).find('form .menu-item-list.active input').each(function (i, e) {
        jQuery(e).attr('name', jQuery(e).attr('data-bundle-name'));
    })
    var this_form = jQuery(container).find('form .menu-item-list.active input');
    var itemId = jQuery(container).attr('data-id');
    var FormData = this_form.serializeArray();

    var sub_data = {
        action: data.action,
        menuitem_id: itemId,
        menuitem_qty: data.menuitem_qty,
        special_instruction: data.special_instruction,
        post_data: FormData,
        security: data.security
    };

    return jQuery.ajax({
        type: "POST",
        data: sub_data,
        dataType: "json",
        url: pla_scripts.ajaxurl,
        xhrFields: {
            withCredentials: true
        },
        success: function (response) {
            if(response){

                jQuery('ul.pl8app-cart').find('li.cart_item.empty').remove();
                jQuery('ul.pl8app-cart').find('li.cart_item.pl8app_subtotal').remove();
                jQuery('ul.pl8app-cart').find('li.cart_item.cart-sub-total').remove();
                jQuery('ul.pl8app-cart').find('li.cart_item.pl8app_cart_tax').remove();
                jQuery('ul.pl8app-cart').find('li.cart_item.pl8app-cart-meta.pl8app-delivery-fee').remove();
                jQuery('ul.pl8app-cart').find('li.cart_item.pl8app-cart-meta.pl8app_subtotal').remove();

                jQuery(response.cart_item).insertBefore('ul.pl8app-cart li.cart_item.pl8app_total');

                if (jQuery('.pl8app-cart').find('.pl8app-cart-meta.pl8app_subtotal').is(':first-child')) {
                    jQuery(this).hide();
                }

                jQuery('.pl8app-cart-quantity').show().text(response.cart_quantity);
                jQuery('.cart_item.pl8app-cart-meta.pl8app_total').find('.cart-total').text(response.total);
                jQuery('.cart_item.pl8app-cart-meta.pl8app_subtotal').find('.subtotal').text(response.total);
                jQuery('.cart_item.pl8app-cart-meta.pl8app_total').css('display', 'block');
                jQuery('.cart_item.pl8app-cart-meta.pl8app_subtotal').css('display', 'block');
                jQuery('.cart_item.pl8app_checkout').addClass(pla_scripts.button_color);
                jQuery('.cart_item.pl8app_checkout').css('display', 'block');


                var subTotal = '<li class="cart_item pl8app-cart-meta pl8app_subtotal">' + pla_scripts.total_text + '<span class="cart-subtotal">' + response.subtotal + '</span></li>';
                if (response.taxes) {
                    var taxHtml = '<li class="cart_item pl8app-cart-meta pl8app_cart_tax">' + response.taxes + '</li>';
                    jQuery(taxHtml).insertBefore('ul.pl8app-cart li.cart_item.pl8app_total');
                    jQuery(subTotal).insertBefore('ul.pl8app-cart li.cart_item.pl8app_cart_tax');
                }

                if (response.taxes === undefined) {
                    jQuery('ul.pl8app-cart').find('.cart_item.pl8app-cart-meta.pl8app_subtotal').remove();
                    var cartLastChild = $('ul.pl8app-cart>li.pl8app-cart-item:last');
                    jQuery(subTotal).insertAfter(cartLastChild);
                }

                jQuery(document.body).trigger('pl8app_added_to_cart', [response]);
                jQuery('ul.pl8app-cart').find('.cart-total').html(response.total);
                jQuery('ul.pl8app-cart').find('.cart-subtotal').html(response.subtotal);

                if (jQuery('li.pl8app-cart-item').length > 0) {
                    jQuery('a.pl8app-clear-cart').show();
                } else {
                    jQuery('a.pl8app-clear-cart').hide();
                }

                jQuery(document.body).trigger('pl8app_added_to_cart', [response]);
            }
        }
    });
}

/* pl8app Frontend Functions */
jQuery(function ($) {

    var noticeID   = $('.pl8app-store-notice').data( 'notice-id' ) || '',
        cookieName = 'store_notice' + noticeID;

    // Check the value of that cookie and show/hide the notice accordingly
    if ('hidden' === Cookies.get( cookieName ) ) {
        $('.pl8app-store-notice').hide();
    } else {
        $('.pl8app-store-notice').show();
    }

    // Set a cookie and hide the store notice when the dismiss button is clicked
    $('.pl8app-store-notice__dismiss-link').on( 'click', function( event ) {
        Cookies.set( cookieName, 'hidden', { path: '/' } );
        $( '.pl8app-store-notice' ).hide();
        event.preventDefault();
    });

    // Sticky category menu on mobile
    var initTopPosition = $('.sticky-sidebar').offset().top;
    $(window).scroll(function () {
        if ($(window).scrollTop() > initTopPosition)
            $('.sticky-sidebar').addClass('mobile-sticky');
        else
            $('.sticky-sidebar').removeClass('mobile-sticky');
    });

    //Remove loading from modal
    $('#pl8appModal').removeClass('loading');

    //Remove service options from modal
    $('#pl8appModal').removeClass('show-service-options');
    $('#pl8appModal').removeClass('minimum-order-notice');

    $('#pl8appModal').on('hidden.bs.modal', function () {
        $('#pl8appModal').removeClass('show-service-options');
        $('#pl8appModal').removeClass('minimum-order-notice');
    });

    var ServiceType = pla_scripts.service_options;

    if (
        ServiceType['delivery'] &&  ServiceType['delivery'] == 1
        && ServiceType['pickup'] && ServiceType['pickup'] == 1) {
        ServiceType = 'delivery';
    }


    // Add to Cart
    $('.pl8app-add-to-cart').click(function (e) {

        e.preventDefault();

        var pla_get_delivery_data = pla_get_storage_data();

        $('#pl8appModal').addClass('loading');
        $('#pl8appModal .modal-body').html('<span class="pl8app-loader">' + pla_scripts.please_wait + '</span>');

        $('#pl8appModal').removeClass('pl8app-delivery-options pl8app-menu-options checkout-error');
        $('#pl8appModal .qty').val('1');
        $('#pl8appModal').find('.cart-action-text').html(pla_scripts.add_to_cart);

        if (!pla_get_delivery_data) {
            var action = 'pl8app_show_delivery_options';
            var security = pla_scripts.service_type_nonce;
            $('#pl8appModal').addClass('show-service-options');
        } else {
            var action = 'pl8app_show_products';
            var security = pla_scripts.show_products_nonce;
        }

        var _self = $(this);
        var menuitem_id = _self.attr('data-menuitem-id');
        var price = _self.attr('data-price');
        var variable_price = _self.attr('data-variable-price');

        var data = {
            action: action,
            menuitem_id: menuitem_id,
            security: security,
        };

        MicroModal.show('pl8appModal');

        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: pla_scripts.ajaxurl,
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {

                $('#pl8appModal').removeClass('loading');
                $('#pl8appModal .modal-title').html(response.data.html_title);
                $('#pl8appModal .modal-body').html(response.data.html);
                $('#pl8appModal .cart-item-price').html(response.data.price);
                $('#pl8appModal .cart-item-price').attr('data-price', response.data.price_raw);
                $('#pl8appModal .cart-item-price').attr('data-item-type', response.data.type);

                if ($('.pl8app-tabs-wrapper').length) {
                    $('#pl8appdeliveryTab > li:first-child > a').length > 0?$('#pl8appdeliveryTab > li:first-child > a')[0].click():'';
                }

                // Trigger event so themes can refresh other areas.
                $(document.body).trigger('opened_service_options', [response.data]);

                $('#pl8appModal').find('.submit-menuitem-button').attr('data-cart-action', 'add-cart');
                $('#pl8appModal').find('.cart-action-text').html(pla_scripts.add_to_cart);

                if (menuitem_id !== '' && price !== '') {
                    $('#pl8appModal').find('.submit-menuitem-button').attr('data-item-id', menuitem_id); //setter
                    $('#pl8appModal').find('.submit-menuitem-button').attr('data-item-price', price);
                    $('#pl8appModal').find('.submit-menuitem-button').attr('data-item-qty', 1);
                }

                update_modal_live_price('menuitem-details');
            }

        });
    });

    // Update Cart
    $('.pl8app-sidebar-cart').on('click', 'a.pl8app-edit-from-cart', function (e) {
        e.preventDefault();

        var _self = $(this);
        _self.parents('.pl8app-cart-item').addClass('edited');

        var CartItemId = _self.attr('data-remove-item');
        var MenuItemId = _self.attr('data-item-id');
        var MenuItemName = _self.attr('data-item-name');
        var MenuQuantity = _self.parents('.pl8app-cart-item').find('.pl8app-cart-item-qty').text();
        var action = 'pl8app_edit_cart_menuitem';
        var security = pla_scripts.edit_cart_menuitem_nonce;

        MicroModal.show('pl8appModal');

        $('#pl8appModal').addClass('loading');
        $('#pl8appModal .modal-body').html('<span class="pl8app-loader">' + pla_scripts.please_wait + '</span>');

        var data = {
            action: action,
            cartitem_id: CartItemId,
            menuitem_id: MenuItemId,
            menuitem_name: MenuItemName,
            security: security,
        };

        if (CartItemId !== '') {

            $.ajax({
                type: "POST",
                data: data,
                dataType: "json",
                url: pla_scripts.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {

                    $('#pl8appModal').removeClass('checkout-error');
                    $('#pl8appModal').removeClass('show-service-options');
                    $('#pl8appModal').removeClass('loading');
                    $('#pl8appModal .modal-title').html(response.data.html_title);

                    $('#pl8appModal').find(".qty").val(MenuQuantity);
                    $('#pl8appModal').find('.submit-menuitem-button').attr('data-item-id', MenuItemId);
                    $('#pl8appModal').find('.submit-menuitem-button').attr('data-cart-key', CartItemId);
                    $('#pl8appModal').find('.submit-menuitem-button').attr('data-cart-action', 'update-cart');
                    $('#pl8appModal').find('.submit-menuitem-button').find('.cart-action-text').html(pla_scripts.update_cart);
                    $('#pl8appModal').find('.submit-menuitem-button').find('.cart-item-price').html(response.data.price);
                    $('#pl8appModal').find('.submit-menuitem-button').find('.cart-item-price').attr('data-price', response.data.price_raw);
                    $('#pl8appModal').find('.submit-menuitem-button').attr('data-item-qty', MenuQuantity);
                    $('#pl8appModal .modal-body').html(response.data.html);

                    update_modal_live_price('menuitem-update-details');
                }
            });
        }
    });

    // Add to Cart / Update Cart Button From Popup
    $(document).on('click', '.submit-menuitem-button',async function (e) {

        e.preventDefault();

        var self = $(this);
        var cartAction = self.attr('data-cart-action');
        var text = self.find('span.cart-action-text').text();
        var validation = '';

        self.find('.cart-action-text').text(pla_scripts.please_wait);

        // Checking the Required & Max addon settings for Addons
        if (jQuery('.addons-wrapper').length > 0) {

            jQuery('.addons-wrapper').each(function (index, el) {

                var _self = jQuery(this);
                var addon = _self.attr('data-id');
                var is_required = _self.children('input.addon_is_required').val();
                var max_addons = _self.children('input.addon_max_limit').val();
                var checked = _self.find('.menu-item-list.active input:radio:checked').length;
                // console.log(checked);
                _self.find('.pl8app-addon-error').removeClass('pl8app-addon-error');
                if (is_required == 'yes' && checked == 0) {
                    _self.find('.pl8app-addon-required').addClass('pl8app-addon-error');
                    validation = 1;
                } else if (max_addons != 0 && checked > max_addons) {
                    _self.find('.pl8app-max-addon').addClass('pl8app-addon-error');
                    validation = 1;
                }

                if (validation != '') {
                    self.removeClass('disable_click');
                    self.find('.cart-action-text').text(text);
                    return false;
                }
            });
        }

        if (cartAction == 'add-cart' && validation == '') {

            self.addClass('disable_click');

            var this_form = self.parents('.modal').find('form#menuitem-details .menu-item-list.active input');
            var itemId = self.attr('data-item-id');
            var itemQty = self.attr('data-item-qty');
            var FormData = this_form.serializeArray();
            var SpecialInstruction = self.parents('.modal').find('textarea.special-instructions').val();
            var action = 'pl8app_add_to_cart';
            var item_type = jQuery('#pl8appModal .cart-item-price').attr('data-item-type');
            var bundled_items = jQuery('.bundled-menu-item-content > div.bundled-menu-item-wrap').length;

            var data = {
                action: action,
                menuitem_id: itemId,
                menuitem_qty: itemQty,
                special_instruction: SpecialInstruction,
                post_data: FormData,
                security: pla_scripts.add_to_cart_nonce
            };
            // console.log(data);
            /**
             * Check the Bundle possibilities
             */
            if(item_type == 'bundle' && bundled_items > 0){
                for(var i = 0 ; i < bundled_items; i++){
                    var container = jQuery('.bundled-menu-item-content > div.bundled-menu-item-wrap')[i];
                    await add_bundle_to_cart(container, data);
                }
            }
            if (itemId !== '') {
                $.ajax({
                    type: "POST",
                    data: data,
                    dataType: "json",
                    url: pla_scripts.ajaxurl,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {
                        if (response) {

                            self.removeClass('disable_click');
                            self.find('.cart-action-text').text(text);

                            var serviceType = pla_getCookie('service_type');

                            var serviceTime = pla_getCookie('service_time');
                            var serviceTimeText = pla_getCookie('service_time_text');
                            var serviceDate = pla_getCookie('delivery_date');

                            $('ul.pl8app-cart').find('li.cart_item.empty').remove();
                            $('ul.pl8app-cart').find('li.cart_item.pl8app_subtotal').remove();
                            $('ul.pl8app-cart').find('li.cart_item.cart-sub-total').remove();
                            $('ul.pl8app-cart').find('li.cart_item.pl8app_cart_tax').remove();
                            $('ul.pl8app-cart').find('li.cart_item.pl8app-cart-meta.pl8app-delivery-fee').remove();
                            $('ul.pl8app-cart').find('li.cart_item.pl8app-cart-meta.pl8app_subtotal').remove();

                            $(response.cart_item).insertBefore('ul.pl8app-cart li.cart_item.pl8app_total');

                            if ($('.pl8app-cart').find('.pl8app-cart-meta.pl8app_subtotal').is(':first-child')) {
                                $(this).hide();
                            }

                            $('.pl8app-cart-quantity').show().text(response.cart_quantity);
                            $('.cart_item.pl8app-cart-meta.pl8app_total').find('.cart-total').text(response.total);
                            $('.cart_item.pl8app-cart-meta.pl8app_subtotal').find('.subtotal').text(response.total);
                            $('.cart_item.pl8app-cart-meta.pl8app_total').css('display', 'block');
                            $('.cart_item.pl8app-cart-meta.pl8app_subtotal').css('display', 'block');
                            $('.cart_item.pl8app_checkout').addClass(pla_scripts.button_color);
                            $('.cart_item.pl8app_checkout').css('display', 'block');

                            if (serviceType !== undefined && serviceType != 'undefined') { console.log(serviceType);
                                serviceLabel = window.localStorage.getItem('serviceLabel');
                                var orderInfo = '<span class="delMethod">' + serviceLabel + ', ' + serviceDate + '</span>';

                                if (serviceTime !== undefined) {
                                    orderInfo += '<span class="delTime">, ' + serviceTimeText + '</span>';
                                }

                                $('.delivery-items-options').find('.delivery-opts').html(orderInfo);

                                if ($('.delivery-wrap .delivery-change').length == 0) {
                                    $("<a href='#' class='delivery-change'>" + pla_scripts.change_txt + "</a>").insertAfter(".delivery-opts");
                                }
                            }
                            else {
                                var orderInfo = '<span class="delMethod">No Service, ' + serviceDate + '</span>';

                                if (serviceTime !== undefined) {
                                    orderInfo += '<span class="delTime">, ' + serviceTimeText + '</span>';
                                }

                                $('.delivery-items-options').find('.delivery-opts').html(orderInfo);

                                if ($('.delivery-wrap .delivery-change').length == 0) {
                                    $("<a href='#' class='delivery-change'>" + pla_scripts.change_txt + "</a>").insertAfter(".delivery-opts");
                                }
                            }

                            $('.delivery-items-options').css('display', 'block');

                            var subTotal = '<li class="cart_item pl8app-cart-meta pl8app_subtotal">' + pla_scripts.total_text + '<span class="cart-subtotal">' + response.subtotal + '</span></li>';
                            if (response.taxes) {
                                var taxHtml = '<li class="cart_item pl8app-cart-meta pl8app_cart_tax">' + response.taxes + '</li>';
                                $(taxHtml).insertBefore('ul.pl8app-cart li.cart_item.pl8app_total');
                                $(subTotal).insertBefore('ul.pl8app-cart li.cart_item.pl8app_cart_tax');
                            }

                            if (response.taxes === undefined) {
                                $('ul.pl8app-cart').find('.cart_item.pl8app-cart-meta.pl8app_subtotal').remove();
                                var cartLastChild = $('ul.pl8app-cart>li.pl8app-cart-item:last');
                                $(subTotal).insertAfter(cartLastChild);
                            }

                            $(document.body).trigger('pl8app_added_to_cart', [response]);
                            $('ul.pl8app-cart').find('.cart-total').html(response.total);
                            $('ul.pl8app-cart').find('.cart-subtotal').html(response.subtotal);

                            if ($('li.pl8app-cart-item').length > 0) {
                                $('a.pl8app-clear-cart').show();
                            } else {
                                $('a.pl8app-clear-cart').hide();
                            }

                            $(document.body).trigger('pl8app_added_to_cart', [response]);
                            MicroModal.close('pl8appModal');
                        }
                    }
                })
            }
        }

        if (cartAction == 'update-cart' && validation == '') {

            self.addClass('disable_click');

            var this_form = self.parents('.modal').find('form#menuitem-update-details .menu-item-list.active input');
            var itemId = self.attr('data-item-id');
            var itemPrice = self.attr('data-item-price');
            var cartKey = self.attr('data-cart-key');
            var itemQty = self.attr('data-item-qty');
            var FormData = this_form.serializeArray();
            var SpecialInstruction = self.parents('.modal').find('textarea.special-instructions').val();
            var action = 'pl8app_update_cart_items';

            var data = {
                action: action,
                menuitem_id: itemId,
                menuitem_qty: itemQty,
                menuitem_cartkey: cartKey,
                special_instruction: SpecialInstruction,
                post_data: FormData,
                security: pla_scripts.update_cart_item_nonce
            };

            if (itemId !== '') {

                $.ajax({
                    type: "POST",
                    data: data,
                    dataType: "json",
                    url: pla_scripts.ajaxurl,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (response) {

                        self.removeClass('disable_click');
                        self.find('.cart-action-text').text(text);

                        if (response) {

                            html = response.cart_item;

                            $('ul.pl8app-cart').find('li.cart_item.empty').remove();

                            $('.pl8app-cart >li.pl8app-cart-item').each(function (index, item) {
                                $(this).find("[data-cart-item]").attr('data-cart-item', index);
                                $(this).attr('data-cart-key', index);
                                $(this).attr('data-remove-item', index);
                            });

                            $('ul.pl8app-cart').find('li.edited').replaceWith(function () {

                                let obj = $(html);
                                obj.attr('data-cart-key', response.cart_key);

                                obj.find("a.pl8app-edit-from-cart").attr("data-cart-item", response.cart_key);
                                obj.find("a.pl8app-edit-from-cart").attr("data-remove-item", response.cart_key);

                                obj.find("a.pl8app_remove_from_cart").attr("data-cart-item", response.cart_key);
                                obj.find("a.pl8app_remove_from_cart").attr("data-remove-item", response.cart_key);

                                return obj;
                            });

                            $('ul.pl8app-cart').find('.cart-total').html(response.total);
                            $('ul.pl8app-cart').find('.cart-subtotal').html(response.subtotal);
                            if (response.taxes) {
                                $('ul.pl8app-cart').find('.pl8app_cart_tax').html(response.taxes);
                            }
                            else{
                                $('ul.pl8app-cart').find('.pl8app_cart_tax').html('');
                            }

                            $(document.body).trigger('pl8app_items_updated', [response]);
                            MicroModal.close('pl8appModal');
                        }
                    }
                });
            }
        }
    });

    // Add Service Date and Time
    $('body').on('click', '.pl8app-delivery-opt-update', function (e) {
        e.preventDefault();

        var _self = $(this);
        var menuItemId = _self.attr('data-menu-id');
        var paymentId = _self.attr('data-payment-id');

        if ($('.pl8app-tabs-wrapper').find('.nav-item.active a').length > 0) {
            var serviceType = $('.pl8app-tabs-wrapper').find('.nav-item.active a').attr('data-service-type');
            var serviceLabel = $('.pl8app-tabs-wrapper').find('.nav-item.active a').text().trim();
            //Store the service label for later use
            window.localStorage.setItem('serviceLabel', serviceLabel);
        }

        var serviceTime = _self.parents('.pl8app-tabs-wrapper').find('.delivery-settings-wrapper.active .pl8app-hrs').val();
        var serviceTimeText = _self.parents('.pl8app-tabs-wrapper').find('.delivery-settings-wrapper.active .pl8app-hrs option:selected').text();
        var serviceDate = _self.parents('.pl8app-tabs-wrapper').find('.delivery-settings-wrapper.active .pl8app_get_delivery_dates').val();

        if (serviceTime === undefined && (pl8app_scripts.pickup_time_enabled == 1 && serviceType == 'pickup' || pl8app_scripts.delivery_time_enabled == 1 && serviceType == 'delivery')) {
            _self.parents('.pl8app-delivery-wrap').find('.pl8app-errors-wrap').text('Please select time for ' + serviceLabel);
            _self.parents('.pl8app-delivery-wrap').find('.pl8app-errors-wrap').removeClass('disabled').addClass('enable');
            return false;
        }

        var sDate = serviceDate === undefined ? pl8app_scripts.current_date : serviceDate;

        _self.parents('.pl8app-delivery-wrap').find('.pl8app-errors-wrap').removeClass('enable').addClass('disabled');
        _self.text(pl8app_scripts.please_wait);

        var action = 'pl8app_check_service_slot';
        var data = {
            action: action,
            serviceType: serviceType,
            serviceTime: serviceTime,
            service_date: sDate
        };
        console.log(data);
        if(paymentId !== '' && paymentId !== undefined) data.payment_id = paymentId;
        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: pl8app_scripts.ajaxurl,
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {

                if (response.status == 'error') {

                    _self.text(pl8app_scripts.update);
                    _self.parents('#pl8appModal').find('.pl8app-errors-wrap').html(response.msg).removeClass('disabled');
                    return false;

                } else {

                    pla_setCookie('service_type', serviceType, pla_scripts.expire_cookie_time);

                    if (serviceDate === undefined) {

                        pla_setCookie('service_date', pl8app_scripts.current_date, pla_scripts.expire_cookie_time);
                        pla_setCookie('delivery_date', pl8app_scripts.display_date, pla_scripts.expire_cookie_time);

                    } else {

                        var delivery_date = $('.delivery-settings-wrapper.active .pl8app_get_delivery_dates option:selected').text();
                        pla_setCookie('service_date', serviceDate, pla_scripts.expire_cookie_time);
                        pla_setCookie('delivery_date', delivery_date, pla_scripts.expire_cookie_time);
                    }

                    if (serviceTime === undefined) {
                        pla_setCookie('service_time', '', pla_scripts.expire_cookie_time);
                    } else {
                        pla_setCookie('service_time', serviceTime, pla_scripts.expire_cookie_time);
                        pla_setCookie('service_time_text', serviceTimeText, pla_scripts.expire_cookie_time);
                    }

                    $('#pl8appModal').removeClass('show-service-options');

                    if (menuItemId) {

                        $('#pl8appModal').addClass('loading');
                        $('#pl8app_menuitem_' + menuItemId).find('.pl8app-add-to-cart').trigger('click');

                    } else {

                        MicroModal.close('pl8appModal');

                        if (typeof serviceType !== 'undefined' && typeof serviceTime !== 'undefined') {

                            $('.delivery-wrap .delivery-opts').html('<span class="delMethod">' + serviceLabel + ',</span> <span class="delTime"> ' + Cookies.get('delivery_date') + ', ' + serviceTimeText + '</span>');

                        } else if (typeof serviceTime == 'undefined') {

                            $('.delivery-items-options').find('.delivery-opts').html('<span class="delMethod">' + serviceLabel + ',</span> <span class="delTime"> ' + Cookies.get('delivery_date') + '</span>');
                        }

                        window.location.reload();
                    }

                    //Trigger checked slot event so that it can be used by theme/plugins
                    $(document.body).trigger('pl8app_checked_slots', [response]);

                    //If it's checkout page then refresh the page to reflect the updated changes.
                    if (pl8app_scripts.is_checkout == '1')
                        window.location.reload();
                }
            }
        });
    });

    // Update Service Date and Time
    $(document).on('click', '.delivery-change', function (e) {

        e.preventDefault();

        var self = $(this);
        var action = 'pl8app_show_delivery_options';
        var ServiceType = pla_getCookie('service_type');
        var ServiceTime = pla_getCookie('service_time');
        var text = self.text();
        self.text(pla_scripts.please_wait);

        var data = {
            action: action,
            security: pla_scripts.service_type_nonce
        }

        $('#pl8appModal').addClass('show-service-options');

        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: pla_scripts.ajaxurl,
            success: function (response) {

                self.text(text);
                $('#pl8appModal .modal-title').html(response.data.html_title);
                $('#pl8appModal .modal-body').html(response.data.html);
                if ($('.pl8app-tabs-wrapper').length) {
                    $('#pl8appdeliveryTab > li:first-child > a').length > 0?$('#pl8appdeliveryTab > li:first-child > a')[0].click():'';
                }
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
        })
    });

    // Remove Item from Cart
    $('.pl8app-cart').on('click', '.pl8app-remove-from-cart', function (event) {

        var $this = $(this),
            item = $this.data('cart-item'),
            action = $this.data('action'),
            id = $this.data('menuitem-id'),
            data = {
                action: action,
                cart_item: item
            };

        $.ajax({

            type: "POST",
            data: data,
            dataType: "json",
            url: pl8app_scripts.ajaxurl,
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {

                if (response.removed) {

                    // Remove the $this cart item
                    $('.pl8app-cart .pl8app-cart-item').each(function () {
                        $(this).find("[data-cart-item='" + item + "']").parents('.pl8app-cart-item').remove();
                    });

                    // Check to see if the purchase form(s) for this menuitem is present on this page
                    if ($('[id^=pl8app_purchase_' + id + ']').length) {
                        $('[id^=pl8app_purchase_' + id + '] .pl8app_go_to_checkout').hide();
                        $('[id^=pl8app_purchase_' + id + '] a.pl8app-add-to-cart').show().removeAttr('data-pl8app-loading');

                        if (pl8app_scripts.quantities_enabled == '1') {
                            $('[id^=pl8app_purchase_' + id + '] .pl8app_menuitem_quantity_wrapper').show();
                        }
                    }

                    $('span.pl8app-cart-quantity').text(response.cart_quantity);

                    $(document.body).trigger('pl8app_quantity_updated', [response.cart_quantity]);

                    if (pl8app_scripts.taxes_enabled) {
                        $('.cart_item.pl8app_subtotal span').html(response.subtotal);
                        $('.cart_item.pl8app_cart_tax').html(response.taxes);
                    }

                    $('.cart_item.pl8app_total span.pl8app-cart-quantity').html(response.cart_quantity);
                    $('.cart_item.pl8app_total span.cart-total').html(response.total);

                    if (response.cart_quantity == 0) {

                        $('.cart_item.pl8app_subtotal,.pl8app-cart-number-of-items,.cart_item.pl8app_checkout,.cart_item.pl8app_cart_tax,.cart_item.pl8app_total').hide();
                        $('.pl8app-cart').each(function () {

                            var cart_wrapper = $(this).parent();

                            if (cart_wrapper) {
                                cart_wrapper.addClass('cart-empty')
                                cart_wrapper.removeClass('cart-not-empty');
                            }

                            $(this).append('<li class="cart_item empty">' + pl8app_scripts.empty_cart_message + '</li>');
                        });
                    }

                    $(document.body).trigger('pl8app_cart_item_removed', [response]);

                    $('ul.pl8app-cart > li.pl8app-cart-item').each(function (index, item) {
                        $(this).find("[data-cart-item]").attr('data-cart-item', index);
                        $(this).find("[data-remove-item]").attr('data-remove-item', index);
                        $(this).attr('data-cart-key', index);
                    });

                    // check if no item in cart left
                    if ($('li.pl8app-cart-item').length == 0) {
                        $('a.pl8app-clear-cart').trigger('click');
                        $('li.delivery-items-options').hide();
                        $('a.pl8app-clear-cart').hide();
                    }
                }
            }
        });

        return false;
    });

    // Clear All Menuitems from Cart
    $(document).on('click', 'a.pl8app-clear-cart', function (e) {

        e.preventDefault();

        if (confirm(pla_scripts.confirm_empty_cart)) {

            var self = $(this);
            var old_text = self.html();
            var action = 'pl8app_clear_cart';
            var data = {
                security: pla_scripts.clear_cart_nonce,
                action: action
            }

            self.text(pla_scripts.please_wait);

            $.ajax({
                type: "POST",
                data: data,
                dataType: "json",
                url: pla_scripts.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {
                    if (response.status == 'success') {
                        $('ul.pl8app-cart').find('li.cart_item.pl8app_total').css('display', 'none');
                        $('ul.pl8app-cart').find('li.cart_item.pl8app_checkout').css('display', 'none');
                        $('ul.pl8app-cart').find('li.pl8app-cart-item').remove();
                        $('ul.pl8app-cart').find('li.cart_item.empty').remove();
                        $('ul.pl8app-cart').find('li.pl8app_subtotal').remove();
                        $('ul.pl8app-cart').find('li.pl8app_cart_tax').remove();
                        $('ul.pl8app-cart').find('li.pl8app-delivery-fee').remove();
                        $('.delivery-items-options').remove();
                        $('ul.pl8app-cart').append(response.response);
                        $('.pl8app-cart-number-of-items').css('display', 'none');
                        self.html(old_text);
                        self.hide();
                    }
                }
            });
        }
    });

    // Proceed to Checkout
    $(document).on('click', '.cart_item.pl8app_checkout a', function (e) {
        e.preventDefault();

        var CheckoutUrl = pla_scripts.checkout_page;
        var _self = $(this);
        var OrderText = _self.text();

        var action = 'pl8app_proceed_checkout';
        var data = {
            action: action,
            security: pla_scripts.proceed_checkout_nonce,
        }

        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: pla_scripts.ajaxurl,
            beforeSend: function () {
                _self.text(pla_scripts.please_wait);
            },
            success: function (response) {
                if (response.status == 'error') {
                    if (response.error_msg) {
                        errorString = response.error_msg;
                    }
                    $('#pl8appModal').addClass('checkout-error');
                    $('#pl8appModal').find('.modal-title').html(pla_scripts.error);
                    $('#pl8appModal .modal-body').html(errorString);

                    MicroModal.show('pl8appModal');
                    _self.text(OrderText);
                } else {
                    window.location.replace(pla_scripts.checkout_page);
                }
            }
        })
    });

    $(document).on('click', 'a.special-instructions-link', function (e) {
        e.preventDefault();
        $(this).parent('div').find('.special-instructions').toggleClass('hide');
    });

    $('body').on('click', '.pl8app-filter-toggle', function () {
        $('div.pl8app-filter-wrapper').toggleClass('active');
    });

    // Show hide cutlery icon on smaller devices
    $(".pl8app-mobile-cart-icons").click(function () {
        $(".pl8app-sidebar-main-wrap").css("left", "0%");
    });

    $(".close-cart-ic").click(function () {
        $(".pl8app-sidebar-main-wrap").css("left", "100%");
    });

    // Show Image on Modal
    $(".pl8app-thumbnail-popup").fancybox({

        openEffect: 'elastic',
        closeEffect: 'elastic',

        helpers: {
            title: {
                type: 'inside'
            }
        }
    });

    if ($(window).width() > 991) {
        var totalHeight = $('header:eq(0)').length > 0 ? $('header:eq(0)').height() + 30 : 120;
        if ($(".sticky-sidebar").length != '') {
            $('.sticky-sidebar').pl8appStickySidebar({
                additionalMarginTop: totalHeight
            });
        }
    } else {
        var totalHeight = $('header:eq(0)').length > 0 ? $('header:eq(0)').height() + 30 : 70;
    }

    var pla_get_delivery_data = pla_get_storage_data();
    if (!pla_get_delivery_data) {
        $('a.delivery-change').trigger('click');
    }

    //Store Map information
    if($('#pl8app-store-osgmap-container').length > 0) {
        var lat = pla_scripts.store_latitude;
        var lon = pla_scripts.store_longitude;

        var map;
        var store_logo;
        if (lat !== '' && lon !== '') {

            map = L.map('pl8app-store-osgmap-container', {
                center: [lat, lon],
                zoom: 20
            });
            if (pla_scripts.store_logo) {
                store_logo = pla_scripts.store_logo;
            }
            var firefoxIcon = L.icon({
                iconUrl: store_logo,
                iconSize: [40, 40], // size of the icon
                popupAnchor: [0, -15]
            });

            var customPopup = pla_scripts.osm_popup_content;

            var marker = L.marker([lat, lon], {icon: firefoxIcon}).bindPopup(customPopup).addTo(map);

        }
        else {
            map = L.map('pl8app-store-osgmap-container', {
                center: [53.958332, -1.080278],
                zoom: 15
            });
        }

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    }
});

/* Make Addons and Variables clickable for Live Price */
jQuery(document).ajaxComplete(function () {

    jQuery('#menuitem-details .menu-item-list input').on('click', function (event) {
        update_modal_live_price('menuitem-details');
    });

    jQuery('.bundled-menu-item-content .menu-item-list input').on('click', function (event) {
        update_modal_live_price('menuitem-details');
    });

    jQuery('#menuitem-update-details .menu-item-list input').on('click', function (event) {
        update_modal_live_price('menuitem-update-details');
    });
});

/* pl8app Sticky Sidebar - Imported from pl8app-sticky-sidebar.js */
jQuery(function ($) {

    if ($(window).width() > 991) {
        var totalHeight = $('header:eq(0)').length > 0 ? $('header:eq(0)').height() + 30 : 120;
        if ($(".sticky-sidebar").length > 0) {
            $('.sticky-sidebar').pl8appStickySidebar({
                additionalMarginTop: totalHeight
            });
        }
    } else {
        var totalHeight = $('header:eq(0)').length > 0 ? $('header:eq(0)').height() + 30 : 70;
    }

    // Category Navigation
    $('body').on('click', '.pl8app-category-link', function (e) {
        e.preventDefault();
        var this_id = $(this).data('id');
        var gotom = setInterval(function () {
            pl8app_go_to_navtab(this_id);
            clearInterval(gotom);
        }, 100);
    });

    function pl8app_go_to_navtab(id) {
        var scrolling_div = jQuery('div.pl8app_menuitems_list').find('div#menu-category-' + id);
        if (scrolling_div.length) {
            offSet = scrolling_div.offset().top;

            var body = jQuery("html, body");

            body.animate({
                scrollTop: offSet - totalHeight
            }, 500);
        }
    }

    $('.pl8app-category-item').on('click', function () {
        $('.pl8app-category-item').removeClass('current');
        $(this).addClass('current');
    });
});

/* Cart Quantity Changer - Imported from cart-quantity-changer.js */
jQuery(function ($) {

    //quantity Minus
    var liveQtyVal;

    jQuery(document).on('click', '.qtyminus', function (e) {

        // Stop acting like a button
        e.preventDefault();

        // Get the field name
        fieldName = 'quantity';

        // Get its current value
        var currentVal = parseInt(jQuery('input[name=' + fieldName + ']').val());

        // If it isn't undefined or its greater than 0
        if (!isNaN(currentVal) && currentVal > 1) {

            // Decrement one only if value is > 1
            jQuery('input[name=' + fieldName + ']').val(currentVal - 1);
            jQuery('.qtyplus').removeAttr('style');
            liveQtyVal = currentVal - 1;

        } else {

            // Otherwise put a 0 there
            jQuery('input[name=' + fieldName + ']').val(1);
            jQuery('.qtyminus').css('color', '#aaa').css('cursor', 'not-allowed');
            liveQtyVal = 1;
        }

        jQuery(this).parents('footer.modal-footer').find('a.submit-menuitem-button').attr('data-item-qty', liveQtyVal);
        jQuery(this).parents('footer.modal-footer').find('a.submit-menuitem-button').attr('data-item-qty', liveQtyVal);

        // Updating live price as per quantity
        var total_price = parseFloat(jQuery('#pl8appModal .cart-item-price').attr('data-current'));
        var new_price = parseFloat(total_price * liveQtyVal);
        jQuery('#pl8appModal .cart-item-price').html(pla_scripts.currency_sign + new_price.toFixed(2));
    });

    jQuery(document).on('click', '.qtyplus', function (e) {

        // Stop acting like a button
        e.preventDefault();

        // Get the field name
        fieldName = 'quantity';

        // Get its current value
        var currentVal = parseInt(jQuery('input[name=' + fieldName + ']').val());
        // If is not undefined
        if (!isNaN(currentVal)) {
            jQuery('input[name=' + fieldName + ']').val(currentVal + 1);
            jQuery('.qtyminus').removeAttr('style');
            liveQtyVal = currentVal + 1;
        } else {
            // Otherwise put a 0 there
            jQuery('input[name=' + fieldName + ']').val(1);
            liveQtyVal = 1;
        }

        jQuery(this).parents('footer.modal-footer').find('a.submit-menuitem-button').attr('data-item-qty', liveQtyVal);
        jQuery(this).parents('footer.modal-footer').find('a.submit-menuitem-button').attr('data-item-qty', liveQtyVal);

        // Updating live price as per quantity
        var total_price = parseFloat(jQuery('#pl8appModal .cart-item-price').attr('data-current'));
        var new_price = parseFloat(total_price * liveQtyVal);
        jQuery('#pl8appModal .cart-item-price').html(pla_scripts.currency_sign + new_price.toFixed(2));
    });

    jQuery(document).on("input", ".qty", function () {
        this.value = this.value.replace(/\D/g, '');
    });

    jQuery(document).on('keyup', '.qty', function (e) {
        // Updating live price as per quantity
        liveQtyVal = jQuery(this).val();
        var total_price = parseFloat(jQuery('#pl8appModal .cart-item-price').attr('data-current'));
        var new_price = parseFloat(total_price * liveQtyVal);
        jQuery('#pl8appModal .cart-item-price').html(pla_scripts.currency_sign + new_price.toFixed(2));
    });
});

/* pl8app Live Search - Imported from live-search.js */
jQuery(function ($) {

    $('.pl8app_menuitems_list').find('.pl8app-title-holder').each(function () {
        $(this).attr('data-search-term', $(this).text().toLowerCase());
    });

    $('#pl8app-menu-search').on('keyup', function () {
        var searchTerm = $(this).val().toLowerCase();
        var DataId;
        var SelectedTermId;

        $('.pl8app_menuitems_list').find('.pl8app-element-title').each(function (index, elem) {
            $(this).removeClass('not-matched');
            $(this).removeClass('matched');
        });

        $('.pl8app_menuitems_list').find('.pl8app-title-holder').each(function () {
            DataId = $(this).parents('.pl8app_menuitem').attr('data-term-id');

            if ((searchTerm != '' && $(this).filter('[data-search-term *= ' + searchTerm + ']').length > 0) || searchTerm.length < 1) {
                $(this).parents('.pl8app_menuitem').show();
                $('.pl8app_menuitems_list').find('.pl8app-element-title').each(function (index, elem) {
                    if ($(this).attr('data-term-id') == DataId) {
                        $(this).addClass('matched');
                    } else {
                        $(this).addClass('not-matched');
                    }
                });
            } else {
                $(this).parents('.pl8app_menuitem').hide();
                $('.pl8app_menuitems_list').find('.pl8app-element-title').each(function (index, elem) {
                    $(this).addClass('not-matched');
                });
            }
        });
    });
})

/* pl8app active category highlighter */
jQuery(function ($) {
    const pla_category_links = $('.pl8app-category-lists .pl8app-category-link');
    if (pla_category_links.length > 0) {
        const header_height = $('header:eq(0)').height();
        let current_category = pla_category_links.eq('0').attr('href').substr(1);

        function RpScrollingCategories() {
            pla_category_links.each(function () {
                const section_id = $(this).attr('href').substr(1);
                const section = document.querySelector(`.menu-category-wrap[data-cat-id="${section_id}"]`);

                if (section && section.getBoundingClientRect().top < header_height + 40) {
                    current_category = section_id;
                }

                $('.pl8app-category-lists .pl8app-category-link').removeClass('active');
                $(`.pl8app-category-lists .pl8app-category-link[href="#${ current_category }"]`).addClass('active');

            });
        }

        window.onscroll = function () {
            RpScrollingCategories();
        }
    }
});
