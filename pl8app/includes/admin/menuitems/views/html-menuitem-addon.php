<?php
/**
 * Menu Item Addons data panel.
 *
 * @package pl8app/Admin
 */

defined( 'ABSPATH' ) || exit;

$count 	  = !empty( $current ) ? $current : time();
$post_id  = get_the_ID();
$addons   = get_post_meta( $post_id, '_addon_items', true );
$variation_label = __( 'Variation', 'pl8app' );

if ( is_array( $addons ) && !empty( $addons ) ) :

  if( ! is_null( $post_id ) && pl8app_has_variable_prices( $post_id ) ) {
    $variation_label = get_post_meta( $post_id, 'pl8app_variable_price_label', true );
    $variation_label = ! is_null( $variation_label ) ? $variation_label : __( 'Variation', 'pl8app' );
  }

  foreach( $addons as $key => $addon_item ) :

    if( ! isset( $addon_item['category'] ) )
      continue;

    $addon_id = $addon_item['category'];

    if( isset( $addon_item['is_required'] ) && $addon_item['is_required'] == 'yes' ) {
      $is_required = 'checked';
    } else {
      $is_required = '';
    }

    ?>

    <!-- Addon category form starts -->
    <div class="pl8app-addon pl8app-metabox">

      <h3>
      	<a href="#" class="remove_row delete"><span class="dashicons dashicons-remove"></span></a>
        <strong><?php esc_html_e( 'Select Options and Upgrades', 'pl8app' ); ?></strong>
      </h3>

    	<div class="pl8app-metabox-content">
    		<div class="addon-category">
    			<select name="addons[<?php echo $key; ?>][category]" class=" pl8app-input pl8app-addon-lists " data-row-id="<?php echo $key; ?>">

            <?php if ( $addon_id == '' ) : ?>
              <option value="">
                <?php esc_html_e( 'Select Options and Upgrades Category', 'pl8app' ); ?>
              </option>
            <?php endif; ?>

    				<?php
    					foreach ( $addon_categories as $category ){
    						echo '<option data-name="'.$category->name.'" '.selected( $addon_item['category'], $category->term_id, false ).' value="' . $category->term_id .'">' .$category->name .'</option>';
    					}
    				?>
    			</select>
    			<button type="button" class="button load-addon" data-item-id=<?php echo isset($post_id)?$post_id:''; ?>>
    				<?php esc_html_e( 'Add', 'pl8app' ); ?>
    			</button>
          <label class="input_max_allowed">
            <?php esc_html_e( 'Max Selections?', 'pl8app' ); ?>
            <input type="number" name="addons[<?php echo $key; ?>][max_addons]" value="<?php echo isset($addon_item['max_addons']) ? $addon_item['max_addons'] : ''; ?>" />
          </label>
          <label class="cb_required">
            <input type="checkbox" name="addons[<?php echo $key; ?>][is_required]" value="yes" <?php echo $is_required; ?> />
            <?php esc_html_e( 'Required?', 'pl8app' ); ?>
            <span> | </span>
          </label>
    		</div>
    		<div class="addon-items">

          <?php
          $get_addons = pl8app_get_addons( $addon_id );
          if ( !empty( $addon_id ) && is_array( $get_addons ) && !empty( $get_addons ) ) : ?>

            <table class="pl8app-addon-items">
              <thead>
                <tr>
                  <th class="select_addon">
                    <strong>
                      <?php esc_html_e( 'Enable', 'pl8app' ); ?>
                    </strong>
                  </th>
                  <th class="addon_name">
                    <strong>
                      <?php esc_html_e( 'Options and Upgrades Name', 'pl8app' ); ?>
                    </strong>
                  </th>
                  <th class="variation_name">
                    <strong>
                      <?php echo $variation_label; ?>
                    </strong>
                  </th>
                  <th class="addon_price">
                    <strong>
                      <?php esc_html_e( 'Price', 'pl8app' ); ?>
                    </strong>
                  </th>
                </tr>
              </thead>
              <tbody>

              <?php foreach( $get_addons as $get_addon ) :

                $addon_item_id = $get_addon->term_id;
                $addon_item_name = $get_addon->name;
                $addon_slug = $get_addon->slug;
                $addon_price = pl8app_get_addon_data( $addon_item_id, 'price' );
                $addon_price = ! empty( $addon_price ) ? $addon_price : '0.00';

                $selected = '';
                $req_selected = '';

                if ( isset( $addon_item['items'] ) ) {
                  if ( in_array( $addon_item_id, $addon_item['items'] ) ) {
                    $selected = 'checked';
                  }
                }

                if ( isset( $addon_item['required'] ) ) {
                  if ( in_array( $addon_item_id, $addon_item['required'] ) ) {
                    $req_selected = 'checked';
                  }
                }

                if( pl8app_has_variable_prices( $post_id ) ) {

                  $count = 1;
                  foreach ( pl8app_get_variable_prices( $post_id ) as $price) {

                    $addon_price = !empty( $addon_item['prices'] ) && !empty( $addon_item['prices'][$addon_item_id][$price['name']] ) ? $addon_item['prices'][$addon_item_id][$price['name']] : $addon_price;

                    ?>

                    <tr class="pl8app-child-addon">
                      <?php if( $count == 1 ) { ?>
                        <td class="td_checkbox"><input type="checkbox" value="<?php echo $addon_item_id; ?>" id="<?php echo $addon_slug; ?>" name="addons[<?php echo $key; ?>][items][]" class="pl8app-checkbox" <?php echo $selected; ?> /></td>
                      <?php } else { ?>
                        <td class="td_checkbox">&nbsp;</td>
                      <?php } ?>
                      <td class="add_label"><label for="<?php echo $addon_slug; ?>"><?php echo $addon_item_name; ?></label></td>
                      <td class="variation_label"><label for="<?php echo $price['name']; ?>"><?php echo $price['name']; ?></label></td>
                      <td class="addon_price"><input class="addon-custom-price" type="text" placeholder="0.00" value="<?php echo $addon_price; ?>" name="addons[<?php echo $key; ?>][prices][<?php echo $addon_item_id; ?>][<?php echo $price['name']; ?>]"></td>
                    </tr>

                  <?php $count++; } ?>

                <?php } else {

                  $addon_price = isset( $addon_item['prices'] ) ? $addon_item['prices'][$addon_item_id] : $addon_price;

                  ?>

                  <tr class="pl8app-child-addon">
                    <td class="td_checkbox"><input type="checkbox" value="<?php echo $addon_item_id; ?>" id="<?php echo $addon_slug; ?>" name="addons[<?php echo $key; ?>][items][]" class="pl8app-checkbox" <?php echo $selected; ?> /></td>
                    <td class="add_label"><label for="<?php echo $addon_slug; ?>"><?php echo $addon_item_name; ?></label></td>
                    <td class="variation_label">&nbsp;</td>
                    <td class="addon_price"><input class="addon-custom-price" type="text" placeholder="0.00" value="<?php echo $addon_price; ?>" name="addons[<?php echo $key; ?>][prices][<?php echo $addon_item_id; ?>]"></td>
                  </tr>

                <?php } ?>
              <?php endforeach; ?>
              </tbody>
            </table>

          <?php else : ?>
            <div class="pl8app-addon-msg">
              <?php esc_html_e( 'Please select a addon category first!', 'pl8app' ); ?>
            </div>
          <?php endif; ?>
    		</div>
    	</div>
    </div>
    <!-- Addon category form ends -->

  <?php endforeach; ?>

