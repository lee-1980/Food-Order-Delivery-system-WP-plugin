<style>
#pl8app-email-menu-list, #pl8app-email-menu-list td, #pl8app-email-menu-list th {
  border: 1px solid #ddd;
  text-align: left;
}
#pl8app-email-menu-list  {
  border-collapse: collapse;
  width: 100%;
}
#pl8app-email-menu-list th, #pl8app-email-menu-list td {
  padding: 15px;
}
</style>

<table id="pl8app-email-menu-list" class="pl8app-table">
  <thead>
    <th><?php _e( 'Name', 'pl8app' ); ?></th>
    <th><?php _e( 'Price', 'pl8app' ); ?></th>
  </thead>
  <tbody>
  <?php if( is_array( $pl8app_email_menuitems ) ) : ?>
    <?php
    foreach ( $pl8app_email_menuitems as $key => $item ) : ?>
      <?php $row_price = array(); ?>
      <tr>
        <td>
          <div class="pl8app_email_receipt_product_name">
            <?php echo $item['quantity']; ?> X <?php echo get_the_title( $item['id'] ); ?> (<?php echo pl8app_price( $item['id'] ); ?>)
            <?php
              if( !empty( $item['options'] ) ) {
                foreach( $item['options'] as $k => $v ) {
                  if( is_array( $v ) ) {
                    array_push( $row_price, $v['price'] );
                    if( !empty( $v['addon_item_name'] ) ) {
                      ?>
                      <br/>&nbsp;&nbsp;<small class="pl8app-receipt-addon-item"><?php echo $v['addon_item_name']; ?> (<?php echo pl8app_currency_filter(pl8app_format_amount($v['price'])); ?>)</small>
                      <?php
                    }
                  }
                }
              }
            ?>
          </div>
        </td>
        <td>
          <?php
          $addon_price = array_sum( $row_price );
          $total_price = $addon_price + pl8app_get_menuitem_price( $item['id'] );
          ?>
          <?php echo pl8app_currency_filter( pl8app_format_amount( $total_price ) ); ?>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>