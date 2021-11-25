<?php
/**
 * pl8app_StoreTiming_Fields
 *
 * @package pl8app_StoreTiming_Fields
 * @since 1.0.1
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists ( 'pl8app_store_timing_callback' ) ) {
  function pl8app_store_timing_callback( $args ) {
     pl8app_StoreTiming_Settings::get_template_part( 'store-time-setting-fields' );
  }
}


if( !function_exists('pl8app_store_otime_page')) {
    function pl8app_store_otime_page(){
        ob_start();
        ?>
        <div class="wrap wrap-st">
            <h2><?php _e('Pl8App Store Open Timing', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields( 'pl8app_settings' );?>
                </table>
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row" class="pl8app-heading"></th>
                        <td>
                            <?PHP
                            pl8app_StoreTiming_Settings::get_template_part('store-time-setting-fields');
                            ?>
                        </td>
                    </tr>
                    </tbody>

                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?PHP
        echo ob_get_clean();

    }
}