<?php else : ?>

  <!-- Addon category form starts -->
  <div class="pl8app-addon pl8app-metabox">
    <h3>
      <a href="#" class="remove_row delete"><span class="dashicons dashicons-remove"></span></a>
      <strong><?php esc_html_e( 'Select Options and Upgrades', 'pl8app' ); ?></strong>
    </h3>
    <div class="pl8app-metabox-content">
      <div class="addon-category">
        <select name="addons[<?php echo $count; ?>][category]" class="pl8app-input pl8app-addon-items-list" data-row-id="<?php echo $count; ?>">
          <option value="">
            <?php esc_html_e( 'Select Options and Upgrades', 'pl8app' ); ?>
          </option>
          <?php
            foreach ( $addon_categories as $category ) :
              echo '<option value="' . $category->term_id .'">' . $category->name .'</option>';
            endforeach;
          ?>
        </select>
        <button type="button" class="button load-addon" data-item-id=<?php echo isset($item_id)?$item_id:$post_id; ?>>
          <?php esc_html_e( 'Add', 'pl8app' ); ?>
        </button>
        <label class="input_max_allowed">
          <?php esc_html_e( 'Max Selections?', 'pl8app' ); ?>
          <input type="number" name="addons[<?php echo $count; ?>][max_addons]" value="" />
        </label>
        <label class="cb_required">
          <input type="checkbox" name="addons[<?php echo $count; ?>][is_required]" value="yes" />Is Required?
          <span> | </span>
        </label>
      </div>
      <div class="addon-items">
        <div class="pl8app-addon-msg">
          <?php esc_html_e( 'Please select a addon category first!', 'pl8app' ); ?>
        </div>
      </div>
    </div>
  </div>
  <!-- Addon category form ends -->

<?php endif; ?>
