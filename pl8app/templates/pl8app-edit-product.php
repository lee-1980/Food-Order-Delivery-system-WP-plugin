<div class="menuitem-description">{itemdescription}</div>
<div class="view-menu-item-wrap">
	<form id="menuitem-update-details" class="row">{menuitemslist}</form>
	<div class="clear"></div>
	<?php if( apply_filters( 'pl8app_special_instructions', true ) ) : ?>
	<div class="pl8app-col-md-12 md-12-top special-inst">
		<a href="#" class="special-instructions-link">
			<?php echo apply_filters( 'pl8app_special_instruction_text', __('Special Instructions?', 'pl8app' ) ); ?>
		</a>
		<textarea placeholder="<?php _e( 'e.g. allergies, extra spicy, etc.', 'pl8app' ); ?>" class="pl8app-col-md-12 special-instructions" name="special_instruction">{cartinstructions}</textarea>
	</div>
	<?php endif; ?>
</div>
