<?php
/*
* Sidebar category template
*
*/

global $data;

ob_start();

if ( $data['category_menu'] ) {
  $get_all_items = pl8app_get_child_cats( $data['ids'] );
} else {
  $get_all_items = pl8app_get_categories( $data );
}

?>

<div class="pl8app-col-lg-2 pl8app-col-md-2 pl8app-col-sm-3 pl8app-col-xs-12 sticky-sidebar cat-lists">
  <div class="pl8app-filter-wrapper">
    <div class="pl8app-categories-menu">
    <?php do_action('pl8app_before_category_list'); ?>
    <?php
    if ( is_array( $get_all_items ) && !empty( $get_all_items ) ) :
    ?>
      <ul class="pl8app-category-lists">
      <?php
      foreach ( $get_all_items as $key => $get_all_item ) : ?>
        <li class="pl8app-category-item ">
          <a href="#<?php echo $get_all_item->slug; ?>" data-id="<?php echo $get_all_item->term_id; ?>" class="pl8app-category-link nav-scroller-item"><?php echo $get_all_item->name; ?></a>
        </li>
      <?php endforeach; ?>
      </ul>
    <?php
    endif;
    ?>
    <?php do_action( 'pl8app_after_category_list' ); ?>
    </div>
  </div>
</div>
<?php
echo ob_get_clean();
