<?php
/**
 * Upgrade Screen
 *
 * @package     pl8app
 * @subpackage  Admin/Upgrades
 * @copyright
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0.0.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Render Upgrades Screen
 *
 * @since  1.0.0
 * @return void
*/
function pl8app_upgrades_screen() {
  $action = isset( $_GET['pl8app-upgrade'] ) ? sanitize_text_field( $_GET['pl8app-upgrade'] ) : '';
  ?>

  <div class="wrap">
  <h2><?php _e( 'pl8app - Upgrades', 'pl8app' ); ?></h2>
  <?php
  if ( is_callable( 'pl8app_upgrade_render_' . $action ) ) {

    // Until we have fully migrated all upgrade scripts to this new system, we will selectively enqueue the necessary scripts.
    add_filter( 'pl8app_load_admin_scripts', '__return_true' );
    pl8app_load_admin_scripts( '' );

    // This is the new method to register an upgrade routine, so we can use an ajax and progress bar to display any needed upgrades.
    call_user_func( 'pl8app_upgrade_render_' . $action );

  } else {

    // This is the legacy upgrade method, which requires a page refresh at each step.
    $step   = isset( $_GET['step'] )        ? absint( $_GET['step'] )                     : 1;
    $total  = isset( $_GET['total'] )       ? absint( $_GET['total'] )                    : false;
    $custom = isset( $_GET['custom'] )      ? absint( $_GET['custom'] )                   : 0;
    $number = isset( $_GET['number'] )      ? absint( $_GET['number'] )                   : 100;
    $steps  = round( ( $total / $number ), 0 );
    if ( ( $steps * $number ) < $total ) {
      $steps++;
    }

    $doing_upgrade_args = array(
      'page'        => 'pl8app-upgrades',
      'pl8app-upgrade' => $action,
      'step'        => $step,
      'total'       => $total,
      'custom'      => $custom,
      'steps'       => $steps
    );
    update_option( 'pl8app_doing_upgrade', $doing_upgrade_args );
    if ( $step > $steps ) {
      // Prevent a weird case where the estimate was off. Usually only a couple.
      $steps = $step;
    }
    ?>

      <?php if( ! empty( $action ) ) : ?>

        <div id="pl8app-upgrade-status">
          <p><?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'pl8app' ); ?></p>

          <?php if( ! empty( $total ) ) : ?>
            <p><strong><?php printf( __( 'Step %d of approximately %d running', 'pl8app' ), $step, $steps ); ?></strong></p>
          <?php endif; ?>
        </div>
        <script type="text/javascript">
          setTimeout(function() { document.location.href = "index.php?pl8app_action=<?php echo $action; ?>&step=<?php echo $step; ?>&total=<?php echo $total; ?>&custom=<?php echo $custom; ?>"; }, 250);
        </script>

      <?php else : ?>

        <div id="pl8app-upgrade-status">
          <p>
            <?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'pl8app' ); ?>
            <img src="<?php echo PL8_PLUGIN_URL . '/assets/images/loading.gif'; ?>" id="pl8app-upgrade-loader"/>
          </p>
        </div>
        <script type="text/javascript">
          jQuery( document ).ready( function() {
            // Trigger upgrades on page load
            var data = { action: 'pl8app_trigger_upgrades' };
            jQuery.post( ajaxurl, data, function (response) {
              if( response == 'complete' ) {
                jQuery('#pl8app-upgrade-loader').hide();
                document.location.href = 'index.php'; // Redirect to the dashboard
              }
            });
          });
        </script>

      <?php endif; ?>

    <?php
  }
  ?>
  </div>
  <?php
}
