<?php

$menuitems_overlay = pl8app_get_option( 'enable_menu_image_popup', false );
$image_placeholder = pl8app_get_option( 'enable_image_placeholder', false );

if ( has_post_thumbnail( $post->ID ) ):

  $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID, 'full' ), 'single-post-thumbnail' ); ?>

  <div class="pl8app-thumbnail-holder pl8app-bg">

    <?php if( $menuitems_overlay == 1 ) : ?>
      <a href="<?php echo $image[0]; ?>" class="pl8app-thumbnail-popup">
          <?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); ?>
      </a>
    <?php else: ?>
      <?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); ?>
    <?php endif; ?>
  </div>

<?php elseif( $image_placeholder == 1 ) : ?>

    <?php $image_src = plugins_url( 'pl8app/assets/svg/no_image.png' ); ?>
    <div class="pl8app-thumbnail-holder pl8app-default-bg">
        <img src="<?php echo $image_src; ?>" alt=""/>
    </div>

<?php endif; ?>
