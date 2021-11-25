

<li class="pl8app-cart-item" data-cart-key="{cart_item_id}">
	<span class="pl8app-cart-item-qty qty-class">{item_qty}</span>
	<span class="separator">x</span>
	<span class="pl8app-cart-item-title">{item_title}</span>&nbsp;
    <span class="pl8app-cart-item-tax-class"><p style="width: 100px;">{tax_name}</p></span>
	<span class="cart-item-quantity-wrap">
		<span class="pl8app-cart-item-price qty-class">{item_amount}</span>
	</span>

	<div>{addon_items}</div>
	<span class="pl8app-special-instruction">{special_instruction}</span>
	<div>
		<span class="cart-action-wrap">
			<a class="pl8app-edit-from-cart" data-cart-item="{cart_item_id}" data-item-name="{item_title}" data-item-id="{item_id}" data-item-price="{item_amount}" data-remove-item="{edit_menu_item}">Edit</a>
			<a href="{remove_url}" data-cart-item="{cart_item_id}" data-menuitem-id="{item_id}" data-action="pl8app_remove_from_cart" class="pl8app-remove-from-cart">Remove</a>
		</span>
	</div>
</li>