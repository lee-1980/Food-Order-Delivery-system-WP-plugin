<?php if ( ! pl8app_has_variable_prices( get_the_ID() ) ) : ?>
	<?php $item_props = pl8app_add_schema_microdata() ? ' itemprop="offers" itemscope itemtype="http://schema.org/Offer"' : ''; ?>
	<div<?php echo $item_props; ?>>
		<div itemprop="price" class="pl8app_price">
			<?php pl8app_price( get_the_ID() ); ?>
		</div>
	</div>
<?php endif; ?>
