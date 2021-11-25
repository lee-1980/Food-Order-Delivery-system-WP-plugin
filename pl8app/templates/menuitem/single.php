<?php
/**
 * A single menuitem inside of the [menuitems] shortcode.
 *
 * @since 1.0.0
 *
 * @package pl8app
 * @category Template
 * @author pl8app
 * @version 1.0.4
 */

global $pl8app_menuitem_shortcode_item_atts, $pl8app_menuitem_shortcode_item_i, $pl8app_menu_item_cats;

$schema = pl8app_add_schema_microdata() ? 'itemscope itemtype="http://schema.org/Product" ' : '';

$post_terms = wp_get_post_terms(get_the_ID(), 'menu-category');

$get_menu_id = $post_terms[0]->term_taxonomy_id;

$menuitem_timings_enabled = check_availability_menu_item_timing($post->ID);

if ($menuitem_timings_enabled) {
    ?>

    <div
        <?php echo $schema; ?>class="<?php echo esc_attr(apply_filters('pl8app_menuitem_class', 'pl8app_menuitem', get_the_ID(), $pl8app_menuitem_shortcode_item_atts, $pl8app_menuitem_shortcode_item_i)); ?>"
        data-term-id="<?php echo $get_menu_id; ?>" id="pl8app_menuitem_<?php the_ID(); ?>">

        <div class="row <?php echo esc_attr(apply_filters('pl8app_menuitem_inner_class', 'pl8app_menuitem_inner', get_the_ID(), $pl8app_menuitem_shortcode_item_atts, $pl8app_menuitem_shortcode_item_i)); ?>">

            <?php do_action('pl8app_menuitem_before'); ?>

            <div class="pl8app-col-md-9">
                <?php
                pl8app_get_template_part('menuitem/content-image');
                do_action('pl8app_menuitem_after_thumbnail');

                pl8app_get_template_part('menuitem/content-title');
                do_action('pl8app_menuitem_after_title');
                ?>
            </div>

            <div class="pl8app-col-md-3">

                <?php
                pl8app_get_template_part('menuitem/content-cart-button');
                do_action('pl8app_menuitem_after_cart_button');
                ?>

            </div>

            <?php do_action('pl8app_menuitem_after'); ?>

        </div>
    </div>

<?PHP }