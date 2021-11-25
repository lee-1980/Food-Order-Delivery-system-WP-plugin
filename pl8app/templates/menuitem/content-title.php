<?php $menu_type = get_post_meta( get_the_id(), 'pl8app_menu_type', true ); ?>

<div class="pl8app-title-holder">

  <?php $item_prop = pl8app_add_schema_microdata() ? ' itemprop="name"' : ''; ?>

  <h3<?php echo $item_prop; ?> class="pl8app_menuitem_title">
    <a class="menu-title" itemprop="url">
      <?php if( ! empty( $menu_type ) ) { ?>
        <img src="<?php echo PL8_PLUGIN_URL . 'assets/images/'.$menu_type.'.png'; ?>">
      <?php } ?>
      <?php the_title();?>
    </a>
  </h3>

  <?php $excerpt_length = apply_filters( 'excerpt_length', 40 ); ?>

  <?php $item_prop = pl8app_add_schema_microdata() ? ' itemprop="description"' : ''; ?>

  <?php if ( has_excerpt() ) : ?>

    <div<?php echo $item_prop; ?> class="pl8app_menuitem_excerpt">
      <?php echo apply_filters( 'pl8app_menuitems_excerpt', wp_trim_words( get_post_field( 'post_excerpt', get_the_ID() ), $excerpt_length ) ); ?>
    </div>

  <?php elseif ( get_the_content() ) : ?>

    <div<?php echo $item_prop; ?> class="pl8app_menuitem_excerpt">
      <?php echo apply_filters( 'pl8app_menuitems_excerpt', wp_trim_words( get_post_field( 'post_content', get_the_ID() ), $excerpt_length ) ); ?>
    </div>

  <?php endif; ?>


</div>
