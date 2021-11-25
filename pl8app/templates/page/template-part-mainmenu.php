<?php
if ( is_front_page() || is_home() || is_404() ) {
	$heading = 'h1';
} else {
	$heading = 'h2';
}
$nav_menu_color = pl8app_get_option('nav_menu_color', '#0c0c0c');
?>
	<div class = "rsrc-top-menu row" >
		<nav id = "site-navigation" class = "navbar navbar-inverse " role = "navigation" itemscope itemtype = "http://schema.org/SiteNavigationElement" style="background-color: <?php echo $nav_menu_color; ?>">
			<div class = "navbar-header">
				<button type = "button" class = "navbar-toggle" data-toggle = "collapse" data-target = ".navbar-1-collapse">
					<span class = "sr-only"><?php esc_html_e( 'Toggle navigation', 'pl8app' ); ?></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<header class="visible-xs-block responsive-title" itemscope itemtype="http://schema.org/WPHeader" role="banner">
					<?php if ( pl8app_get_option( 'header_logo', '' ) != '' ) : ?>
						<div class="menu-img text-left" itemprop="headline">
							<a itemprop="url" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( pl8app_get_option( 'header_logo') ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" /></a>
						</div>
					<?php else : ?>
						<div class="rsrc-header-text menu-text">
							<<?php echo $heading ?> class="site-title" itemprop="headline"><a itemprop="url" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></a></<?php echo $heading ?>>
						</div>
					<?php endif; ?>
				</header>
			</div>

			<?php
			wp_nav_menu( array(
				'theme_location'	 => 'pl8app_main_menu',
				'depth'				 => 3,
				'container'			 => 'div',
				'container_class'	 => 'collapse navbar-collapse navbar-1-collapse',
				'menu_class'		 => 'nav navbar-nav ' . pl8app_get_option( 'menu-position', 'menu-center' ),
				'fallback_cb'		 => 'wp_bootstrap_navwalker::fallback',
				'walker'			 => new wp_bootstrap_navwalker() )
			);
			?>
		</nav>
	</div>

 