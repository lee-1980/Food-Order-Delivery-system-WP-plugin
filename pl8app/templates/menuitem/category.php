<?php

global $curr_cat_var;
global $menuitem_term_slug;
global $pl8app_menuitem_id;

$class = ($curr_cat_var == $menuitem_term_slug )? 'pl8app-same-cat' : 'pl8app-different-cat';
$curr_cat_var = $menuitem_term_slug;
$menu_category = get_term_by( 'slug', $menuitem_term_slug, 'menu-category' );

if( $class == 'pl8app-different-cat' ) : ?>

<div id="menu-category-<?php echo $menu_category->term_id; ?>" class="pl8app-element-title" id="<?php echo $pl8app_menuitem_id; ?>" data-term-id="<?php echo $menu_category->term_id; ?>">
  <div class="menu-category-wrap" data-cat-id="<?php echo $menuitem_term_slug; ?>">
    <div class="menu-category-wrap" data-cat-id="<?php echo $menuitem_term_slug; ?>">
      <h5 class="pl8app-cat pl8app-different-cat"><?php echo $menu_category->name; ?></h5>
        <?php if( !empty( $menu_category->description ) ) : ?>
          <span><?php echo $menu_category->description; ?></span>
        <?php endif; ?>
    </div>
  </div>
</div>

<?php endif; ?>