<?php
/**
 * pl8app Store SiteMap content
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function pl8app_sitemap_page(){

    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Sitemap', 'pl8app'); ?></h2>
        <div id="tab_container">
            <div class="pl8app-wrapper">
            <table class="form-table" role="presentation">
                <tr>
                    <th>
                        <?php echo __('Sitemap Link:', 'pl8app');?>
                    </th>
                    <td>
                        <?php echo home_url('/pl8app_sitemap.xml'); ?>
                    </td>
                </tr>
            </table>
            </div>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}

/**
 * pl8app Store Item Reviews Store
 */

function pl8app_reviews_feed(){

    ob_start();
    ?>
    <style>
        .pl8app-wrapper span {
            margin-bottom: 10px;
            display: initial !important;
        }
    </style>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Sitemap', 'pl8app'); ?></h2>
        <div id="tab_container">
            <div class="pl8app-wrapper">
                <div vocab="https://schema.org/" typeof="Restaurant">
                    <p><strong>Name:</strong> <span property="name">GreatFood</span></p>
                    <div property="aggregateRating" typeof="AggregateRating">
                        <p><strong>AggregateRating:</strong>
                            <span property="ratingValue">4</span> stars - based on
                            <span property="reviewCount">250</span> reviews
                        </p>
                    </div>
                    <div property="address" typeof="PostalAddress">
                        <p><strong>Address:</strong>
                            <span property="streetAddress">1901 Lemur Ave</span>
                            <span property="addressLocality">Sunnyvale</span>,
                            <span property="addressRegion">CA</span> <span property="postalCode">94086</span>
                        </p>
                    </div>

                    <div property="telephone"><p><strong>Telephone:</strong> (408) 714-1489</p></div>

                    <div property="url" href="http://www.greatfood.com"><p><strong>URL:</strong> www.greatfood.com</p></div>

                    <p><strong>Hours:</strong></p>
                    <p>
                        <meta property="openingHours" content="Mo-Sa 11:00-14:30">
                        Mon-Sat 11am - 2:30pm
                    </p>
                    <p>
                        <meta property="openingHours" content="Mo-Th 17:00-21:30">
                        Mon-Thu 5pm - 9:30pm
                    </p>
                    <p>
                        <meta property="openingHours" content="Fr-Sa 17:00-22:00">
                        Fri-Sat 5pm - 10:00pm
                    </p>

                    <p><strong>Categories:</strong>
                        <span property="servesCuisine">Middle Eastern</span>,
                        <span property="servesCuisine">Mediterranean</span>
                    </p>

                    <p><strong>Price Range:</strong> <span property="priceRange">$$</span> </p>
                    <p><strong>Takes Reservations:</strong> Yes </p>
                </div>
            </div>
        </div>
        <p class="submit">
            <input type="button" name="update_review_feed_xml" id="update_review_feed_xml" class="button button-primary" value="Update Review Feed XML">
        </p>
    </div>

    <?php
    echo ob_get_clean();
}

