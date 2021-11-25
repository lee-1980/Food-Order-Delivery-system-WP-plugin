jQuery(function ($) {

    //show the toast message
    function pl8app_toast(heading, message, type) {
        $.toast({
            heading: heading,
            text: message,
            showHideTransition: 'slide',
            icon: type,
            position: {top: '36px', right: '0px'},
            stack: false
        });
    }

    $(document).on('click', '.pl8app-service-time span', function (e) {
        $(this).parents('.pl8app-service-time').find('input').trigger('click');
        e.preventDefault();
        $('.service_available_hrs').timepicker({
            dropdown: true,
            scrollbar: true,
        });
    });

    $('select.addon-items-list').chosen();

    $('select.addon-items-list').on('change', function (event, params) {
        if (event.type == 'change') {
            $('.pl8app-order-payment-recalc-totals').show();
        }
    });

    $('input.pl8app_timings').timepicker({
        dropdown: true,
        scrollbar: true,
    });

    //Validate License
    $('body').on('click', '.pl8app-validate-license', function (e) {
        e.preventDefault();
        var _self = $(this);

        $('.pl8app-license-wrapper').find('.pl8app-license-field').removeClass('empty-license-key');

        var ButtonText = _self.text();
        var Selected = _self.parent('.pl8app-license-wrapper').find('.pl8app-license-field')
        var ItemId = Selected.attr('data-item-id');
        var ProductName = Selected.attr('data-item-name');
        var License = Selected.val();
        var LicenseString = _self.parent('.pl8app-license-wrapper').find('.pl8app_license_string').val();
        var action = _self.attr('data-action');

        if (License.length) {
            _self.addClass('disabled');
            _self.text(pl8app_vars.please_wait);

            data = {
                action: action,
                item_id: ItemId,
                product_name: ProductName,
                license: License,
                license_key: LicenseString,
            };

            $.ajax({
                type: "POST",
                data: data,
                dataType: "json",
                url: pl8app_vars.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {
                    if (response.status !== 'error') {
                        pl8app_toast(pl8app_vars.success, pl8app_vars.license_success, 'success');
                        _self.parent('.pl8app-license-wrapper').addClass('pl8app-updated');
                        _self.parents('.pl8app-purchased-wrap').find('.pl8app-license-deactivate-wrapper').removeClass('hide').addClass('show');
                    }
                    else {
                        pl8app_toast(pl8app_vars.error, response.message, 'error');
                    }
                    _self.text(pl8app_vars.license_activate);
                    _self.removeClass('disabled');
                }
            })
        }
        else {
            $(this).parents('.pl8app-license-wrapper').find('.pl8app-license-field').addClass('empty-license-key');
            pl8app_toast(pl8app_vars.error, pl8app_vars.empty_license, 'error');
        }
    });

    //Deactivate License
    $('body').on('click', '.pl8app-deactivate-license', function (e) {
        e.preventDefault();
        var _self = $(this);
        var action = $(this).attr('data-action');
        var Licensestring = $(this).parents('.pl8app-purchased-wrap').find('.pl8app_license_string').val();
        var ProductName = $(this).parents('.pl8app-purchased-wrap').find('.pl8app-license-field').attr('data-item-name');

        _self.addClass('disabled');
        _self.text(pl8app_vars.please_wait);

        if (Licensestring.length) {
            data = {
                action: action,
                product_name: ProductName,
                license_key: Licensestring,
            };

            $.ajax({
                type: "POST",
                data: data,
                dataType: "json",
                url: pl8app_vars.ajaxurl,
                xhrFields: {
                    withCredentials: true
                },
                success: function (response) {
                    pl8app_toast(pl8app_vars.information, pl8app_vars.license_deactivated, 'info');
                    if (response.status !== 'error') {
                        _self.parents('.pl8app-purchased-wrap').find('.pl8app-license-wrapper').removeClass('pl8app-updated');
                        _self.parents('.pl8app-purchased-wrap').find('.pl8app-license-deactivate-wrapper').removeClass('show').addClass('hide');
                    }
                    _self.text(pl8app_vars.deactivate_license);
                    _self.removeClass('disabled');
                }
            })
        }
    });

});


