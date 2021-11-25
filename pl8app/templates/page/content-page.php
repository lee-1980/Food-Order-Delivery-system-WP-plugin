<!-- start content container -->

<div class="row rsrc-content" >
	<article class="col-md-12 rsrc-main">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

				<div <?php post_class( 'rsrc-post-content' ); ?>>                            
					<header>                              
						<h1 class="entry-title page-header">
							<?php the_title(); ?>
						</h1> 
						<time class="posted-on published" datetime="<?php the_time( 'Y-m-d' ); ?>"></time>                                                        
					</header>                            
					<div class="entry-content">                              
						<?php the_content(); ?>                            
					</div>                               
					<?php wp_link_pages(); ?>                                                                                   

				</div>        
			<?php endwhile; ?>        
		<?php else: ?>            
			<?php pl8app_get_template_part( 'page/content-none' ); ?>
		<?php endif; ?>    
	</article>
</div>
<!-- end content container -->