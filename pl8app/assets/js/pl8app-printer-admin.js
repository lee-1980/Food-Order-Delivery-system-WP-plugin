var ajax_url = pl8app_printer_vars.ajaxUrl;

jQuery(document).ready(function(){

	jQuery('.pl8app_print_now').on('click',function(){
        var payment_id = jQuery(this).data('payment-id');
        jQuery('#print-display-area-' + payment_id).load( ajax_url + '?action=pl8app_print_payment_data&payment_id=' + payment_id,function(){

            var printContent = document.getElementById('print-display-area-' + payment_id);
            var WinPrint = window.open('', '', 'width=900,height=650');
            WinPrint.document.write(printContent.innerHTML);
            WinPrint.document.close();

            setTimeout(function () {

                WinPrint.focus();
                WinPrint.print();
                WinPrint.close();

            }, 200);
        });
    });
});