jQuery(document).ready(function ($) {
    //Emergency stop
    var $info = $("#pl8app-emergency-id");
    $info.dialog({
        title: 'Emergency Stop',
        dialogClass: 'wp-dialog',
        autoOpen: false,
        draggable: false,
        width: 'auto',
        modal: true,
        resizable: false,
        closeOnEscape: true,
        position: {
            my: "center",
            at: "center",
            of: window
        },
        open: function () {// close dialog by clicking the overlay behind it
            $('.ui-widget-overlay').bind('click', function(){
                $('#pl8app-emergency-id').dialog('close');
            })
            $('.ui-widget-content').css({'z-index': 2});
            $('.ui-widget-overlay').css({'z-index': 1});
            $('.ui-widget-overlay').css({'position': 'fixed'});
            $('.ui-dialog .ui-dialog-titlebar-close').css({'width' : '40px'});
            $('.ui-button-icon').remove();
        },

    });
    // bind a button or a link to open the dialog
    $(document.body).on('click', 'input.emergency_modal', function(e) {
        e.preventDefault();
        var _self = this;
        if (this.checked) {
            $info.dialog('open');
        }
        else{
            var screenoptionnonce = $('input#screenoptionnonce').val();
            var data = {
                action: 'pl8app_emergency_stop_disable',
                screenoptionnonce: screenoptionnonce
            };
            $.post(ajaxurl, data, function (response) {
               $(_self).prop('checked', false);
            });
        }
    });

    $('a.emergency_stop').click(function (e) {
        e.preventDefault();
        var screenoptionnonce = $('input#screenoptionnonce').val();
        var data = {
            action: 'pl8app_emergency_stop',
            screenoptionnonce: screenoptionnonce
        };
        $.post(ajaxurl, data, function (response) {
            if(response){
                $info.dialog('close');
                $('input.emergency_modal').prop('checked', true);
            }
        });
    });

    //Visual Representation

    $('.pl8app-service-date').datepicker({
        dateFormat: 'MM d, yy',
        changeYear: true,
        changeMonth: true,
        onSelect: function(dateText) {
            render_order_visual_widget(dateText);
        }
    });

    function render_order_visual_widget(date) {

        if(date == '' || !$('input#screenoptionnonce').length || !$('#pl8app_dashboard_visualization').length) return;

        var screenoptionnonce = $('input#screenoptionnonce').val();

        var data = {
            action: 'pl8app_order_visual_widget_render',
            screenoptionnonce: screenoptionnonce,
            service_date: date
        };
        $.post(ajaxurl, data, function (response) {
            if(response) {
                var res = JSON.parse(response);
                if(res && res.hasOwnProperty('data')){
                    $('.slot-chart-content').html(res.data);
                }
            }
        });
    }

    render_order_visual_widget($('.pl8app-service-date').val());

    //Store Location

    if($('#pl8app-store-location-map').length){

        var lat = $('input[name="pl8app_settings[pl8app_st_latitude]"]').val();
        var lon = $('input[name="pl8app_settings[pl8app_st_longitude]"]').val();

        var map;
        var store_logo;
        var marker;
        var firefoxIcon;
        if(!lat || !lon){
            lat = 53.958332;
            lon = -1.080278;
        }

        map = L.map('pl8app-store-location-map',{
            center: [lat, lon],
            zoom: 20
        });
        if(pl8app_vars.store_logo){
            store_logo = pl8app_vars.store_logo;
        }
        firefoxIcon = L.icon({
            iconUrl: store_logo,
            iconSize: [40, 40], // size of the icon
        });

        marker = L.marker([lat, lon], {icon: firefoxIcon}).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        get_geolocation_from_osm();

        $(document.body).on('input','input[name="pl8app_settings[pl8app_street_address]"], ' +
            'input[name="pl8app_settings[pl8app_town]"], ' +
            'input[name="pl8app_settings[pl8app_pz_code]"]', function(){
            get_geolocation_from_osm();
        });

        $(document.body).on('change','select[name="pl8app_settings[base_state]"], ' +
            'select[name="pl8app_settings[base_country]"]', function(){
            get_geolocation_from_osm();
        });

        function get_geolocation_from_osm(){
            var street = $('input[name="pl8app_settings[pl8app_street_address]"]').val();
            var town = $('input[name="pl8app_settings[pl8app_town]"]').val();
            var state = $('select[name="pl8app_settings[base_state]"] option:selected').text();
            var country = $('select[name="pl8app_settings[base_country]"] option:selected').text();
            var code = $('input[name="pl8app_settings[pl8app_pz_code]"]').val();

            var address = (street !== '' && street !== undefined? street + ', ':'') +
                (town !== '' && town !== undefined? town + ', ':'') +
                (state !== '' && state !== undefined? state + ', ':'') +
                (code !== '' && code !== undefined? code + ', ':'') +
                (country !== '' && country !== undefined? country :'');
            if(address !== '' && address !== undefined){
                $.get("https://nominatim.openstreetmap.org/?q=" + address + "&format=json", function(data){
                    map.removeLayer(marker);
                    if(Array.isArray(data) && data.length > 0){
                        !$('.pl8app-osm-autodetect').hasClass('pl8app-hidden')?$('.pl8app-osm-autodetect').addClass('pl8app-hidden'):'';
                        $('.pl8app-osm-autodetect-text').text(data[0].display_name);
                        $('.pl8app-osm-autodetect-text').removeClass('pl8app-hidden');
                        $('input[name="pl8app_settings[pl8app_st_latitude]"]').val(data[0].lat);
                        $('input[name="pl8app_settings[pl8app_st_longitude]"]').val(data[0].lon);
                        marker = L.marker([data[0].lat, data[0].lon], {icon: firefoxIcon}).addTo(map);
                        map.setView([data[0].lat, data[0].lon], 20);
                    }
                    else{
                        $('.pl8app-osm-autodetect').removeClass('pl8app-hidden');
                        $('.pl8app-osm-autodetect-text').addClass('pl8app-hidden');
                        $('input[name="pl8app_settings[pl8app_st_latitude]"]').val('');
                        $('input[name="pl8app_settings[pl8app_st_longitude]"]').val('');
                    }
                });
            }
        }

    }

    //Appearance Social links for top bar right

    $(document.body).on('click', 'input:checkbox[name="pl8app_settings[pl8app_socials]"]', function () {
        if (!this.checked) {
            $('.container-social-lists ').addClass('hidden');
        }
        else{
            $('.container-social-lists ').removeClass('hidden');
        }
    });

    //Appearance Social links for top bar right

    $(document.body).on('click', 'input:checkbox[name="pl8app_settings[enable_taxes]"]', function () {
        if (!this.checked) {
            $('.tax_row').addClass('hidden');
        }
        else{
            $('.tax_row').removeClass('hidden');
        }
    });

    /**
     * Choose service
     */
    $(document.body).on('click', 'input:checkbox[name="pl8app_settings[enable_service][no_service]"]', function () {
        if (this.checked) {
            $('input:checkbox[name="pl8app_settings[enable_service][delivery]"]').prop('checked', false);
            $('input:checkbox[name="pl8app_settings[enable_service][pickup]"]').prop('checked', false);
        }
    });

    $(document.body).on('click', 'input:checkbox[name="pl8app_settings[enable_service][delivery]"], input:checkbox[name="pl8app_settings[enable_service][pickup]"]', function () {
        if (this.checked) {
            $('input:checkbox[name="pl8app_settings[enable_service][no_service]"]').prop('checked', false);
        }
    });


    // Tooltips
    var tooltips = $('.pl8app-help-tip');
    pl8app_attach_tooltips(tooltips);

    /**
     * pl8app Configuration Metabox
     */
    var pl8app_pl8app_Configuration = {
        init: function () {
            this.add();
            this.move();
            this.remove();
            this.type();
            this.prices();
            this.files();
            this.updatePrices();
        },
        clone_repeatable: function (row) {

            // Retrieve the highest current key
            var key = highest = 1;
            row.parent().find('.pl8app_repeatable_row').each(function () {
                var current = $(this).data('key');
                if (parseInt(current) > highest) {
                    highest = current;
                }
            });
            key = highest += 1;

            clone = row.clone();

            clone.removeClass('pl8app_add_blank');

            clone.attr('data-key', key);
            clone.find('input, select, textarea').val('').each(function () {
                var name = $(this).attr('name');
                var id = $(this).attr('id');

                if (name) {

                    name = name.replace(/\[(\d+)\]/, '[' + parseInt(key) + ']');
                    $(this).attr('name', name);

                }

                $(this).attr('data-key', key);

                if (typeof id != 'undefined') {

                    id = id.replace(/(\d+)/, parseInt(key));
                    $(this).attr('id', id);

                }

            });

            /** manually update any select box values */
            clone.find('select').each(function () {
                $(this).val(row.find('select[name="' + $(this).attr('name') + '"]').val());
            });

            /** manually uncheck any checkboxes */
            clone.find('input[type="checkbox"]').each(function () {

                // Make sure checkboxes are unchecked when cloned
                var checked = $(this).is(':checked');
                if (checked) {
                    $(this).prop('checked', false);
                }

                // reset the value attribute to 1 in order to properly save the new checked state
                $(this).val(1);
            });

            clone.find('span.pl8app_price_id').each(function () {
                $(this).text(parseInt(key));
            });

            clone.find('span.pl8app_file_id').each(function () {
                $(this).text(parseInt(key));
            });

            clone.find('.pl8app_repeatable_default_input').each(function () {
                $(this).val(parseInt(key)).removeAttr('checked');
            });

            clone.find('.pl8app_repeatable_condition_field').each(function () {
                $(this).find('option:eq(0)').prop('selected', 'selected');
            });

            // Remove Chosen elements
            clone.find('.search-choice').remove();
            clone.find('.chosen-container').remove();
            pl8app_attach_tooltips(clone.find('.pl8app-help-tip'));

            return clone;
        },

        add: function () {
            $(document.body).on('click', '.submit .pl8app_add_repeatable', function (e) {
                e.preventDefault();
                var button = $(this),
                    row = button.parent().parent().prev('.pl8app_repeatable_row'),
                    clone = pl8app_pl8app_Configuration.clone_repeatable(row);

                clone.insertAfter(row).find('input, textarea, select').filter(':visible').eq(0).focus();

                // Setup chosen fields again if they exist
                clone.find('.pl8app-select-chosen').chosen({
                    inherit_select_classes: true,
                    placeholder_text_single: pl8app_vars.one_option,
                    placeholder_text_multiple: pl8app_vars.one_or_more_option,
                });
                clone.find('.pl8app-select-chosen').css('width', '100%');
                clone.find('.pl8app-select-chosen .chosen-search input').attr('placeholder', pl8app_vars.search_placeholder);
            });
        },

        move: function () {

            $(".pl8app_repeatable_table .pl8app-repeatables-wrap").sortable({
                handle: '.pl8app-draghandle-anchor',
                items: '.pl8app_repeatable_row',
                opacity: 0.6,
                cursor: 'move',
                axis: 'y',
                update: function () {
                    var count = 0;
                    $(this).find('.pl8app_repeatable_row').each(function () {
                        $(this).find('input.pl8app_repeatable_index').each(function () {
                            $(this).val(count);
                        });
                        count++;
                    });
                }
            });

        },

        remove: function () {
            $(document.body).on('click', '.pl8app-remove-row, .pl8app_remove_repeatable', function (e) {
                e.preventDefault();

                var row = $(this).parents('.pl8app_repeatable_row'),
                    count = row.parent().find('.pl8app_repeatable_row').length,
                    type = $(this).data('type'),
                    repeatable = 'div.pl8app_repeatable_' + type + 's',
                    focusElement,
                    focusable,
                    firstFocusable;

                // Set focus on next element if removing the first row. Otherwise set focus on previous element.
                if ($(this).is('.ui-sortable .pl8app_repeatable_row:first-child .pl8app-remove-row, .ui-sortable .pl8app_repeatable_row:first-child .pl8app_remove_repeatable')) {
                    focusElement = row.next('.pl8app_repeatable_row');
                } else {
                    focusElement = row.prev('.pl8app_repeatable_row');
                }

                focusable = focusElement.find('select, input, textarea, button').filter(':visible');
                firstFocusable = focusable.eq(0);

                if (type === 'price') {
                    var price_row_id = row.data('key');
                    /** remove from price condition */
                    $('.pl8app_repeatable_condition_field option[value="' + price_row_id + '"]').remove();
                }

                if (count > 1) {
                    $('input, select', row).val('');
                    row.fadeOut('fast').remove();
                    firstFocusable.focus();
                } else {
                    switch (type) {
                        case 'price' :
                            alert(pl8app_vars.one_price_min);
                            break;
                        case 'file' :
                            $('input, select', row).val('');
                            break;
                        default:
                            alert(pl8app_vars.one_field_min);
                            break;
                    }
                }

                /* re-index after deleting */
                $(repeatable).each(function (rowIndex) {
                    $(this).find('input, select').each(function () {
                        var name = $(this).attr('name');
                        name = name.replace(/\[(\d+)\]/, '[' + rowIndex + ']');
                        $(this).attr('name', name).attr('id', name);
                    });
                });
            });
        },

        type: function () {

            $(document.body).on('change', '#_pl8app_product_type', function (e) {

                var pl8app_products = $('#pl8app_products'),
                    pl8app_menuitem_files = $('#pl8app_menuitem_files'),
                    pl8app_menuitem_limit_wrap = $('#pl8app_menuitem_limit_wrap');

                if ('bundle' === $(this).val()) {
                    pl8app_products.show();
                    pl8app_menuitem_files.hide();
                    pl8app_menuitem_limit_wrap.hide();
                } else {
                    pl8app_products.hide();
                    pl8app_menuitem_files.show();
                    pl8app_menuitem_limit_wrap.show();
                }

            });

        },

        prices: function () {
            $(document.body).on('change', '#pl8app_variable_pricing', function (e) {
                var checked = $(this).is(':checked');
                var single = $('#pl8app_regular_price_field');
                var variable = $('#pl8app_variable_price_fields, .pl8app_repeatable_table .pricing');
                var bundleRow = $('.pl8app-bundled-product-row, .pl8app-repeatable-row-standard-fields');
                if (checked) {
                    single.hide();
                    variable.show();
                    bundleRow.addClass('has-variable-pricing');
                } else {
                    single.show();
                    variable.hide();
                    bundleRow.removeClass('has-variable-pricing');
                }
            });
        },

        files: function () {
            var file_frame;
            window.formfield = '';

            $(document.body).on('click', '.pl8app_upload_file_button', function (e) {

                e.preventDefault();

                var button = $(this);

                window.formfield = $(this).closest('.pl8app_repeatable_upload_wrapper');

                // If the media frame already exists, reopen it.
                if (file_frame) {
                    //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                    file_frame.open();
                    return;
                }

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    frame: 'post',
                    state: 'insert',
                    title: button.data('uploader-title'),
                    button: {
                        text: button.data('uploader-button-text')
                    },
                    multiple: $(this).data('multiple') == '0' ? false : true  // Set to true to allow multiple files to be selected
                });

                file_frame.on('menu:render:default', function (view) {
                    // Store our views in an object.
                    var views = {};

                    // Unset default menu items
                    view.unset('library-separator');
                    view.unset('gallery');
                    view.unset('featured-image');
                    view.unset('embed');

                    // Initialize the views in our view object.
                    view.set(views);
                });

                // When an image is selected, run a callback.
                file_frame.on('insert', function () {

                    var selection = file_frame.state().get('selection');
                    selection.each(function (attachment, index) {
                        attachment = attachment.toJSON();

                        var selectedSize = 'image' === attachment.type ? $('.attachment-display-settings .size option:selected').val() : false;
                        var selectedURL = attachment.url;
                        var selectedName = attachment.title.length > 0 ? attachment.title : attachment.filename;

                        if (selectedSize && typeof attachment.sizes[selectedSize] != "undefined") {
                            selectedURL = attachment.sizes[selectedSize].url;
                        }

                        if ('image' === attachment.type) {
                            if (selectedSize && typeof attachment.sizes[selectedSize] != "undefined") {
                                selectedName = selectedName + '-' + attachment.sizes[selectedSize].width + 'x' + attachment.sizes[selectedSize].height;
                            } else {
                                selectedName = selectedName + '-' + attachment.width + 'x' + attachment.height;
                            }
                        }

                        if (0 === index) {
                            // place first attachment in field
                            window.formfield.find('.pl8app_repeatable_attachment_id_field').val(attachment.id);
                            window.formfield.find('.pl8app_repeatable_thumbnail_size_field').val(selectedSize);
                            window.formfield.find('.pl8app_repeatable_upload_field').val(selectedURL);
                            window.formfield.find('.pl8app_repeatable_name_field').val(selectedName);
                        } else {
                            // Create a new row for all additional attachments
                            var row = window.formfield,
                                clone = pl8app_pl8app_Configuration.clone_repeatable(row);

                            clone.find('.pl8app_repeatable_attachment_id_field').val(attachment.id);
                            clone.find('.pl8app_repeatable_thumbnail_size_field').val(selectedSize);
                            clone.find('.pl8app_repeatable_upload_field').val(selectedURL);
                            clone.find('.pl8app_repeatable_name_field').val(selectedName);
                            clone.insertAfter(row);
                        }
                    });
                });

                // Finally, open the modal
                file_frame.open();
            });


            var file_frame;
            window.formfield = '';

        },

        updatePrices: function () {
            $('#pl8app_price_fields').on('keyup', '.pl8app_variable_prices_name', function () {

                var key = $(this).parents('.pl8app_repeatable_row').data('key'),
                    name = $(this).val(),
                    field_option = $('.pl8app_repeatable_condition_field option[value=' + key + ']');

                if (field_option.length > 0) {
                    field_option.text(name);
                } else {
                    $('.pl8app_repeatable_condition_field').append(
                        $('<option></option>')
                            .attr('value', key)
                            .text(name)
                    );
                }
            });
        }

    };

    // Toggle display of entire custom settings section for a price option
    $(document.body).on('click', '.toggle-custom-price-option-section', function (e) {
        e.preventDefault();
        var show = $(this).html() == pl8app_vars.show_advanced_settings ? true : false;

        if (show) {
            $(this).html(pl8app_vars.hide_advanced_settings);
        } else {
            $(this).html(pl8app_vars.show_advanced_settings);
        }

        var header = $(this).parents('.pl8app-repeatable-row-header');
        header.siblings('.pl8app-custom-price-option-sections-wrap').slideToggle();

        var first_input;
        if (show) {
            first_input = $(":input:not(input[type=button],input[type=submit],button):visible:first", header.siblings('.pl8app-custom-price-option-sections-wrap'));
        } else {
            first_input = $(":input:not(input[type=button],input[type=submit],button):visible:first", header.siblings('.pl8app-repeatable-row-standard-fields'));
        }
        first_input.focus();
    });

    pl8app_pl8app_Configuration.init();

    // Date picker
    var pl8app_datepicker = $('.pl8app_datepicker');
    if (pl8app_datepicker.length > 0) {
        var dateFormat = 'mm/dd/yy';
        pl8app_datepicker.datepicker({
            dateFormat: dateFormat
        });
    }

    /**
     * Edit payment screen JS
     */
    var pl8app_Edit_Payment = {

        init: function () {
            this.edit_address();
            this.remove_menuitem();
            this.add_menuitem();
            this.create_order_item();
            this.change_customer();
            this.new_customer();
            this.edit_price();
            this.edit_qty();
            this.recalculate_total();
            this.variable_prices_check();
            this.add_note();
            this.remove_note();
            this.resend_receipt();
            this.copy_menuitem_link();
            this.service_type_delivery_address();
        },

        edit_address: function () {

            // Update base state field based on selected base country
            $('select[name="pl8app-payment-address[0][country]"]').change(function () {
                var $this = $(this);
                var data = {
                    action: 'pl8app_get_states',
                    country: $this.val(),
                    field_name: 'pl8app-payment-address[0][state]'
                };
                $.post(ajaxurl, data, function (response) {
                    var state_wrapper = $('#pl8app-order-address-state-wrap select, #pl8app-order-address-state-wrap input');
                    // Remove any chosen containers here too
                    $('#pl8app-order-address-state-wrap .chosen-container').remove();
                    if ('nostates' == response) {
                        state_wrapper.replaceWith('<input type="text" name="pl8app-payment-address[0][state]" value="" class="pl8app-edit-toggles medium-text"/>');
                    } else {
                        state_wrapper.replaceWith(response);
                    }
                });

                return false;
            });

        },

        remove_menuitem: function () {

            // Remove a menuitem from a purchase
            $('#pl8app-purchased-items').on('click', '.pl8app-order-remove-menuitem', function (e) {

                var count = $(document.body).find('#pl8app-purchased-items > .row:not(.header)').length;

                if (count === 1) {
                    alert(pl8app_vars.one_menuitem_min);
                    return false;
                }

                if (confirm(pl8app_vars.delete_payment_menuitem)) {

                    var key = $(this).data('key');
                    var menuitem_id = $('input[name="pl8app-payment-details-menuitems[' + key + '][id]"]').val();
                    var price_id = $('input[name="pl8app-payment-details-menuitems[' + key + '][price_id]"]').val();
                    var quantity = $('input[name="pl8app-payment-details-menuitems[' + key + '][quantity]"]').val();
                    var amount = $('input[name="pl8app-payment-details-menuitems[' + key + '][amount]"]').val();

                    // if ( $('input[name="pl8app-payment-details-menuitems['+key+'][item_tax]"]') ) {
                    //   var fees = $('input[name="pl8app-payment-details-menuitems['+key+'][item_tax]"]').val();
                    // }

                    // if ( $('input[name="pl8app-payment-details-menuitems['+key+'][fees]"]') ) {
                    //   var fees = $.parseJSON( $('input[name="pl8app-payment-details-menuitems['+key+'][fees]"]').val() );
                    // }

                    var currently_removed = $('input[name="pl8app-payment-removed"]').val();
                    currently_removed = $.parseJSON(currently_removed);
                    if (currently_removed.length < 1) {
                        currently_removed = {};
                    }

                    var removed_item = [{
                        'id': menuitem_id,
                        'price_id': price_id,
                        'quantity': quantity,
                        'amount': amount,
                        'cart_index': key
                    }];
                    currently_removed[key] = removed_item

                    $('input[name="pl8app-payment-removed"]').val(JSON.stringify(currently_removed));

                    $(this).parents('.row.pl8app-purchased-row').remove();

                    // if ( fees && fees.length) {
                    //   $.each( fees, function( key, value ) {
                    //     $('*li[data-fee-id="' + value + '"]').remove();
                    //   });
                    // }

                    // Flag the pl8app section as changed
                    $('#pl8app-payment-menuitems-changed').val(1);
                    $('.pl8app-order-payment-recalc-totals').show();
                }
                return false;
            });

        },

        change_customer: function () {

            $('#pl8app-customer-details').on('click', '.pl8app-payment-change-customer, .pl8app-payment-change-customer-cancel', function (e) {
                e.preventDefault();

                var change_customer = $(this).hasClass('pl8app-payment-change-customer');
                var cancel = $(this).hasClass('pl8app-payment-change-customer-cancel');

                if (change_customer) {
                    $('.customer-info').hide();
                    $('.change-customer').show();
                    $('.pl8app-payment-change-customer-input').css('width', 'auto');
                } else if (cancel) {
                    $('.customer-info').show();
                    $('.change-customer').hide();
                }

            });

        },

        new_customer: function () {

            $('#pl8app-customer-details').on('click', '.pl8app-payment-new-customer, .pl8app-payment-new-customer-cancel', function (e) {
                e.preventDefault();

                var new_customer = $(this).hasClass('pl8app-payment-new-customer');
                var cancel = $(this).hasClass('pl8app-payment-new-customer-cancel');

                if (new_customer) {
                    $('.customer-info').hide();
                    $('.new-customer').show();
                } else if (cancel) {
                    $('.customer-info').show();
                    $('.new-customer').hide();
                }


                var new_customer = $('#pl8app-new-customer');
                if ($('.new-customer').is(":visible")) {
                    new_customer.val(1);
                } else {
                    new_customer.val(0);
                }

            });

        },

        loop_all_sub_bundle_menu_item: function(){
            return new Promise(async (resolve, reject)=>{

                for(const e of pl8app_Edit_Payment.bundles){
                    if (parseInt(e) > 0) {
                        var postData = {
                            action: 'pl8app_check_for_menuitem_price_variations',
                            menuitem_id: e
                        };

                        $('select#pl8app_order_menuitem_select').val(e);

                        try{
                            await $.ajax({
                                type: "POST",
                                data: postData,
                                url: ajaxurl,
                                success: function (response) {
                                    $('select#pl8app_order_menuitem_select').parents('.pl8app-add-menuitem-to-purchase').find('span.pl8app-menuitem-price').html(response.price);
                                    $('#pl8app-order-menuitem-tax').val(response.tax);
                                    $('#pl8app-order-menuitem-quantity').val(1);
                                }
                            });
                            await pl8app_Edit_Payment.clone_new_menu_item();
                        }
                        catch (e) {
                            console.log(e);
                        }

                    }
                }
                resolve();
            });

        },
        create_order_item: function () {

            // Add a New pl8app from the Add pl8app to Purchase Box
            $('.pl8app-edit-purchase-element').on('click', '#pl8app-order-create-menuitem',async function (e) {

                e.preventDefault();

                if(pl8app_Edit_Payment.bundles.length > 0) {
                    await pl8app_Edit_Payment.loop_all_sub_bundle_menu_item();
                    pl8app_Edit_Payment.bundles = [];
                }
                else{
                    await pl8app_Edit_Payment.clone_new_menu_item();
                }
            });
        },


        clone_new_menu_item: function(){

            return new Promise((resolve, reject) => {
                var order_menuitem_select = $('#pl8app_order_menuitem_select'),
                    order_menuitem_quantity = $('#pl8app-order-menuitem-quantity'),
                    order_menuitem_price = $('#pl8app-order-menuitem-price'),
                    order_menuitem_tax = $('#pl8app-order-menuitem-tax'),
                    selected_price_option = $('.pl8app_price_options_select option:selected'),
                    selected_item_price = $('.pl8app_selected_price');

                var menuitem_id = order_menuitem_select.val();
                var menuitem_title = order_menuitem_select.find(':selected').text();
                var quantity = order_menuitem_quantity.val();
                var item_price = selected_item_price.val();
                var item_tax = order_menuitem_tax.val();
                var price_id = selected_price_option.val();
                var price_name = selected_price_option.text();

                if (menuitem_id < 1) {
                    return false;
                }

                if (!item_price) {
                    item_price = 0;
                }

                item_price = parseFloat(item_price);
                if (isNaN(item_price)) {
                    alert(pl8app_vars.numeric_item_price);
                    return false;
                }

                item_tax = parseFloat(item_tax);
                if (isNaN(item_tax)) {
                    alert(pl8app_vars.numeric_item_tax);
                    return false;
                }

                if (isNaN(parseInt(quantity))) {
                    alert(pl8app_vars.numeric_quantity);
                    return false;
                }

                if (price_name) {
                    menuitem_title = menuitem_title + ' - ' + price_name;
                }

                var count = $('#pl8app-purchased-items div.row').length;
                var IndexCount = count - 1;

                var clone = pl8app_vars.new_menu_item_template;
                clone = $(clone);
                // var Name = $('#pl8app-purchased-items div.row:last').find('select').attr('name');

                clone.find('.menuitem span.pl8app-purchased-menuitem-title').html('<a href="post.php?post=' + menuitem_id + '&action=edit"></a>');
                clone.find('.menuitem span.pl8app-purchased-menuitem-title a').text(menuitem_title);
                clone.find('h3.pl8app-purchased-item-name').text(menuitem_title);
                clone.find('.pl8app-payment-details-menuitem-item-price').val(item_price.toFixed(pl8app_vars.currency_decimals));
                clone.find('.pl8app-payment-details-menuitem-item-tax').val(item_tax.toFixed(pl8app_vars.currency_decimals));
                clone.find('input.pl8app-payment-details-menuitem-id').val(menuitem_id);
                clone.find('input.pl8app-payment-details-menuitem-price-id').val(price_id);

                clone.find('.order-addon-items.special-instructions').remove();

                var item_total = (item_price * quantity) + item_tax;
                item_total = item_total.toFixed(pl8app_vars.currency_decimals);
                clone.find('span.pl8app-payment-details-menuitem-amount').text(item_total);
                clone.find('input.pl8app-payment-details-menuitem-amount').val(item_total);
                clone.find('input.pl8app-payment-details-menuitem-quantity').val(quantity);
                clone.find('input.pl8app-payment-details-menuitem-has-log').val(0);

                clone.find('.pl8app-copy-menuitem-link-wrapper').remove();
                clone.find('.pl8app-special-instruction').remove();

                // Replace the name / id attributes
                clone.find('input').each(function () {
                    var name = $(this).attr('name');
                    if (name !== undefined) {
                        name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');

                        $(this).attr('name', name).attr('id', name);
                    }
                });

                clone.find('select').each(function () {
                    var name = $(this).attr('name');
                    var CustomName = 'pl8app-payment-details-menuitems[' + count + '][addon_items][]';
                    $(this).attr('name', CustomName);
                });

                clone.find('a.pl8app-order-remove-menuitem').attr('data-key', parseInt(count));

                // Flag the pl8app section as changed
                $('#pl8app-payment-menuitems-changed').val(1);

                setTimeout(function () {
                    pl8app_get_addon_items_list(menuitem_id, clone);
                }, 1000);

                $('#pl8app-purchased-items').append(clone);
                clone.find('select').html();
                $('.pl8app-order-payment-recalc-totals').show();
                $('.pl8app-add-menuitem-field').val('');

                $("#pl8app_order_menuitem_select").val('').trigger("chosen:updated");
                $(".pl8app-add-update-elements").find('.pl8app-menuitem-price').empty();
                resolve();
            });

        },

        add_menuitem: function(){
            $('.pl8app-edit-purchase-element').on('click', '#pl8app-order-add-menuitem', function (e) {

                e.preventDefault();

                var selectedButton = $(this);

                var order_menuitem_select = $('#pl8app_order_menuitem_select'),
                    order_menuitem_quantity = $('#pl8app-order-menuitem-quantity'),
                    order_menuitem_price = $('#pl8app-order-menuitem-price'),
                    order_menuitem_tax = $('#pl8app-order-menuitem-tax'),
                    selected_price_option = $('.pl8app_price_options_select option:selected'),
                    selected_item_price = $('.pl8app_selected_price');

                var menuitem_id = order_menuitem_select.val();
                var menuitem_title = order_menuitem_select.find(':selected').text();
                var quantity = order_menuitem_quantity.val();
                var item_price = selected_item_price.val();
                var item_tax = order_menuitem_tax.val();
                var price_id = selected_price_option.val();
                var price_name = selected_price_option.text();

                if (menuitem_id < 1) {
                    return false;
                }

                if (!item_price) {
                    item_price = 0;
                }

                item_price = parseFloat(item_price);
                if (isNaN(item_price)) {
                    alert(pl8app_vars.numeric_item_price);
                    return false;
                }

                item_tax = parseFloat(item_tax);
                if (isNaN(item_tax)) {
                    alert(pl8app_vars.numeric_item_tax);
                    return false;
                }

                if (isNaN(parseInt(quantity))) {
                    alert(pl8app_vars.numeric_quantity);
                    return false;
                }

                if (price_name) {
                    menuitem_title = menuitem_title + ' - ' + price_name;
                }

                var count = $('#pl8app-purchased-items div.row').length;
                var IndexCount = count - 1;
                var clone = $('#pl8app-purchased-items div.row:last').clone();
                // var Name = $('#pl8app-purchased-items div.row:last').find('select').attr('name');

                clone.find('.menuitem span.pl8app-purchased-menuitem-title').html('<a href="post.php?post=' + menuitem_id + '&action=edit"></a>');
                clone.find('.menuitem span.pl8app-purchased-menuitem-title a').text(menuitem_title);
                clone.find('h3.pl8app-purchased-item-name').text(menuitem_title);
                clone.find('.pl8app-payment-details-menuitem-item-price').val(item_price.toFixed(pl8app_vars.currency_decimals));
                clone.find('.pl8app-payment-details-menuitem-item-tax').val(item_tax.toFixed(pl8app_vars.currency_decimals));
                clone.find('input.pl8app-payment-details-menuitem-id').val(menuitem_id);
                clone.find('input.pl8app-payment-details-menuitem-price-id').val(price_id);

                clone.find('.order-addon-items.special-instructions').remove();

                var item_total = (item_price * quantity) + item_tax;
                item_total = item_total.toFixed(pl8app_vars.currency_decimals);
                clone.find('span.pl8app-payment-details-menuitem-amount').text(item_total);
                clone.find('input.pl8app-payment-details-menuitem-amount').val(item_total);
                clone.find('input.pl8app-payment-details-menuitem-quantity').val(quantity);
                clone.find('input.pl8app-payment-details-menuitem-has-log').val(0);

                clone.find('.pl8app-copy-menuitem-link-wrapper').remove();
                clone.find('.pl8app-special-instruction').remove();

                // Replace the name / id attributes
                clone.find('input').each(function () {
                    var name = $(this).attr('name');
                    if (name !== undefined) {
                        name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');

                        $(this).attr('name', name).attr('id', name);
                    }
                });

                clone.find('select').each(function () {
                    var name = $(this).attr('name');
                    var CustomName = 'pl8app-payment-details-menuitems[' + count + '][addon_items][]';
                    $(this).attr('name', CustomName);
                });

                clone.find('a.pl8app-order-remove-menuitem').attr('data-key', parseInt(count));

                // Flag the pl8app section as changed
                $('#pl8app-payment-menuitems-changed').val(1);

                setTimeout(function () {
                    pl8app_get_addon_items_list(menuitem_id, clone);
                }, 1000);



                $(clone).insertAfter('#pl8app-purchased-items div.row:last');
                clone.find('select').html();
                $('.pl8app-order-payment-recalc-totals').show();
                $('.pl8app-add-menuitem-field').val('');

                $("#pl8app_order_menuitem_select").val('').trigger("chosen:updated");
                $(".pl8app-add-update-elements").find('.pl8app-menuitem-price').empty();
            });
        },

        edit_qty: function () {
            $(document.body).on('change keyup', '.pl8app-payment-details-menuitem-quantity', function () {
                var selectedQty = $(this).val();
                var row = $(this).parents('ul.pl8app-purchased-items-list-wrapper');


                row.find('input.pl8app-payment-details-menuitem-quantity').val(selectedQty);
            });
        },

        edit_price: function () {
            $(document.body).on('change keyup', '.pl8app-payment-item-input', function () {
                var row = $(this).parents('ul.pl8app-purchased-items-list-wrapper');
                $('.pl8app-order-payment-recalc-totals').show();

                var quantity = row.find('input.pl8app-payment-details-menuitem-quantity').val().replace(pl8app_vars.thousands_separator, '');
                var item_price = row.find('input.pl8app-payment-details-menuitem-item-price').val().replace(pl8app_vars.thousands_separator, '');
                var item_tax = row.find('input.pl8app-payment-details-menuitem-item-tax').val().replace(pl8app_vars.thousands_separator, '');
                if ($(this).hasClass('pl8app-payment-details-menuitem-quantity')) {
                    var quantity = $(this).val();
                }

                item_price = parseFloat(item_price);
                if (isNaN(item_price)) {
                    alert(pl8app_vars.numeric_item_price);
                    return false;
                }

                item_tax = parseFloat(item_tax);
                if (isNaN(item_tax)) {
                    item_tax = 0.00;
                }

                if (isNaN(parseInt(quantity))) {
                    quantity = 1;
                }

                var item_total = (item_price * quantity) + item_tax;
                item_total = item_total.toFixed(pl8app_vars.currency_decimals);
                row.find('input.pl8app-payment-details-menuitem-amount').val(item_total);
                row.find('span.pl8app-payment-details-menuitem-amount').text(item_total);
            });

        },

        recalculate_total: function () {

            // Update taxes and totals for any changes made.
            $('#pl8app-order-recalc-total').on('click', function (e) {
                e.preventDefault();

                var addonTotalPrice;
                var addonTotal = 0;

                $(".addon-items-list").each(function (key, item) {

                    var row = $(this).parents('.pl8app-order-items-wrapper');
                    var quantity = row.find('input.pl8app-payment-details-menuitem-quantity').val().replace(pl8app_vars.thousands_separator, '');

                    addonTotalPrice = $(this).val();
                    if (addonTotalPrice !== null && addonTotalPrice !== '') {
                        for (var i = 0; i < addonTotalPrice.length; i++) {
                            addonData = addonTotalPrice[i].split('|');
                            addonData = addonData[2] == '' ? 0 : addonData[2];
                            addonTotal += parseFloat(addonData * quantity);
                        }
                    }

                });

                var total = 0,
                    tax = 0,
                    totals = $('#pl8app-purchased-items .row input.pl8app-payment-details-menuitem-amount'),
                    taxes = $('#pl8app-purchased-items .row input.pl8app-payment-details-menuitem-item-tax');

                if (totals.length) {
                    totals.each(function () {
                        total += parseFloat($(this).val());
                    });
                }

                total += addonTotal;

                if (taxes.length) {
                    taxes.each(function () {
                        tax += parseFloat($(this).val());
                    });
                }

                if ($('.pl8app-payment-fees').length) {
                    $('.pl8app-payment-fees span.fee-amount').each(function () {
                        total += parseFloat($(this).data('fee'));
                    });
                }

                $('input[name=pl8app-payment-total]').val(total.toFixed(pl8app_vars.currency_decimals));
                $('input[name=pl8app-payment-tax]').val(tax.toFixed(pl8app_vars.currency_decimals))
            });

        },

        variable_prices_check: function () {

            // On pl8app Select, Check if Variable Prices Exist
            $('.pl8app-edit-purchase-element').on('change', 'select#pl8app_order_menuitem_select', function () {

                var $this = $(this), menuitem_id = $this.val();

                if (parseInt(menuitem_id) > 0) {
                    var postData = {
                        action: 'pl8app_check_for_menuitem_price_variations',
                        menuitem_id: menuitem_id
                    };

                    $.ajax({
                        type: "POST",
                        data: postData,
                        url: ajaxurl,
                        success: function (response) {
                            $this.parents('.pl8app-add-menuitem-to-purchase').find('span.pl8app-menuitem-price').html(response.price);
                            $('#pl8app-order-menuitem-tax').val(response.tax);
                            if(response.bundle && response.bundle.length > 0){
                                pl8app_Edit_Payment.bundles = response.bundle;
                            }
                            else{
                                pl8app_Edit_Payment.bundles = [];
                            }

                            //$this.parents('.pl8app-add-menuitem-to-purchase').find('input.pl8app-order-menuitem-price').val(response);
                            //$('.pl8app_price_options_select').remove();
                            //$(response).insertAfter( $this.next() );
                        }
                    }).fail(function (data) {
                        if (window.console && window.console.log) {
                            console.log(data);
                        }
                    });

                }
            });

        },

        add_note: function () {

            $('#pl8app-add-payment-note').on('click', function (e) {
                e.preventDefault();
                var postData = {
                    action: 'pl8app_insert_payment_note',
                    payment_id: $(this).data('payment-id'),
                    note: $('#pl8app-payment-note').val()
                };

                if (postData.note) {

                    $.ajax({
                        type: "POST",
                        data: postData,
                        url: ajaxurl,
                        success: function (response) {
                            $('#pl8app-payment-notes-inner').append(response);
                            $('.pl8app-no-payment-notes').hide();
                            $('#pl8app-payment-note').val('');
                        }
                    }).fail(function (data) {
                        if (window.console && window.console.log) {
                            console.log(data);
                        }
                    });

                } else {
                    var border_color = $('#pl8app-payment-note').css('border-color');
                    $('#pl8app-payment-note').css('border-color', 'red');
                    setTimeout(function () {
                        $('#pl8app-payment-note').css('border-color', border_color);
                    }, 500);
                }

            });

        },

        remove_note: function () {

            $(document.body).on('click', '.pl8app-delete-payment-note', function (e) {

                e.preventDefault();

                if (confirm(pl8app_vars.delete_payment_note)) {

                    var postData = {
                        action: 'pl8app_delete_payment_note',
                        payment_id: $(this).data('payment-id'),
                        note_id: $(this).data('note-id')
                    };

                    $.ajax({
                        type: "POST",
                        data: postData,
                        url: ajaxurl,
                        success: function (response) {
                            $('#pl8app-payment-note-' + postData.note_id).remove();
                            if (!$('.pl8app-payment-note').length) {
                                $('.pl8app-no-payment-notes').show();
                            }
                            return false;
                        }
                    }).fail(function (data) {
                        if (window.console && window.console.log) {
                            console.log(data);
                        }
                    });
                    return true;
                }

            });

        },

        resend_receipt: function () {

            var emails_wrap = $('.pl8app-order-resend-receipt-addresses');

            $(document.body).on('click', '#pl8app-select-receipt-email', function (e) {

                e.preventDefault();
                emails_wrap.slideDown();

            });

            $(document.body).on('change', '.pl8app-order-resend-receipt-email', function () {

                var href = $('#pl8app-select-receipt-email').prop('href') + '&email=' + $(this).val();

                if (confirm(pl8app_vars.resend_receipt)) {
                    window.location = href;
                }

            });


            $(document.body).on('click', '#pl8app-resend-receipt', function (e) {

                return confirm(pl8app_vars.resend_receipt);

            });

        },

        copy_menuitem_link: function () {
            $(document.body).on('click', '.pl8app-copy-menuitem-link', function (e) {
                e.preventDefault();
                var $this = $(this);
                var postData = {
                    action: 'pl8app_get_file_menuitem_link',
                    payment_id: $('input[name="pl8app_payment_id"]').val(),
                    menuitem_id: $this.data('menuitem-id'),
                    price_id: $this.data('price-id')
                };

                $.ajax({
                    type: "POST",
                    data: postData,
                    url: ajaxurl,
                    success: function (link) {
                        $("#pl8app-menuitem-link").dialog({
                            width: 400
                        }).html('<textarea rows="10" cols="40" id="pl8app-menuitem-link-textarea">' + link + '</textarea>');
                        $("#pl8app-menuitem-link-textarea").focus().select();
                        return false;
                    }
                }).fail(function (data) {
                    if (window.console && window.console.log) {
                        console.log(data);
                    }
                });

            });
        },

        service_type_delivery_address: function () {
            $(document.body).on('change', 'select[name="pla_service_type"]', function () {
                if($(this).val() != 'delivery'){
                    $('div.pl8app-delivery-address').css('display', 'none');
                }
                else{
                    $('div.pl8app-delivery-address').css('display', 'block');
                }
            });
        }

    };
    pl8app_Edit_Payment.init();


    /**
     * Discount add / edit screen JS
     */
    var pl8app_Discount = {

        init: function () {
            this.type_select();
            this.product_requirements();
        },

        type_select: function () {

            $('#pl8app-edit-discount #pl8app-type, #pl8app-add-discount #pl8app-type').change(function () {
                var val = $(this).val();
                $('.pl8app-amount-description').hide();
                $('.pl8app-amount-description.' + val + '-discount').show();

            });

        },

        product_requirements: function () {

            $('#products').change(function () {

                var product_conditions = $('#pl8app-discount-product-conditions');

                if ($(this).val()) {
                    product_conditions.show();
                } else {
                    product_conditions.hide();
                }

            });

        },

    };
    pl8app_Discount.init();


    /**
     * Reports / Exports screen JS
     */
    var pl8app_Reports = {

        init: function () {
            this.date_options();
            this.customers_export();
        },

        date_options: function () {

            // Show hide extended date options
            $('#pl8app-graphs-date-options').change(function () {
                var $this = $(this),
                    date_range_options = $('#pl8app-date-range-options');

                if ('other' === $this.val()) {
                    date_range_options.show();
                } else {
                    date_range_options.hide();
                }
            });

        },

        customers_export: function () {

            // Show / hide pl8app option when exporting customers

            $('#pl8app_customer_export_menuitem').change(function () {

                var $this = $(this),
                    menuitem_id = $('option:selected', $this).val(),
                    customer_export_option = $('#pl8app_customer_export_option');

                if ('0' === $this.val()) {
                    customer_export_option.show();
                } else {
                    customer_export_option.hide();
                }

                // On pl8app Select, Check if Variable Prices Exist
                if (parseInt(menuitem_id) != 0) {
                    var data = {
                        action: 'pl8app_check_for_menuitem_price_variations',
                        menuitem_id: menuitem_id,
                        all_prices: true
                    };

                    var price_options_select = $('.pl8app_price_options_select');

                    $.post(ajaxurl, data, function (response) {
                        price_options_select.remove();
                        $('#pl8app_customer_export_menuitem_chosen').after(response);
                    });
                } else {
                    price_options_select.remove();
                }
            });

        }

    };
    pl8app_Reports.init();

    /**
     * Settings screen JS
     */
    var pl8app_Settings = {

        init: function () {
            this.general();
            this.taxes();
            this.misc();
        },

        general: function () {

            var pl8app_color_picker = $('.pl8app-color-picker');

            if (pl8app_color_picker.length) {
                pl8app_color_picker.wpColorPicker();
            }

            // Settings Upload field JS
            if (typeof wp === "undefined" || '1' !== pl8app_vars.new_media_ui) {
                //Old Thickbox uploader
                var pl8app_settings_upload_button = $('.pl8app_settings_upload_button');
                if (pl8app_settings_upload_button.length > 0) {
                    window.formfield = '';

                    $(document.body).on('click', pl8app_settings_upload_button, function (e) {
                        e.preventDefault();
                        window.formfield = $(this).parent().prev();
                        window.tbframe_interval = setInterval(function () {
                            jQuery('#TB_iframeContent').contents().find('.savesend .button').val(pl8app_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
                        }, 2000);
                        tb_show(pl8app_vars.add_new_menuitem, 'media-upload.php?TB_iframe=true');
                    });

                    window.pl8app_send_to_editor = window.send_to_editor;
                    window.send_to_editor = function (html) {
                        if (window.formfield) {
                            imgurl = $('a', '<div>' + html + '</div>').attr('href');
                            window.formfield.val(imgurl);
                            window.clearInterval(window.tbframe_interval);
                            tb_remove();
                        } else {
                            window.pl8app_send_to_editor(html);
                        }
                        window.send_to_editor = window.pl8app_send_to_editor;
                        window.formfield = '';
                        window.imagefield = false;
                    };
                }
            } else {
                // WP 3.5+ uploader
                var file_frame;
                window.formfield = '';

                $(document.body).on('click', '.pl8app_settings_upload_button', function (e) {

                    e.preventDefault();

                    var button = $(this);

                    window.formfield = $(this).parent().prev();

                    // If the media frame already exists, reopen it.
                    if (file_frame) {
                        //file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
                        file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                        frame: 'post',
                        state: 'insert',
                        title: button.data('uploader_title'),
                        button: {
                            text: button.data('uploader_button_text')
                        },
                        multiple: false
                    });

                    file_frame.on('menu:render:default', function (view) {
                        // Store our views in an object.
                        var views = {};

                        // Unset default menu items
                        view.unset('library-separator');
                        view.unset('gallery');
                        view.unset('featured-image');
                        view.unset('embed');

                        // Initialize the views in our view object.
                        view.set(views);
                    });

                    // When an image is selected, run a callback.
                    file_frame.on('insert', function () {

                        var selection = file_frame.state().get('selection');
                        selection.each(function (attachment, index) {
                            attachment = attachment.toJSON();
                            window.formfield.val(attachment.url);
                            if($(window.formfield).prev().hasClass('thumbnail-image')){
                                $(window.formfield).prev().find('img').attr("src", attachment.url);
                                $(window.formfield).prev().removeClass('hidden');
                                $(window.formfield).prev().prev().addClass('hidden');
                            }
                        });
                    });

                    // Finally, open the modal
                    file_frame.open();
                });

                $(document.body).on('click', '.button.image-upload-remove-button', function(e) {
                    e.preventDefault();
                    window.formfield = $(this).parent().prev();
                    window.formfield.val('');
                    if($(window.formfield).prev().hasClass('thumbnail-image')){
                        $(window.formfield).prev().find('img').attr("src", "");
                        $(window.formfield).prev().addClass('hidden');
                        $(window.formfield).prev().prev().removeClass('hidden');
                    }
                });


                // WP 3.5+ uploader
                var file_frame;
                window.formfield = '';
            }

        },

        taxes: function () {
            var no_states = $('select.pl8app-no-states');
            if (no_states.length) {
                no_states.closest('tr').addClass('hidden');
            }

            // Update base state field based on selected base country
            $('select[name="pl8app_settings[base_country]"]').change(function () {
                var $this = $(this), $tr = $this.closest('tr');
                var data = {
                    action: 'pl8app_get_states',
                    country: $(this).val(),
                    field_name: 'pl8app_settings[base_state]'
                };
                $.post(ajaxurl, data, function (response) {
                    if ('nostates' == response) {
                        $tr.next().addClass('hidden');
                    } else {
                        $tr.next().removeClass('hidden');
                        $tr.next().find('select').replaceWith(response);
                    }
                });

                return false;
            });

            // Update tax rate state field based on selected rate country
            $(document.body).on('change', '#pl8app_tax_rates select.pl8app-tax-country', function () {
                var $this = $(this);
                var data = {
                    action: 'pl8app_get_states',
                    country: $(this).val(),
                    field_name: $this.attr('name').replace('country', 'state')
                };
                $.post(ajaxurl, data, function (response) {
                    if ('nostates' == response) {
                        var text_field = '<input type="text" name="' + data.field_name + '" value=""/>';
                        $this.parent().next().find('select').replaceWith(text_field);
                    } else {
                        $this.parent().next().find('input,select').show();
                        $this.parent().next().find('input,select').replaceWith(response);
                    }
                });

                return false;
            });

            $(document.body).on('click', '#pl8app_tax_rates tbody#rates tr', function(evt){

                if (evt.ctrlKey){
                    $(this).addClass("current");
                }
                else{
                    $('#pl8app_tax_rates tbody#rates tr').each((i, e) => {
                        $(e).removeClass("current");
                    });
                    $(this).addClass("current");
                }

                $('#pl8app_tax_rates .pl8app_remove_tax_rate').removeClass('disabled');
            });

            // Insert new tax rate row
            $('#pl8app_add_tax_rate').on('click', function () {
                var unqiue_key = ID();
                var clone = '<tr class="rate">' +
                    '<td class="rate">' +
                    '<input type="text" value="1.00" placeholder="0" name="pl8app_settings[tax][' + unqiue_key + '][rate]" data-attribute="tax_rate" required>' +
                    '</td>' +
                    '<td class="name">' +
                    '<input type="text" value="A" name="pl8app_settings[tax][' + unqiue_key + '][name]" data-attribute="tax_rate_name" pattern="[A-Z]{1,1}" required> ' +
                    '</td>' +
                    '<td class="desc">' +
                    '<input type="text" value="new_tax" name="pl8app_settings[tax][' + unqiue_key + '][desc]" data-attribute="tax_rate_desc" required>' +
                    '</td>' +
                    '</tr>';
                $('#pl8app_tax_rates tbody#rates').append(clone);
                return false;
            });

            // Remove tax row
            $(document.body).on('click', '#pl8app_tax_rates .pl8app_remove_tax_rate', function () {
                if($(this).hasClass('disabled')) return false;
                if (confirm(pl8app_vars.delete_tax_rate)) {

                    /* re-index after deleting */
                    $('#pl8app_tax_rates tbody#rates tr.current').each(function (rowIndex, e) {
                        $(e).remove();
                    });
                    $(this).addClass('disabled');
                }
                return false;
            });

        },

        misc: function () {

            var menuitemMethod = $('select[name="pl8app_settings[menuitem_method]"]');
            var symlink = menuitemMethod.parent().parent().next();

            // Hide Symlink option if pl8app Method is set to Direct
            if (menuitemMethod.val() == 'direct') {
                symlink.hide();
                symlink.find('input').prop('checked', false);
            }
            // Toggle menuitem method option
            menuitemMethod.on('change', function () {
                if ($(this).val() == 'direct') {
                    symlink.hide();
                    symlink.find('input').prop('checked', false);
                } else {
                    symlink.show();
                }
            });
        }

    }
    pl8app_Settings.init();

    var ID = function () {
        // Math.random should be unique because of its seeding algorithm.
        // Convert it to base 36 (numbers + letters), and grab the first 9 characters
        // after the decimal.
        return '_' + Math.random().toString(36).substr(2, 9);
    };

    $('.menuitem_page_pl8app-payment-history .row-actions .delete a, a.pl8app-delete-payment').on('click', function () {
        if (confirm(pl8app_vars.delete_payment)) {
            return true;
        }
        return false;
    });

    $('body').on('click', '#the-list .editinline', function () {

        var post_id = $(this).closest('tr').attr('id');

        post_id = post_id.replace("post-", "");

        var $pl8app_inline_data = $('#post-' + post_id);

        var regprice = $pl8app_inline_data.find('.column-price .menuitemprice-' + post_id).val();

        // If variable priced product disable editing, otherwise allow price changes
        if (regprice != $('#post-' + post_id + '.column-price .menuitemprice-' + post_id).val()) {
            $('.regprice', '#pl8app-menuitem-data').val(regprice).attr('disabled', false);
        } else {
            $('.regprice', '#pl8app-menuitem-data').val(pl8app_vars.quick_edit_warning).attr('disabled', 'disabled');
        }
    });


    // Bulk edit save
    $(document.body).on('click', '#bulk_edit', function () {

        // define the bulk edit row
        var $bulk_row = $('#bulk-edit');

        // get the selected post ids that are being edited
        var $post_ids = new Array();
        $bulk_row.find('#bulk-titles').children().each(function () {
            $post_ids.push($(this).attr('id').replace(/^(ttle)/i, ''));
        });

        // get the stock and price values to save for all the product ID's
        var $price = $('#pl8app-menuitem-data input[name="_pl8app_regprice"]').val();

        var data = {
            action: 'pl8app_save_bulk_edit',
            pl8app_bulk_nonce: $post_ids,
            post_ids: $post_ids,
            price: $price
        };

        // save the data
        $.post(ajaxurl, data);

    });

    // Setup Chosen menus
    $('.pl8app-select-chosen').chosen({
        inherit_select_classes: true,
        placeholder_text_single: pl8app_vars.one_option,
        placeholder_text_multiple: pl8app_vars.one_or_more_option,
    });

    $('.pl8app-select-chosen .chosen-search input').each(function () {
        var selectElem = $(this).parent().parent().parent().prev('select.pl8app-select-chosen'),
            type = selectElem.data('search-type'),
            placeholder = selectElem.data('search-placeholder');
        $(this).attr('placeholder', placeholder);
    });

    // Add placeholders for Chosen input fields
    $('.chosen-choices').on('click', function () {
        var placeholder = $(this).parent().prev().data('search-placeholder');
        if (typeof placeholder === "undefined") {
            placeholder = pl8app_vars.type_to_search;
        }
        $(this).children('li').children('input').attr('placeholder', placeholder);
    });

    // Variables for setting up the typing timer
    var typingTimer;               // Timer identifier
    var doneTypingInterval = 342;  // Time in ms, Slow - 521ms, Moderate - 342ms, Fast - 300ms

    // Replace options with search results
    $(document.body).on('keyup', '.pl8app-select.chosen-container .chosen-search input, .pl8app-select.chosen-container .search-field input', function (e) {

        var val = $(this).val()
        var container = $(this).closest('.pl8app-select-chosen');
        var menu_id = container.attr('id').replace('_chosen', '');
        var select = container.prev();
        var no_bundles = container.hasClass('no-bundles');
        var variations = container.hasClass('variations');
        var lastKey = e.which;
        var search_type = 'pl8app_menuitem_search';

        // Detect if we have a defined search type, otherwise default to menuitems
        if (container.prev().data('search-type')) {

            // Don't trigger AJAX if this select has all options loaded
            if ('no_ajax' == select.data('search-type')) {
                return;
            }

            search_type = 'pl8app_' + select.data('search-type') + '_search';
        }

        // Don't fire if short or is a modifier key (shift, ctrl, apple command key, or arrow keys)
        if (
            (val.length <= 3 && 'pl8app_menuitem_search' == search_type) ||
            (
                lastKey == 16 ||
                lastKey == 13 ||
                lastKey == 91 ||
                lastKey == 17 ||
                lastKey == 37 ||
                lastKey == 38 ||
                lastKey == 39 ||
                lastKey == 40
            )
        ) {
            return;
        }
        clearTimeout(typingTimer);
        typingTimer = setTimeout(
            function () {
                $.ajax({
                    type: 'GET',
                    url: ajaxurl,
                    data: {
                        action: search_type,
                        s: val,
                        no_bundles: no_bundles,
                        variations: variations,
                    },
                    dataType: "json",
                    beforeSend: function () {
                        select.closest('ul.chosen-results').empty();
                    },
                    success: function (data) {
                        // Remove all options but those that are selected
                        $('option:not(:selected)', select).remove();
                        $.each(data, function (key, item) {
                            // Add any option that doesn't already exist
                            if (!$('option[value="' + item.id + '"]', select).length) {
                                select.prepend('<option value="' + item.id + '">' + item.name + '</option>');
                            }
                        });
                        // Update the options
                        $('.pl8app-select-chosen').trigger('chosen:updated');
                        select.next().find('input').val(val);
                    }
                }).fail(function (response) {
                    if (window.console && window.console.log) {
                        console.log(response);
                    }
                }).done(function (response) {

                });
            },
            doneTypingInterval
        );
    });

    // This fixes the Chosen box being 0px wide when the thickbox is opened
    $('#post').on('click', '.pl8app-thickbox', function () {
        $('.pl8app-select-chosen', '#choose-menuitem').css('width', '100%');
    });


    /**
     * Tools screen JS
     */
    var pl8app_Tools = {

        init: function () {
            this.revoke_api_key();
            this.regenerate_api_key();
            this.create_api_key();
            this.recount_stats();
        },

        revoke_api_key: function () {
            $(document.body).on('click', '.pl8app-revoke-api-key', function (e) {
                return confirm(pl8app_vars.revoke_api_key);
            });
        },
        regenerate_api_key: function () {
            $(document.body).on('click', '.pl8app-regenerate-api-key', function (e) {
                return confirm(pl8app_vars.regenerate_api_key);
            });
        },
        create_api_key: function () {
            $(document.body).on('submit', '#api-key-generate-form', function (e) {
                var input = $('input[type="text"][name="user_id"]');

                input.css('border-color', '#ddd');

                var user_id = input.val();
                if (user_id.length < 1 || user_id == 0) {
                    input.css('border-color', '#ff0000');
                    return false;
                }
            });
        },
        recount_stats: function () {
            $(document.body).on('change', '#recount-stats-type', function () {

                var export_form = $('#pl8app-tools-recount-form');
                var selected_type = $('option:selected', this).data('type');
                var submit_button = $('#recount-stats-submit');
                var products = $('#tools-product-dropdown');

                // Reset the form
                export_form.find('.notice-wrap').remove();
                submit_button.removeClass('button-disabled').attr('disabled', false);
                products.hide();
                $('.pl8app-recount-stats-descriptions span').hide();

                if ('recount-menuitem' === selected_type) {

                    products.show();
                    products.find('.pl8app-select-chosen').css('width', 'auto');

                } else if ('reset-stats' === selected_type) {

                    export_form.append('<div class="notice-wrap"></div>');
                    var notice_wrap = export_form.find('.notice-wrap');
                    notice_wrap.html('<div class="notice notice-warning"><p><input type="checkbox" id="confirm-reset" name="confirm_reset_store" value="1" /> <label for="confirm-reset">' + pl8app_vars.reset_stats_warn + '</label></p></div>');

                    $('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');

                } else {

                    products.hide();
                    products.val(0);

                }

                $('#' + selected_type).show();
            });

            $(document.body).on('change', '#confirm-reset', function () {
                var checked = $(this).is(':checked');
                if (checked) {
                    $('#recount-stats-submit').removeClass('button-disabled').removeAttr('disabled');
                } else {
                    $('#recount-stats-submit').addClass('button-disabled').attr('disabled', 'disabled');
                }
            });

            $('#pl8app-tools-recount-form').submit(function (e) {
                var selection = $('#recount-stats-type').val();
                var export_form = $(this);
                var selected_type = $('option:selected', this).data('type');


                if ('reset-stats' === selected_type) {
                    var is_confirmed = $('#confirm-reset').is(':checked');
                    if (is_confirmed) {
                        return true;
                    } else {
                        has_errors = true;
                    }
                }

                export_form.find('.notice-wrap').remove();

                export_form.append('<div class="notice-wrap"></div>');
                var notice_wrap = export_form.find('.notice-wrap');
                var has_errors = false;

                if (null === selection || 0 === selection) {
                    // Needs to pick a method pl8app_vars.batch_export_no_class
                    notice_wrap.html('<div class="updated error"><p>' + pl8app_vars.batch_export_no_class + '</p></div>');
                    has_errors = true;
                }

                if ('recount-menuitem' === selected_type) {

                    var selected_menuitem = $('select[name="menuitem_id"]').val();
                    if (selected_menuitem == 0) {
                        // Needs to pick menuitem pl8app_vars.batch_export_no_reqs
                        notice_wrap.html('<div class="updated error"><p>' + pl8app_vars.batch_export_no_reqs + '</p></div>');
                        has_errors = true;
                    }

                }

                if (has_errors) {
                    export_form.find('.button-disabled').removeClass('button-disabled');
                    return false;
                }
            });
        },
    };
    pl8app_Tools.init();

    /**
     * Export screen JS
     */
    var pl8app_Export = {

        init: function () {
            this.submit();
            this.dismiss_message();
        },

        submit: function () {

            var self = this;

            $(document.body).on('submit', '.pl8app-export-form', function (e) {
                e.preventDefault();

                var submitButton = $(this).find('input[type="submit"]');

                if (!submitButton.hasClass('button-disabled')) {

                    var data = $(this).serialize();

                    submitButton.addClass('button-disabled');
                    $(this).find('.notice-wrap').remove();
                    $(this).append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="pl8app-progress"><div></div></div></div>');

                    // start the process
                    self.process_step(1, data, self);

                }

            });
        },

        process_step: function (step, data, self) {

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    form: data,
                    action: 'pl8app_do_ajax_export',
                    step: step,
                },
                dataType: "json",
                success: function (response) {
                    if ('done' == response.step || response.error || response.success) {

                        // We need to get the actual in progress form, not all forms on the page
                        var export_form = $('.pl8app-export-form').find('.pl8app-progress').parent().parent();
                        var notice_wrap = export_form.find('.notice-wrap');

                        export_form.find('.button-disabled').removeClass('button-disabled');

                        if (response.error) {

                            var error_message = response.message;
                            notice_wrap.html('<div class="updated error"><p>' + error_message + '</p></div>');

                        } else if (response.success) {

                            var success_message = response.message;
                            notice_wrap.html('<div id="pl8app-batch-success" class="updated notice is-dismissible"><p>' + success_message + '<span class="notice-dismiss"></span></p></div>');

                        } else {

                            notice_wrap.remove();
                            window.location = response.url;

                        }

                    } else {
                        $('.pl8app-progress div').animate({
                            width: response.percentage + '%',
                        }, 50, function () {
                            // Animation complete.
                        });
                        self.process_step(parseInt(response.step), data, self);
                    }

                }
            }).fail(function (response) {
                if (window.console && window.console.log) {
                    console.log(response);
                }
            });

        },

        dismiss_message: function () {
            $(document.body).on('click', '#pl8app-batch-success .notice-dismiss', function () {
                $('#pl8app-batch-success').parent().slideUp('fast');
            });
        }

    };
    pl8app_Export.init();

    /**
     * Import screen JS
     */
    var pl8app_Import = {

        init: function () {
            this.submit();
        },

        submit: function () {

            var self = this;

            $('.pl8app-import-form').ajaxForm({
                beforeSubmit: self.before_submit,
                success: self.success,
                complete: self.complete,
                dataType: 'json',
                error: self.error
            });

        },

        before_submit: function (arr, $form, options) {

            $form.find('.notice-wrap').remove();
            $form.append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="pl8app-progress"><div></div></div></div>');

            //check whether client browser fully supports all File API
            if (window.File && window.FileReader && window.FileList && window.Blob) {

                // HTML5 File API is supported by browser

            } else {

                var import_form = $('.pl8app-import-form').find('.pl8app-progress').parent().parent();
                var notice_wrap = import_form.find('.notice-wrap');

                import_form.find('.button-disabled').removeClass('button-disabled');

                //Error for older unsupported browsers that doesn't support HTML5 File API
                notice_wrap.html('<div class="update error"><p>' + pl8app_vars.unsupported_browser + '</p></div>');
                return false;

            }

        },

        success: function (responseText, statusText, xhr, $form) {
        },

        complete: function (xhr) {

            var response = jQuery.parseJSON(xhr.responseText);

            if (response.success) {

                var $form = $('.pl8app-import-form .notice-wrap').parent();

                $form.find('.pl8app-import-file-wrap,.notice-wrap').remove();

                $form.find('.pl8app-import-options').slideDown();

                // Show column mapping
                var select = $form.find('select.pl8app-import-csv-column');
                var row = select.parents('tr').first();
                var options = '';

                var columns = response.data.columns.sort(function (a, b) {
                    if (a < b) return -1;
                    if (a > b) return 1;
                    return 0;
                });

                $.each(columns, function (key, value) {
                    options += '<option value="' + value + '">' + value + '</option>';
                });

                select.append(options);

                select.on('change', function () {
                    var $key = $(this).val();

                    if (!$key) {

                        $(this).parent().next().html('');

                    } else {

                        if (false != response.data.first_row[$key]) {
                            $(this).parent().next().html(response.data.first_row[$key]);
                        } else {
                            $(this).parent().next().html('');
                        }

                    }

                });

                $.each(select, function () {
                    $(this).val($(this).attr('data-field')).change();
                });

                $(document.body).on('click', '.pl8app-import-proceed', function (e) {

                    e.preventDefault();

                    $form.append('<div class="notice-wrap"><span class="spinner is-active"></span><div class="pl8app-progress"><div></div></div></div>');

                    response.data.mapping = $form.serialize();

                    pl8app_Import.process_step(1, response.data, self);
                });

            } else {

                pl8app_Import.error(xhr);

            }

        },

        error: function (xhr) {

            // Something went wrong. This will display error on form

            var response = jQuery.parseJSON(xhr.responseText);
            var import_form = $('.pl8app-import-form').find('.pl8app-progress').parent().parent();
            var notice_wrap = import_form.find('.notice-wrap');

            import_form.find('.button-disabled').removeClass('button-disabled');

            if (response.data.error) {

                notice_wrap.html('<div class="update error"><p>' + response.data.error + '</p></div>');

            } else {

                notice_wrap.remove();

            }
        },

        process_step: function (step, import_data, self) {

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    form: import_data.form,
                    nonce: import_data.nonce,
                    class: import_data.class,
                    upload: import_data.upload,
                    mapping: import_data.mapping,
                    action: 'pl8app_do_ajax_import',
                    step: step,
                },
                dataType: "json",
                success: function (response) {

                    if ('done' == response.data.step || response.data.error) {

                        // We need to get the actual in progress form, not all forms on the page
                        var import_form = $('.pl8app-import-form').find('.pl8app-progress').parent().parent();
                        var notice_wrap = import_form.find('.notice-wrap');

                        import_form.find('.button-disabled').removeClass('button-disabled');

                        if (response.data.error) {

                            notice_wrap.html('<div class="update error"><p>' + response.data.error + '</p></div>');

                        } else {

                            import_form.find('.pl8app-import-options').hide();
                            $('html, body').animate({
                                scrollTop: import_form.parent().offset().top
                            }, 500);

                            notice_wrap.html('<div class="updated"><p>' + response.data.message + '</p></div>');

                        }

                    } else {

                        $('.pl8app-progress div').animate({
                            width: response.data.percentage + '%',
                        }, 50, function () {
                            // Animation complete.
                        });

                        pl8app_Import.process_step(parseInt(response.data.step), import_data, self);
                    }

                }
            }).fail(function (response) {
                if (window.console && window.console.log) {
                    console.log(response);
                }
            });

        }

    };
    pl8app_Import.init();

    /**
     * Customer management screen JS
     */
    var pl8app_Customer = {

        vars: {
            customer_card_wrap_editable: $('.pl8app-customer-card-wrapper .editable'),
            customer_card_wrap_edit_item: $('.pl8app-customer-card-wrapper .edit-item'),
            user_id: $('input[name="customerinfo[user_id]"]'),
            state_input: $(':input[name="customerinfo[state]"]'),
            note: $('#customer-note'),
        },
        init: function () {
            this.edit_customer();
            this.add_email();
            this.user_search();
            this.remove_user();
            this.cancel_edit();
            this.change_country();
            this.add_note();
            this.delete_checked();
        },
        edit_customer: function () {
            $(document.body).on('click', '#edit-customer', function (e) {
                e.preventDefault();

                pl8app_Customer.vars.customer_card_wrap_editable.hide();
                pl8app_Customer.vars.customer_card_wrap_edit_item.fadeIn().css('display', 'block');
            });
        },
        add_email: function () {
            $(document.body).on('click', '#add-customer-email', function (e) {
                e.preventDefault();
                var button = $(this);
                var wrapper = button.parent();

                wrapper.parent().find('.notice-container').remove();
                wrapper.find('.spinner').css('visibility', 'visible');
                button.attr('disabled', true);

                var customer_id = wrapper.find('input[name="customer-id"]').val();
                var email = wrapper.find('input[name="additional-email"]').val();
                var primary = wrapper.find('input[name="make-additional-primary"]').is(':checked');
                var nonce = wrapper.find('input[name="add_email_nonce"]').val();

                var postData = {
                    pl8app_action: 'customer-add-email',
                    customer_id: customer_id,
                    email: email,
                    primary: primary,
                    _wpnonce: nonce,
                };

                $.post(ajaxurl, postData, function (response) {

                    if (true === response.success) {
                        window.location.href = response.redirect;
                    } else {
                        button.attr('disabled', false);
                        wrapper.after('<div class="notice-container"><div class="notice notice-error inline"><p>' + response.message + '</p></div></div>');
                        wrapper.find('.spinner').css('visibility', 'hidden');
                    }

                }, 'json');

            });
        },
        user_search: function () {
            // Upon selecting a user from the dropdown, we need to update the User ID
            $(document.body).on('click.pl8appSelectUser', '.pl8app_user_search_results a', function (e) {
                e.preventDefault();
                var user_id = $(this).data('userid');
                pl8app_Customer.vars.user_id.val(user_id);
            });
        },
        remove_user: function () {
            $(document.body).on('click', '#disconnect-customer', function (e) {

                e.preventDefault();

                if (confirm(pl8app_vars.disconnect_customer)) {

                    var customer_id = $('input[name="customerinfo[id]"]').val();

                    var postData = {
                        pl8app_action: 'disconnect-userid',
                        customer_id: customer_id,
                        _wpnonce: $('#edit-customer-info #_wpnonce').val()
                    };

                    $.post(ajaxurl, postData, function (response) {

                        window.location.href = window.location.href;

                    }, 'json');
                }

            });
        },
        cancel_edit: function () {
            $(document.body).on('click', '#pl8app-edit-customer-cancel', function (e) {
                e.preventDefault();
                pl8app_Customer.vars.customer_card_wrap_edit_item.hide();
                pl8app_Customer.vars.customer_card_wrap_editable.show();

                $('.pl8app_user_search_results').html('');
            });
        },
        change_country: function () {
            $('select[name="customerinfo[country]"]').change(function () {
                var $this = $(this);
                var data = {
                    action: 'pl8app_get_states',
                    country: $this.val(),
                    field_name: 'customerinfo[state]'
                };
                $.post(ajaxurl, data, function (response) {
                    if ('nostates' == response) {
                        pl8app_Customer.vars.state_input.replaceWith('<input type="text" name="' + data.field_name + '" value="" class="pl8app-edit-toggles medium-text"/>');
                    } else {
                        pl8app_Customer.vars.state_input.replaceWith(response);
                    }
                });

                return false;
            });
        },
        add_note: function () {
            $(document.body).on('click', '#add-customer-note', function (e) {
                e.preventDefault();
                var postData = {
                    pl8app_action: 'add-customer-note',
                    customer_id: $('#customer-id').val(),
                    customer_note: pl8app_Customer.vars.note.val(),
                    add_customer_note_nonce: $('#add_customer_note_nonce').val()
                };

                if (postData.customer_note) {

                    $.ajax({
                        type: "POST",
                        data: postData,
                        url: ajaxurl,
                        success: function (response) {
                            $('#pl8app-customer-notes').prepend(response);
                            $('.pl8app-no-customer-notes').hide();
                            pl8app_Customer.vars.note.val('');
                        }
                    }).fail(function (data) {
                        if (window.console && window.console.log) {
                            console.log(data);
                        }
                    });

                } else {
                    var border_color = pl8app_Customer.vars.note.css('border-color');
                    pl8app_Customer.vars.note.css('border-color', 'red');
                    setTimeout(function () {
                        pl8app_Customer.vars.note.css('border-color', border_color);
                    }, 500);
                }
            });
        },
        delete_checked: function () {
            $('#pl8app-customer-delete-confirm').change(function () {
                var records_input = $('#pl8app-customer-delete-records');
                var submit_button = $('#pl8app-delete-customer');

                if ($(this).prop('checked')) {
                    records_input.attr('disabled', false);
                    submit_button.attr('disabled', false);
                } else {
                    records_input.attr('disabled', true);
                    records_input.prop('checked', false);
                    submit_button.attr('disabled', true);
                }
            });
        }

    };
    pl8app_Customer.init();

    // AJAX user search
    $('.pl8app-ajax-user-search').keyup(function () {
        var user_search = $(this).val();
        var exclude = '';

        if ($(this).data('exclude')) {
            exclude = $(this).data('exclude');
        }

        $('.pl8app-ajax').show();
        var data = {
            action: 'pl8app_search_users',
            user_name: user_search,
            exclude: exclude
        };

        document.body.style.cursor = 'wait';

        $.ajax({
            type: "POST",
            data: data,
            dataType: "json",
            url: ajaxurl,
            success: function (search_response) {

                $('.pl8app-ajax').hide();
                $('.pl8app_user_search_results').removeClass('hidden');
                $('.pl8app_user_search_results span').html('');
                $(search_response.results).appendTo('.pl8app_user_search_results span');
                document.body.style.cursor = 'default';
            }
        });
    });

    $(document.body).on('click.pl8appSelectUser', '.pl8app_user_search_results span a', function (e) {
        e.preventDefault();
        var login = $(this).data('login');
        $('.pl8app-ajax-user-search').val(login);
        $('.pl8app_user_search_results').addClass('hidden');
        $('.pl8app_user_search_results span').html('');
    });

    $(document.body).on('click.pl8appCancelUserSearch', '.pl8app_user_search_results a.pl8app-ajax-user-cancel', function (e) {
        e.preventDefault();
        $('.pl8app-ajax-user-search').val('');
        $('.pl8app_user_search_results').addClass('hidden');
        $('.pl8app_user_search_results span').html('');
    });

    if ($('#pl8app_dashboard_sales').length) {
        $.ajax({
            type: "GET",
            data: {
                action: 'pl8app_load_dashboard_widget'
            },
            url: ajaxurl,
            success: function (response) {
                $('#pl8app_dashboard_sales .inside').html(response);
            }
        });
    }

    $(document.body).on('keydown', '.customer-note-input', function (e) {
        if (e.keyCode == 13 && (e.metaKey || e.ctrlKey)) {
            $('#add-customer-note').click();
        }
    });

    $(document.body).on('click', 'a#pl8app-bundle-add-menuitem', function (e) {
        e.preventDefault();
        var menuitem_id = $('#pl8app_nobundle_menuitem_select').val();
        if(menuitem_id == '' || menuitem_id == 0) return;
        var postData = {
            action: 'pl8app_get_bundle_item',
            menuitem_id: menuitem_id,
            security: pl8app_vars.load_bundle_item_nonce,
        };
        console.log(postData);
        jQuery.ajax({
            type: "POST",
            data: postData,
            url: ajaxurl,
            success: function (response) {

                if (response !== undefined) {
                    $('.pl8app-add-menuitem-to-purchase.list ul').append(response.data.html);
                    check_discount_limit();
                }
            },
        });
    });

    $(document.body).on('click', '.list span.remove', function (e) {
        $(this).closest('li').remove();
        check_discount_limit();
    });

    $(document.body).on('input', 'input#_pl8app_bundled_discount', function (e) {
        check_discount_limit();
    });

    $(document.body).on('change', 'select[name="_pl8app_product_type"]', function () {

        if($(this).val() == 'bundle'){
            $('div.pl8app_bundled_field').removeClass('hidden');
            $('p.pl8app_price_field').addClass('hidden');
            $('p.pl8app_menuitem_vat_field ').addClass('hidden');
            $('div.chosen-container').css('width', '100%');
        }
        else{
            $('div.pl8app_bundled_field').addClass('hidden');
            $('p.pl8app_price_field').removeClass('hidden');
            $('p.pl8app_menuitem_vat_field ').removeClass('hidden');
        }
    });

    $('.pl8app-delivery-details .pl8app-order-date').datepicker({
        dateFormat: 'MM d, yy',
        changeYear: true,
        changeMonth: true,
        onSelect: function(dateText) {
            // console.log(dateText);
        }
    });

    pl8app_bundle_arrage();

});
function check_discount_limit(){
    var discount = jQuery('input#_pl8app_bundled_discount').val();

    var discount_limit= 0;
    jQuery('div.pl8app-add-menuitem-to-purchase.list ul li').each(function (i, e) {
        var bundle_price = jQuery(e).find('span.data').attr('data-price');
        if(bundle_price){
            discount_limit += parseFloat(bundle_price);
        }
    });

    if(isNaN(discount)) {
        jQuery('span.discount-error').html('Invalid format value!');
        jQuery('span.discount-error').removeClass('hidden');
    }
    else if(!isNaN(discount) && parseInt(discount) > discount_limit){
        jQuery('span.discount-error').html('Invalid! discount is over the limit(<span class="bundle-total">' + discount_limit + '</span>).')
        jQuery('span.discount-error').removeClass('hidden');
        // console.log(!isNaN(discount) && parseInt(discount) > discount_limit);
    }
    else {
        if(!jQuery('span.discount-error').hasClass('hidden')) jQuery('span.discount-error').addClass('hidden');
    }
}
/**
 * drag and arrange the rows
 */
