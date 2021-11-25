
<div class="menuitem-description">{itemdescription}</div>
<div class="view-menu-item-wrap">
	<form id="menuitem-details">{menuitemslist}</form>
	<div class="clear"></div>
    <div class="bundled-menu-item-content {hidden}">{bundleditems}</div>
	<div class="pl8app-col-md-12 md-4-top special-margin">
		<a href="#" class="special-instructions-link">
			<?php echo apply_filters('pl8app_special_instruction_text', __('Special Instructions?', 'pl8app')); ?>
		</a>
		<textarea placeholder="<?php esc_html_e('Add Instructions...', 'pl8app') ?>" class="pl8app-col-md-12 special-instructions " name="special_instruction"></textarea>
	</div>
</div>