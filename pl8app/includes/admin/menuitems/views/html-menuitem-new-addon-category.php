<?php
/**
 * Menu Item create new addon category html.
 *
 * @package pl8app/Admin
 */

defined( 'ABSPATH' ) || exit;

$row = isset( $_POST['i'] ) ?  $_POST['i'] : 0;

?>

<!-- Create new addon form starts -->
<div class="pl8app-addon pl8app-metabox create-new-addon">
	<h3>
		<a href="#" class="remove_row delete">Remove</a>
		<div class="tips sort" data-tip="<?php esc_html_e( 'Drag Drop to reorder the addon categories.', 'pl8app' );?>"></div>
		<strong class="addon_category_name">
			<?php esc_html_e( 'Create New Options and Upgrades Category', 'pl8app' ); ?>
		</strong>
	</h3>
	<div class="pl8app-metabox-content">

    <div class="pl8app-metabox-content-wrapper">
      <div class="pl8app-col-6 addon-category">
        <table class="form-table">
          <thead>
            <tr>
              <th scope="row">
                <?php esc_html_e( 'Addon Category:', 'default' ); ?>
              </th>
              <th scope="row">
                <?php esc_html_e( 'Type:', 'default' ); ?>
              </th>
            </tr>
          </thead>
          <tbody>
            <td>
              <input type="text" name="addon_category[<?php echo $row; ?>][name]" id="" class="pl8app-input addon-category-name" placeholder="<?php esc_html_e( 'Addon Category Name', 'pl8app' ); ?>">
            </td>
            <td>
              <select name="addon_category[<?php echo $row; ?>][type]" class="pl8app-input addon-category-type ttt">
                <?php
                  foreach ( $addon_types as $k => $type ){
                    echo '<option value="' . $k .'">' .$type .'</option>';
                  }
                ?>
              </select>
            </td>
          </tbody>
        </table>
      </div>

      <div class="pl8app-col-6 addon-items">
        <table class="form-table">
          <thead>
            <tr>
              <th scope="row">
                <?php esc_html_e( 'Options and Upgrades Items:', 'pl8app' ); ?>
              </th>
              <th scope="row" class="addon-price-symbol">
                <?php echo sprintf( __( 'Price (%s)', 'pl8app' ), pl8app_currency_symbol() ); ?>
              </th>
              <th scope="row">&nbsp</th>
            </tr>
          </thead>
          <tbody>
            <tr class="addon-items-row">
              <td>
                <input type="text" name="addon_category[<?php echo $row; ?>][addon_name][]" class="pl8app-input" placeholder="<?php esc_html_e( 'Addon Item Name', 'pl8app' ); ?>">
              </td>
              <td>
                <input type="text" name="addon_category[<?php echo $row; ?>][addon_price][]" class="pl8app-input pl8app-addon-price" placeholder="9.99">
              </td>
              <td>
                <span class="remove pl8app-addon-cat">
                  <span class="dashicons dashicons-dismiss"></span>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="clear"></div>
    </div>

		<div class="toolbar-bottom toolbar">
      <button type="button" class="button button-primary add-new-addon alignright add-addon-multiple-item"> + <?php esc_html_e( 'Add New', 'pl8app' ); ?></button>
    </div>
	</div>
</div>
<!-- Create new addon form ends -->