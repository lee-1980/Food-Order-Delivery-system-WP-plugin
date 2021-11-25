<?php
/**
 * Menu Item category data panel.
 *
 * @package pl8app/Admin
 */

defined( 'ABSPATH' ) || exit;
$categories = pl8app_get_categories( array( 'hide_empty' => false ) );
$menu_categories = $menuitem_object->get_menu_categories();

?>
<div id="category_menuitem_data" class="panel pl8app_options_panel">
	<div class="pl8app-metabox-container">
		<div class="toolbar toolbar-top">
			<span class="pl8app-toolbar-title">
				<?php esc_html_e( 'Menu Category', 'pl8app' ); ?>
			</span>
		</div>
		<div class="options_group pl8app-category">
			<div class="pl8app-metabox">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label><?php esc_html_e( 'Select Category', 'pl8app' ); ?></label>
							</th>
							<td class="pl8app-select-category">
								<select name="menu_categories[]" class="pl8app-category-select pl8app-select2" multiple="multiple">
									<?php foreach ( $categories as $category ){
										echo '<option ' . pla_selected( $category->term_id, $menu_categories ) . ' value="' . $category->term_id .'">' .$category->name .'</option>';
									}
									?>
								</select> <?php echo pla_help_tip( __( 'Select the menu categories you would like to assign to this menu item. This will be used for the filtering on the menu items list page.', 'pl8app' ) ); ?>
							</td>
						</tr>
						<tr class="pl8app-add-category" style="display: none;">
							<th scope="row"></th>
							<td>
								<input type="text" class="pl8app-input" name="pla_category" id="pl8app-category-name" placeholder="<?php esc_html_e( 'Enter Category Name', 'pl8app' ); ?>">
								<select name="_parent_category" id="pl8app-parent-category" class="pl8app-input pl8app-select2">
									<option value="">
										<?php esc_html_e( 'Parent Category', 'pl8app' ); ?>
									</option>
									<?php foreach ( $categories as $category ){
										echo '<option value="' . $category->term_id .'">' .$category->name .'</option>';
									}
									?>
								</select>
								<button type="button" class="button add-category alignright">
									<?php esc_html_e( 'Save Changes', 'pl8app' ); ?>
								</button>
							</td>
						</tr>
						<tr>
							<th scope="row"></th>
							<td class="alignright">
								<button type="button" class="button button-primary pla_add_category">
									<?php esc_html_e( ' + Add New Category', 'pl8app' ); ?>
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php do_action( 'pl8app_menuitem_categories' ); ?>
		</div>
	</div>
	<?php do_action( 'pl8app_menuitem_options_category_data' ); ?>
</div>