function pl8app_bundle_arrage() {
    jQuery('.pl8app-add-menuitem-to-purchase.list li').arrangeable({
        dragEndEvent: 'woosb_drag_event',
        dragSelector: '.move',
    });
}

// Graphing Helper Functions
var pl8appFormatCurrency = function (value) {
    // Convert the value to a floating point number in case it arrives as a string.
    var numeric = parseFloat(value);
    // Specify the local currency.
    var storeCurrency = pl8app_vars.currency;
    var decimalPlaces = pl8app_vars.currency_decimals;
    return numeric.toLocaleString(storeCurrency, {
        style: 'currency',
        currency: storeCurrency,
        minimumFractionDigits: decimalPlaces,
        maximumFractionDigits: decimalPlaces
    });
}

var pl8appFormatNumber = function (value) {
    // Convert the value to a floating point number in case it arrives as a string.
    var numeric = parseFloat(value);
    // Specify the local currency.
    var storeCurrency = pl8app_vars.currency;
    var decimalPlaces = pl8app_vars.currency_decimals;
    return numeric.toLocaleString(storeCurrency, {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

var pl8appLabelFormatter = function (label, series) {
    return '<div style="font-size:12px; text-align:center; padding:2px">' + label + '</div>';
}

var pl8appLegendFormatterSales = function (label, series) {
    var slug = label.toLowerCase().replace(/\s/g, '-');
    var color = '<div class="pl8app-legend-color" style="background-color: ' + series.color + '"></div>';
    var value = '<div class="pl8app-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + pl8appFormatNumber(series.data[0][1]) + ')</div>';
    var item = '<div id="' + series.pl8app_vars.id + slug + '" class="pl8app-legend-item-wrapper">' + color + value + '</div>';

    jQuery('#pl8app-pie-legend-' + series.pl8app_vars.id).append(item);
    return item;
}

var pl8appLegendFormatterEarnings = function (label, series) {
    var slug = label.toLowerCase().replace(/\s/g, '-');
    var color = '<div class="pl8app-legend-color" style="background-color: ' + series.color + '"></div>';
    var value = '<div class="pl8app-pie-legend-item">' + label + ': ' + Math.round(series.percent) + '% (' + pl8appFormatCurrency(series.data[0][1]) + ')</div>';
    var item = '<div id="' + series.pl8app_vars.id + slug + '" class="pl8app-legend-item-wrapper">' + color + value + '</div>';

    jQuery('#pl8app-pie-legend-' + series.pl8app_vars.id).append(item);
    return item;
}

jQuery(document).on('change', '.pl8app-get-variable-prices input[type=radio]', function () {
    var selectedPrice = jQuery(this).val();
    jQuery(this).parents('.pl8app-get-variable-prices').find('.pl8app_selected_price').val(selectedPrice);
});

//Get Option and Upgrade items in the admin order
function pl8app_get_addon_items_list(menuitem_id, clone) {

    if (parseInt(menuitem_id) > 0) {

        var Options;
        var postData = {
            action: 'pl8app_admin_order_addon_items',
            menuitem_id: menuitem_id,
            security: pl8app_vars.load_admin_addon_nonce,
        };

        clone.find('select').html();

        jQuery.ajax({
            type: "POST",
            data: postData,
            url: ajaxurl,
            success: function (response) {
                if (response !== undefined) {
                    clone.find('select.addon-items-list').html(response);
                    clone.find('div.chosen-container').last().remove();
                    clone.find('select').chosen();
                }
            },
        });
    }
}

function pl8app_attach_tooltips(selector) {
    // Tooltips
    selector.tooltip({
        content: function () {
            return jQuery(this).prop('title');
        },
        tooltipClass: 'pl8app-ui-tooltip',
        position: {
            my: 'center top',
            at: 'center bottom+10',
            collision: 'flipfit'
        },
        hide: {
            duration: 200
        },
        show: {
            duration: 200
        }
    });
}

if (typeof auto_print_processing === 'undefined') {
    // variable is undefined

   var auto_print_processing = false;
}

if (pl8app_vars.order_auto_print_enable == 1 && pl8app_vars.is_admin == 1){
    var WinPrint = window.open('', 'pl8app_auto_print', 'width=1,height=1');
    if(WinPrint !=null && WinPrint.document) {
        WinPrint.document.title = 'pl8app Auto Printing --Kiosk Mode'
    }
}


jQuery(function ($) {

    $('body').on('click', 'button.add-new-faq.alignright', function () {
        var $content = '<div class="pl8app-addon pl8app-metabox faq-row-container">\n' +
            '                                    <h3>\n' +
            '                                        <a href="#" class="remove_row delete"><span class="dashicons dashicons-remove"></span></a>\n' +
            '                                    </h3>\n' +
            '                                    <div class="pl8app-metabox-content">\n' +
            '                                        <div class="pl8app-grid-row">\n' +
            '                                            <label>Question: <textarea name="pl8app_settings[faq][question][]"\n' +
            '                                                                       class="large-text"></textarea></label>\n' +
            '                                        </div>\n' +
            '                                        <div class="pl8app-grid-row">\n' +
            '                                            <label>Answer: <textarea name="pl8app_settings[faq][answer][]"\n' +
            '                                                                     class="large-text"></textarea></label>\n' +
            '                                        </div>\n' +
            '                                    </div>\n' +
            '                                </div>';
        $('div.faq-container').append($content);
    });

    $('body').on('click', 'h3 a.remove_row.delete', function (e) {
        $(this).closest('div.faq-row-container').remove();
        e.preventDefault();
    });


    if (pl8app_vars.is_admin == 1 && pl8app_vars.enable_order_notification == 1) {

        var $info_notification = $("#pl8app_check_sound_notification");
        $info_notification.dialog({
            title: 'Notification Sound Enable',
            dialogClass: 'wp-dialog',
            autoOpen: false,
            draggable: false,
            width: 'auto',
            modal: true,
            resizable: false,
            closeOnEscape: true,
            position: {
                my: "center",
                at: "center",
                of: window
            },
            open: function () {// close dialog by clicking the overlay behind it
                $('.ui-widget-overlay').bind('click', function(){
                    $('#pl8app_check_sound_notification').dialog('close');
                })
                $('.ui-widget-content').css({'z-index': 2});
                $('.ui-widget-overlay').css({'z-index': 1});
                $('.ui-widget-overlay').css({'position': 'fixed'});
                $('.ui-dialog .ui-dialog-titlebar-close').css({'width' : '40px'});
                $('.ui-button-icon').remove();
            },
            close: function(){
                if (pl8app_vars.order_auto_print_enable == 1){
                    WinPrint = window.open('', 'pl8app_auto_print', 'width=1,height=1');
                    if(WinPrint.document) {
                        WinPrint.document.title = 'pl8app Auto Printing --Kiosk Mode'
                    }
                }
            }
        });

        /**
         *  Async print process
         */
        var async_print_new_order = (content) => {
            return new Promise((resolve, reject) => {

                try{
                    WinPrint.document.write(content);
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
        };

        // Add iframe for autoplay
        var audio = new Audio(pl8app_vars.notification_sound);
        var promise = audio.play();


        if (promise !== undefined) {
            promise.then(_ => {
                audio.pause();
                //console.log('pause');

            }).catch(error => {
                // Autoplay was prevented.
                // Show a "Play" button so that user can start playback.
                console.log(error.message)
                $info_notification.dialog('open');
            });
        }

        $(document.body).on('click', '#_pl8app_copies_per_print_start', async function () {
            try{

                var loops = parseInt($('#_pl8app_copies_per_print').val());
                var printContent = document.getElementById('pl8app_new_order_print_content');
                for(var i = 0 ; i < loops; i ++) {
                    await async_print_new_order(printContent.innerHTML);
                }
                auto_print_processing = false;
            }
            catch (e) {
            }
        });

        if (typeof Notification !== "undefined") {
            setInterval(function () {
                if(auto_print_processing) return
                $.ajax({
                    type: 'POST',
                    data: {
                        action: 'pl8app_check_new_orders'
                    },
                    url: ajaxurl,
                    success: function (response) {
                        if (response.title) {

                            Notification.requestPermission().then(function (result) {
                                if (result === 'denied') {
                                    // console.log('Permission wasn\'t granted. Allow a retry.');
                                    return;
                                }

                                if (result === 'default') {
                                    // console.log('The permission request was dismissed.');
                                    return;
                                }

                                if (typeof response.title === "undefined") return;

                                var notifyTitle = response.title;
                                var options = {
                                    body: 'New Order is just placed!',
                                    icon: pl8app_vars.pl8app_icon,
                                    sound: pl8app_vars.notification_sound,
                                };
                                var n = new Notification(notifyTitle, options);
                                n.custom_options = {
                                    url: response.url,
                                };
                                n.onclick = function (event) {
                                    event.preventDefault(); // prevent the browser from focusing the Notification's tab
                                    window.open(n.custom_options.url, '_blank');
                                };
                            });

                            if($('.pl8app_notify_audio').length){
                                $('.pl8app_notify_audio').remove();
                            }
                            //add audio notify because, this property is not currently supported in any browser.
                            if (pl8app_vars.notification_sound != '') {
                                $("<audio autoplay controls loop class='pl8app_notify_audio' style='visibility: hidden;'></audio>").attr({
                                    'src': pl8app_vars.notification_sound,
                                }).appendTo("body");
                                $('.pl8app_notify_audio').trigger("play");

                                if(pl8app_vars.order_auto_print_enable && response.print_content && response.copies_per_print){

                                    $('#_pl8app_copies_per_print').val(response.copies_per_print);
                                    $('#pl8app_new_order_print_content').html(response.print_content);
                                    auto_print_processing = true;
                                    $('#_pl8app_copies_per_print_start').click();

                                }
                            }
                            //Increase the new Order counts
                            !$('#wp-admin-bar-new-order').length? $('ul#wp-admin-bar-root-default').append('<li id="wp-admin-bar-new-order"><a class="ab-item" href="' + pl8app_vars.order_list_page +'"><span class="ab-icon dashicons dashicons-bell" style="background: #E61F64 !important;"></span>New Order!</a></li>'):'';
                        }
                    },
                    complete: function () {
                    }
                });
            }, 10000);

        }
    }
});
