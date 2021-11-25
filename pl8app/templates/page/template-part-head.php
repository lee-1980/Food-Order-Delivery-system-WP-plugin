
<?php
$body_color = pl8app_get_option('body_color', '#0c0c0c');
?>

<div class="container rsrc-container" role="main" style="background-color: <?php echo $body_color; ?>">
    <?php
    if ( is_front_page() || is_home() || is_404() ) {
        $heading = 'h1';
        $desc	 = 'h2';
    } else {
        $heading = 'h2';
        $desc	 = 'h3';
    }
    ?>

    <?php if ( pl8app_get_option( 'infobox-text-right', '' ) != '' || pl8app_get_option( 'infobox-text-left', '' ) != '' ) : ?>
        <div class="top-section row">
            <div class="top-infobox text-left col-xs-6">
                <?php if ( pl8app_get_option( 'pl8app_socials', '-1' ) == 1 ) { ?>
                    <div class="social-section col-md-4">
                        <?php pl8app_bar_social_links(); ?>
                    </div>
                <?php } ?>
            </div>
            <div class="top-infobox text-right col-xs-6">
                <?php if ( pl8app_get_option( 'infobox-text-right', '' ) != '' ) {
                    echo pl8app_get_option( 'infobox-text-right' );
                } ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="header-section row" >
        <?php // Site title/logo ?>
        <header id="site-header" class="col-sm-4 col-xs-12 col-sm-push-4 <?php if (has_nav_menu( 'pl8app_main_menu' ) ) { echo 'hidden-xs'; }  ?> rsrc-header text-center" itemscope itemtype="http://schema.org/WPHeader" role="banner">
            <?php if ( pl8app_get_option( 'header_logo', '' ) != '' ) : ?>
                <div class="rsrc-header-img" itemprop="headline">
                    <a itemprop="url" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( pl8app_get_option( 'header_logo' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" /></a>
                </div>
            <?php else : ?>
            <div class="rsrc-header-text">
                <<?php echo $heading ?> class="site-title" itemprop="headline"><a itemprop="url" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></a></<?php echo $heading ?>>
            <<?php echo $desc ?> class="site-desc" itemprop="description"><?php esc_attr( bloginfo( 'description' ) ); ?></<?php echo $desc ?>>
            </div>
        <?php endif; ?>
        </header>
    </div>

    <?php pl8app_get_template_part('page/template-part-mainmenu'); ?>

<div id="site-content" ></div>
