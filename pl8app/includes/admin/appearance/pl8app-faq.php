<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * FAQ Page
 *
 * Renders the FAQ page contents.
 *
 * @since 1.0
 * @return void
 */
function pl8app_faq_page()
{
    $options = get_option('pl8app_settings');
    $fqs = !empty($options['faq']['question']) && is_array($options['faq']['question']) ? $options['faq']['question'] : array();
    $fas = !empty($options['faq']['answer']) && is_array($options['faq']['answer']) ? $options['faq']['answer'] : array();
    ob_start();

    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('FAQ', 'pl8app'); ?></h2>
        <form method="post" action="options.php">
            <table class="form-table pl8app-settings">
                <?PHP settings_fields( 'pl8app_settings' );?>
            </table>
            <div class="pl8app-wrapper">
                <div class="panel-wrap menuitem_data" id="addons_menuitem_data">
                    <div class="panel pl8app-metaboxes-wrapper pl8app_options_panel">
                        <div class="pl8app-metabox-container">
                            <div class="pl8app-addons pl8app-metaboxes faq-container">

                                <?php foreach ($fqs as $index => $fq) {?>
                                <div class="pl8app-addon pl8app-metabox faq-row-container">
                                    <h3>
                                        <a href="#" class="remove_row delete"><span class="dashicons dashicons-remove"></span></a>
                                    </h3>
                                    <div class="pl8app-metabox-content">
                                        <div class="pl8app-grid-row">
                                            <label>Question: <textarea name="pl8app_settings[faq][question][]"
                                                                       class="large-text" required><?php echo !empty($fq) ? $fq : ''; ?></textarea></label>
                                        </div>
                                        <div class="pl8app-grid-row">
                                            <label>Answer: <textarea name="pl8app_settings[faq][answer][]"
                                                                     class="large-text" required><?php echo !empty($fas[$index]) ? $fas[$index] : ''; ?></textarea></label>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="toolbar toolbar-bottom">
                                <button type="button" data-item-id="1008"
                                        class="button button-primary add-new-faq alignright">
                                    <span class="dashicons dashicons-plus"></span><span>Add New FAQ</span>

                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
    $faq_display = ob_get_clean();
    echo $faq_display;
}
?>