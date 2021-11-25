jQuery(function ($) {

    if ($('table#pl8app_store_timings').length) {
        var Selected = $('table#pl8app_store_timings');
        Selected.parents('.form-table').find('th').addClass('pl8app-headings');
    }


    $(document.body).on('click', '.st_checkbox', function () {
        if ($(this).is(":checked")) {
            $(this).val('enabled');
            $(this).closest('.pl8app_store_timings_day').find('.st_day_timetable_wrapper').removeClass('hidden');
            $(this).closest('.pl8app_st_days_checkbox').find('.checkbox_stat_label.open').removeClass('hidden');
            $(this).closest('.pl8app_st_days_checkbox').find('.checkbox_stat_label.closed').addClass('hidden');
        }
        else {
            $(this).val('');
            $(this).closest('.pl8app_store_timings_day').find('.st_day_timetable_wrapper').addClass('hidden');
            $(this).closest('.pl8app_st_days_checkbox').find('.checkbox_stat_label.open').addClass('hidden');
            $(this).closest('.pl8app_st_days_checkbox').find('.checkbox_stat_label.closed').removeClass('hidden');
        }
    });

    $(document.body).on('click', '.inventory_setting_checkbox_wrapper', function () {
        if ($(this).find('input[name="pl8app_item_stock_enable"]').is(":checked")) {
            $(this).find('input[name="pl8app_item_stock_enable"]').prop('checked', false);
            $(this).closest('.pl8app-metabox-container').find('div.inventory_setting').addClass('hidden');
        }
        else {
            $(this).find('input[name="pl8app_item_stock_enable"]').prop('checked', true);
            $(this).closest('.pl8app-metabox-container').find('div.inventory_setting').removeClass('hidden');
        }
    });


    $(document.body).on('click', '.checkbox_stat_label.24hours input', function () {
        if ($(this).is(":checked")) {
            $(this).val('enabled');
            $(this).closest('.st_day_timetable_wrapper').find('.st_day_timetable').addClass('hidden');
            $(this).closest('.st_day_timetable_wrapper').find('.st_day_time_add').addClass('hidden');
        }
        else {
            $(this).val('');
            $(this).closest('.pl8app_store_timings_day').find('.st_day_timetable').removeClass('hidden');
            $(this).closest('.pl8app_store_timings_day').find('.st_day_time_add').removeClass('hidden');
        }
    });

    $(document.body).on('change', '.ui-timepicker-input', function () {
        var empty_statue = $(this).closest('.st_day_timetable').find('input[name^="pl8app_store_timing"]').filter(
            (i, e) => {
                return $.trim($(e).val()).length == 0;
            }
        ).length;
        if (empty_statue > 0) {
            $(this).closest('.st_day_timetable_wrapper').find('.st_day_time_add').addClass('hidden');
        }
        else {
            $(this).closest('.st_day_timetable_wrapper').find('.st_day_time_add').removeClass('hidden');
        }

    });
    $(document.body).on('click', '.store_open_time .st_day_time_add_new_line', function () {
        var dayname = $(this).closest('.st_day_timetable_wrapper').data('day');

        var unqiue_pattern = /\[(\d+)\]/i;
        var current_indexArray = [];

        $(this).closest('.st_day_timetable_wrapper').find('input.storetime:even').each((i, e) => {
            current_indexArray.push(parseInt($(e).attr('name').match(unqiue_pattern)[1]));
        });
        let row_i = 0;
        while (current_indexArray.includes(row_i)) {
            row_i++;
        }
        var new_row = '<div class="st_day_time">\n' +
            '<div class="pl8app_st_open_time">\n' +
            '<input type="text" class="pl8app storetime ui-timepicker-input"\n' +
            '                                           name="pl8app_store_timing[open_time][' + dayname + '][' + row_i + ']">\n' +
            '</div>\n' +
            '<div class="pass_stion">-</div>\n' +
            '<div class="pl8app_st_close_time">\n' +
            '<input type="text" class="pl8app storetime ui-timepicker-input"\n' +
            '    name="pl8app_store_timing[close_time][' + dayname + '][' + row_i + ']">\n' +
            '</div>\n' +
            '<div class="st_day_time_remove hidden">\n' +
            '<span class="st_remove_time_icon"><span class="Ce1Y1c" style="top: -12px"><svg\n' +
            '    xmlns="https://www.w3.org/2000/svg" width="24" height="24"\n' +
            '    viewBox="0 0 24 24"><path\n' +
            '    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path><path\n' +
            '    d="M0 0h24v24H0z" fill="none"></path></svg></span></span>\n' +
            '</div>\n' +
            '</div>';
        $(this).closest('.st_day_timetable_wrapper').find('.st_day_timetable').append(new_row);
        $(this).closest('.st_day_time_add').addClass('hidden');
        $(this).closest('.st_day_timetable_wrapper').find('.st_day_time_remove').removeClass('hidden');
        rearrange_timepicker($(this).closest('.st_day_timetable_wrapper'));
    });

    $(document.body).on('click', '.menu_available_time .st_day_time_add_new_line', function () {
        var dayname = $(this).closest('.st_day_timetable_wrapper').data('day');

        var unqiue_pattern = /\[(\d+)\]/i;
        var current_indexArray = [];

        $(this).closest('.st_day_timetable_wrapper').find('input.storetime:even').each((i, e) => {
            current_indexArray.push(parseInt($(e).attr('name').match(unqiue_pattern)[1]));
        });
        let row_i = 0;
        while (current_indexArray.includes(row_i)) {
            row_i++;
        }
        var new_row = '<div class="st_day_time">\n' +
            '<div class="pl8app_st_open_time">\n' +
            '<input type="text" class="pl8app storetime ui-timepicker-input"\n' +
            '                                           name="pl8app_menuitem_timing[open_time][' + dayname + '][' + row_i + ']">\n' +
            '</div>\n' +
            '<div class="pass_stion">-</div>\n' +
            '<div class="pl8app_st_close_time">\n' +
            '<input type="text" class="pl8app storetime ui-timepicker-input"\n' +
            '    name="pl8app_menuitem_timing[close_time][' + dayname + '][' + row_i + ']">\n' +
            '</div>\n' +
            '<div class="st_day_time_remove hidden">\n' +
            '<span class="st_remove_time_icon"><span class="Ce1Y1c" style="top: -12px"><svg\n' +
            '    xmlns="https://www.w3.org/2000/svg" width="24" height="24"\n' +
            '    viewBox="0 0 24 24"><path\n' +
            '    d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path><path\n' +
            '    d="M0 0h24v24H0z" fill="none"></path></svg></span></span>\n' +
            '</div>\n' +
            '</div>';
        $(this).closest('.st_day_timetable_wrapper').find('.st_day_timetable').append(new_row);
        $(this).closest('.st_day_time_add').addClass('hidden');
        $(this).closest('.st_day_timetable_wrapper').find('.st_day_time_remove').removeClass('hidden');
        rearrange_timepicker($(this).closest('.st_day_timetable_wrapper'));
    });

    $(document.body).on('click', '.store_delivery_cut .st_day_time_add_new_line', function () {
        var dayname = $(this).closest('.st_day_timetable_wrapper').data('day');
        var unqiue_pattern = /\[(\d+)\]/i;
        var current_indexArray = [];

        $(this).closest('.st_day_timetable_wrapper').find('input.storetime:even').each((i, e) => {
            current_indexArray.push(parseInt($(e).attr('name').match(unqiue_pattern)[1]));
        });
        let row_i = 0;
        while (current_indexArray.includes(row_i)) {
            row_i++;
        }
        var new_row = '<div class="st_day_time">\n' +
            '                                <div class="pl8app_st_open_time">\n' +
            '                                    <input type="text" class="pl8app storetime ui-timepicker-input"\n' +
            '                                           name="pl8app_settings[delivery_timing][open_time][' + dayname + '][' + row_i + ']">\n' +
            '                                </div>\n' +
            '                                <div class="pass_stion">-</div>\n' +
            '                                <div class="pl8app_st_close_time">\n' +
            '                                    <input type="text" class="pl8app storetime ui-timepicker-input"\n' +
            '                                           name="pl8app_settings[delivery_timing][close_time][' + dayname + '][' + row_i + ']">\n' +
            '                                </div>\n' +
            '                                <div class="st_day_time_remove hidden">\n' +
            '                                    <span class="st_remove_time_icon"><span class="Ce1Y1c" style="top: -12px"><svg\n' +
            '                                                    xmlns="https://www.w3.org/2000/svg" width="24" height="24"\n' +
            '                                                    viewBox="0 0 24 24"><path\n' +
            '                                                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path><path\n' +
            '                                                        d="M0 0h24v24H0z" fill="none"></path></svg></span></span>\n' +
            '                                </div>\n' +
            '                            </div>';
        $(this).closest('.st_day_timetable_wrapper').find('.st_day_timetable').append(new_row);
        $(this).closest('.st_day_time_add').addClass('hidden');
        $(this).closest('.st_day_timetable_wrapper').find('.st_day_time_remove').removeClass('hidden');
        rearrange_timepicker($(this).closest('.st_day_timetable_wrapper'));
    });
    $(document.body).on('click', '.st_day_time_remove', function () {
        var parent = $(this).closest('.st_day_timetable_wrapper');
        $(this).closest('.st_day_time').remove();

        var day_row_num = parent.find('.st_day_time').length;

        if (day_row_num < 2) {
            parent.find('.st_day_timetable .st_day_time_remove').each((i, e) => $(e).addClass('hidden'));
        }
        var empty_statue = parent.find('.st_day_timetable input[name^="pl8app_store_timing"]').filter(
            (i, e) => {
                return $.trim($(e).val()).length == 0;
            }
        ).length;
        if (empty_statue > 0) {
            parent.find('.st_day_time_add').addClass('hidden');
        }
        else {
            parent.find('.st_day_time_add').removeClass('hidden');
        }
    });


    var date = new Date();
    date.setDate(date.getDate());

    //Add datepicker to the holiday fields
    $('.pl8app-holiday-date').datepicker({
        dateFormat: 'd MM, yy',
        startDate: date,
        minDate: 0
    });

    //pl8app Add Holiday
    $('body').on('click', '.pl8app-add-holiday', function (e) {
        e.preventDefault();
        var Selected = $(this);

        Row = Selected.parent('.holidays-wrap').find('.holidays-single-list').length + 1;

        if (Row > 1) {
            LastRow = Selected.parent('.holidays-wrap').find('.holidays-single-list').last().attr('data-row');
            Row = parseInt(LastRow) + 1;
        }

        CustomHtml = '<div class="holidays-single-list" data-row="' + Row + '">';
        CustomHtml += '<input type="text" class="pl8app-holiday-date" data-name="pl8app_store_timing[holiday][' + Row + ']" name="pl8app_store_timing[holiday][' + Row + ']">';
        CustomHtml += '<button class="button button-primary pl8app-remove-holiday">' + pl8appStoreTime.remove_holiday + '';
        CustomHtml += '</button>';

        Selected.parent('.holidays-wrap').find('.holidays-lists-single-wrap.child').append(CustomHtml);

        $('div[data-row="' + Row + '"] .pl8app-holiday-date').datepicker({
            dateFormat: 'd MM, yy',
            startDate: date,
            minDate: 0
        });
    });

    //pl8app Remove Holiday
    $('body').on('click', '.pl8app-remove-holiday', function (e) {
        e.preventDefault();
        $(this).parent('.holidays-single-list').remove();
    });

    $('body').on('change', 'input.pl8app.storetime',async function (e) {
        let container = $(this).closest('div.pl8app_store_timings_day');
        // await new Promise((resolve) => {
        //     var length =  $('div.ui-timepicker-wrapper').length;
        //     $('div.ui-timepicker-wrapper').each((i,e)=>{
        //         $(e).remove();
        //         length == i + 1 ?resolve():'';
        //     })
        //
        // });
        rearrange_timepicker(container);
    });


    function rearrange_timepicker(container){

        var length = $(container).find('input.pl8app.storetime').length;
        var minTime = '00:00';
        var maxTime = '23:30';
        // console.log('------------', length);
        $(container).find('input.pl8app.storetime').each((i, e) => {

            if(i == 0){
                maxTime = find_closest_maxValue(1, length, container);
                $(e).timepicker({
                    timeFormat: 'H:i',
                    maxTime: addMinutes(maxTime, -30),
                    dynamic: true
                });
                if(!(Date.parse('01/01/2011 '+$(e).val()) <= Date.parse('01/01/2011 '+ maxTime) && Date.parse('01/01/2011 '+$(e).val()) >= Date.parse('01/01/2011 '+ minTime))){
                    $(e).val('');
                }
            }
            if(length > 2 && i > 0 && i < length - 1){
                minTime = find_closest_minValue(i - 1, container);
                maxTime = find_closest_maxValue(i + 1, length, container);
                $(e).timepicker({
                    timeFormat: 'H:i',
                    maxTime: addMinutes(maxTime, -30),
                    minTime: addMinutes(minTime, 30),
                    dynamic: true
                });
                if(!(Date.parse('01/01/2011 '+$(e).val()) <= Date.parse('01/01/2011 '+ maxTime) && Date.parse('01/01/2011 '+$(e).val()) >= Date.parse('01/01/2011 '+ minTime))){
                    $(e).val('');
                }
            }
            if(i == length - 1){
                minTime = find_closest_minValue(i - 1, container);
                maxTime = '23:30';
                $(e).timepicker({
                    timeFormat: 'H:i',
                    minTime: addMinutes(minTime, 30),
                    dynamic: true
                });
                if(!(Date.parse('01/01/2011 '+$(e).val()) <= Date.parse('01/01/2011 '+ maxTime) && Date.parse('01/01/2011 '+$(e).val()) >= Date.parse('01/01/2011 '+ minTime))){
                    $(e).val('');
                }
            }
        });
    }

    function addMinutes(time, minsToAdd) {
        function D(J){ return (J<10? '0':'') + J;};
        var piece = time.split(':');
        var mins = parseInt(piece[0]*60) + parseInt(piece[1]) + parseInt(minsToAdd);

        return D(mins%(24*60)/60 | 0) + ':' + D(mins%60);
    }

    function find_closest_minValue($index, container){
        let i = $index;

        while($($(container).find('input.pl8app.storetime')[i]).val() == null || $($(container).find('input.pl8app.storetime')[i]).val() == ''){
            if(i == 0) return '00:00';
            i--;
        }
        return $($(container).find('input.pl8app.storetime')[i]).val();
    }

    function find_closest_maxValue($index, length, container){
        let i = $index;

        while($($(container).find('input.pl8app.storetime')[i]).val() == null || $($(container).find('input.pl8app.storetime')[i]).val() == ''){
            if(i == length - 1) return '23:30';
            i++;
        }
        return $($(container).find('input.pl8app.storetime')[i]).val();
    }
    $('div.pl8app_store_timings_day').each((i,e) => {
        rearrange_timepicker(e);
    });

});