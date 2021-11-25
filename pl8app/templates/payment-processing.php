<div id="pl8app-payment-processing">
	<p><?php printf( __( 'Your order is processing. This page will reload automatically in 8 seconds. If it does not, click <a href="%s">here</a>.', 'pl8app' ), pl8app_get_success_page_uri() ); ?>
	<span class="pl8app-cart-ajax"><i class="pl8app-icon-spinner pl8app-icon-spin"></i></span>
	<script type="text/javascript">setTimeout(function(){ window.location = '<?php echo pl8app_get_success_page_uri(); ?>'; }, 8000);</script>
</div>