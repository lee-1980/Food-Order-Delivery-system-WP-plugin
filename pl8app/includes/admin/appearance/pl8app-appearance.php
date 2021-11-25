<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */

function pl8app_appearance_page(){
    $pages = pl8app_get_pages(true);
    $options = get_option('pl8app_settings');
    $buttons = pl8app_get_button_styles();

    require_once PL8_PLUGIN_DIR . '/includes/libraries/phpqrcode/qrlib.php';

    ob_start();
    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Appearance', 'pl8app'); ?></h2>
        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">
                    <?PHP settings_fields( 'pl8app_settings' );?>
                </table>
                <div class="pl8app-wrapper">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row" class="pl8app-label-section"><h3><?php echo __('Menu Pages','pl8app') ?></h3></th>
                        <td> <a></a>
                            <?php
                            $url = admin_url( 'nav-menus.php' );
                            echo sprintf( __( 'To add pages to menu and rearrange them into the order you prefer, Kindly <a href="%1$s" target="_blank">click here</a>', 'pl8app' ), esc_url( $url ) );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="pl8app-label-section"><h3><?php echo __('Pages','pl8app');?></h3></th>
                        <td><span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help"
                                  title="<strong>Page Settings</strong><br />pl8app uses the pages below for handling the display of checkout, purchase confirmation, order history, and order failures. If pages are deleted or removed in some way, they can be recreated manually from the Pages menu. When re-creating the pages, enter the shortcode shown in the page content area."></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Menu Items Page','pl8app');?></th>
                        <td>
                            <select class="pl8app-select-chosen" id="pl8app_settings[menu_items_page]"
                                    name="pl8app_settings[menu_items_page]" data-placeholder="Select a page"
                                    readonly="readonly"
                                    value="<?PHP echo !empty($options['menu_items_page']) ? $options['menu_items_page'] : ''; ?>">
                                <?php
                                foreach ($pages as $key => $page) {
                                    echo '<option value="' . $key . '" ' . (!empty($options['menu_items_page']) && $options['menu_items_page'] == $key ? "selected" : "") . '>' . $page . '</option>';
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[menu_items_page]"><?php echo __(' This is the menu page where buyers can
                                browser and select items to place an order.. The [menuitems] shortcode must be on
                                this page.','pl8app');?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Checkout Page','pl8app');?></th>
                        <td>
                            <select class="pl8app-select-chosen" id="pl8app_settings[purchase_page]"
                                    name="pl8app_settings[purchase_page]" data-placeholder="Select a page"
                                    readonly="readonly"
                                    value="<?PHP echo !empty($options['purchase_page']) ? $options['purchase_page'] : ''; ?>">
                                <?php
                                foreach ($pages as $key => $page) {
                                    echo '<option value="' . $key . '" ' . (!empty($options['purchase_page']) && $options['purchase_page'] == $key ? "selected" : "") . '>' . $page . '</option>';
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[purchase_page]"><?php echo __(' This is the checkout page where buyers will complete their purchases. The [menuitem_checkout] shortcode must be on this page.','pl8app');?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Success Page','pl8app');?></th>
                        <td>
                            <select class="pl8app-select-chosen" id="pl8app_settings[success_page]"
                                    name="pl8app_settings[success_page]" data-placeholder="Select a page"
                                    readonly="readonly"
                                    value="<?PHP echo !empty($options['success_page']) ? $options['success_page'] : ''; ?>">
                                <?php
                                foreach ($pages as $key => $page) {
                                    echo '<option value="' . $key . '" ' . (!empty($options['success_page']) && $options['success_page'] == $key ? "selected" : "") . '>' . $page . '</option>';
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[success_page]"><?php echo __(' This is the page buyers are sent to after
                                completing their purchases. The [pl8app_receipt] shortcode should be on this
                                page.','pl8app');?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Failed Transaction Page','pl8app');?></th>
                        <td>
                            <select class="pl8app-select-chosen" id="pl8app_settings[failure_page]"
                                    name="pl8app_settings[failure_page]" data-placeholder="Select a page"
                                    readonly="readonly"
                                    value="<?PHP echo !empty($options['failure_page']) ? $options['failure_page'] : ''; ?>">
                                <?php
                                foreach ($pages as $key => $page) {
                                    echo '<option value="' . $key . '" ' . (!empty($options['failure_page']) && $options['failure_page'] == $key ? "selected" : "") . '>' . $page . '</option>';
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[failure_page]"><?php echo __(' This is the page buyers are sent to if their
                                transaction is cancelled or fails.','pl8app');?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Order History Page','pl8app');?></th>
                        <td>
                            <select class="pl8app-select-chosen" id="pl8app_settings[order_history_page]"
                                    name="pl8app_settings[order_history_page]" data-placeholder="Select a page"
                                    readonly="readonly"
                                    value="<?PHP echo !empty($options['order_history_page']) ? $options['order_history_page'] : ''; ?>">
                                <?php
                                foreach ($pages as $key => $page) {
                                    echo '<option value="' . $key . '" ' . (!empty($options['order_history_page']) && $options['order_history_page'] == $key ? "selected" : "") . '>' . $page . '</option>';
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[order_history_page]"><?php echo __(' This page shows a complete order
                                history for the current user, including menuitem links. The [order_history]
                                shortcode should be on this page.','pl8app');?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Login Redirect Page','pl8app');?></th>
                        <td>
                            <select class="pl8app-select-chosen" id="pl8app_settings[login_redirect_page]"
                                    name="pl8app_settings[login_redirect_page]" data-placeholder="Select a page"
                                    readonly="readonly"
                                    value="<?PHP echo !empty($options['login_redirect_page']) ? $options['login_redirect_page'] : ''; ?>">
                                <?php
                                foreach ($pages as $key => $page) {
                                    echo '<option value="' . $key . '" ' . (!empty($options['login_redirect_page']) && $options['login_redirect_page'] == $key ? "selected" : "") . '>' . $page . '</option>';
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[login_redirect_page]"><?php echo __(' If a customer logs in using the
                                [pl8app_login] shortcode, this is the page they will be redirected to. Note, this
                                can be overridden using the redirect attribute in the shortcode like this:
                                [pl8app_login redirect="http://localhost:8082/"].','pl8app');?></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="pl8app-label-section"><h3><?php echo __('Store Notice','pl8app') ?></h3></th>
                        <td>
                            <div class="customize-control-content">
                                <input type="hidden" name="pl8app_settings[pl8app_enable_notice]" value="-1">
                                <input type="checkbox" id="pl8app_settings[pl8app_enable_notice]" name="pl8app_settings[pl8app_enable_notice]" value="1" <?PHP echo ! empty( $options['pl8app_enable_notice'] ) ? checked( 1, $options['pl8app_enable_notice'], false ) : '' ;?>>
                                <label for="pl8app_settings[pl8app_enable_notice]">
                                    <?php echo __('Enable Store Notice.', 'pl8app'); ?> </label>
                            </div>
                            <div class="customize-control-content">
                                <span class="help-text">If enabled, this text will be shown site-wide. You can use it to show events or promotions to visitors!</span>
                                <textarea class="small-text" rows="5" cols="50" id="pl8app_settings[pl8app_store_notice]" name="pl8app_settings[pl8app_store_notice]">
                                <?PHP echo !empty($options['pl8app_store_notice']) ? $options['pl8app_store_notice'] : ''; ?>
                                </textarea>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="pl8app-label-section"><h3><?php echo __('Top Bars','pl8app') ?></h3></th>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('left side','pl8app') ?></th>
                        <td>
                            <div class="customize-control-content">
                                <input type="hidden" name="pl8app_settings[pl8app_socials]" value="-1">
                                <input type="checkbox" id="pl8app_settings[pl8app_socials]" name="pl8app_settings[pl8app_socials]" value="1" <?PHP echo ! empty( $options['pl8app_socials'] ) ? checked( 1, $options['pl8app_socials'], false ) : '' ;?>>
                                <label for="pl8app_settings[pl8app_socials]">
                                    <?php echo __('Check this to enable
                                    social links in the top bar left side.', 'pl8app'); ?> </label>
                            </div>
                            <div class="container-social-lists <?PHP echo  $options['pl8app_socials'] != 1 ? 'hidden' : '' ;?>">
                                <div class="customize-control-content">
                                    <b><?php echo __('Contact Us', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[pl8app_socials_text]" name="pl8app_settings[pl8app_socials_text]"
                                           value="<?PHP echo !empty($options['pl8app_socials_text']) ? $options['pl8app_socials_text'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('FaceBook', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_facebook]" name="pl8app_settings[twp_social_facebook]"
                                           value="<?PHP echo !empty($options['twp_social_facebook']) ? $options['twp_social_facebook'] : ''; ?>" type="text">
                                    <?php echo __('Insert your custom link to show the Facebook icon.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Twitter', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_twitter]" name="pl8app_settings[twp_social_twitter]"
                                           value="<?PHP echo !empty($options['twp_social_twitter']) ? $options['twp_social_twitter'] : ''; ?>" type="text">
                                    <?php echo __('Insert your custom link to show the Twitter icon.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Google-Plus', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_google]" name="pl8app_settings[twp_social_google]"
                                           value="<?PHP echo !empty($options['twp_social_google']) ? $options['twp_social_google'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Instagram', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_instagram]" name="pl8app_settings[twp_social_instagram]"
                                           value="<?PHP echo !empty($options['twp_social_instagram']) ? $options['twp_social_instagram'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Pinterest', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_pin]" name="pl8app_settings[twp_social_pin]"
                                           value="<?PHP echo !empty($options['twp_social_pin']) ? $options['twp_social_pin'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('YouTube', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_youtube]" name="pl8app_settings[twp_social_youtube]"
                                           value="<?PHP echo !empty($options['twp_social_youtube']) ? $options['twp_social_youtube'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('LinkedIn', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_linkedin]" name="pl8app_settings[twp_social_linkedin]"
                                           value="<?PHP echo !empty($options['twp_social_linkedin']) ? $options['twp_social_linkedin'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Vimeo', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_vimeo]" name="pl8app_settings[twp_social_vimeo]"
                                           value="<?PHP echo !empty($options['twp_social_vimeo']) ? $options['twp_social_vimeo'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Flickr', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_flickr]" name="pl8app_settings[twp_social_flickr]"
                                           value="<?PHP echo !empty($options['twp_social_flickr']) ? $options['twp_social_flickr'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Dribbble', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_dribble]" name="pl8app_settings[twp_social_dribble]"
                                           value="<?PHP echo !empty($options['twp_social_dribble']) ? $options['twp_social_dribble'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Email', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_envelope-o]" name="pl8app_settings[twp_social_envelope-o]"
                                           value="<?PHP echo !empty($options['twp_social_envelope-o']) ? $options['twp_social_envelope-o'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('Rss', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_rss]" name="pl8app_settings[twp_social_rss]"
                                           value="<?PHP echo !empty($options['twp_social_rss']) ? $options['twp_social_rss'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                                <div class="customize-control-content">
                                    <b><?php echo __('VKontakte', 'pl8app'); ?></b>
                                    <input class="large-text" id="pl8app_settings[twp_social_vk]" name="pl8app_settings[twp_social_vk]"
                                           value="<?PHP echo !empty($options['twp_social_vk']) ? $options['twp_social_vk'] : ''; ?>" type="text">
                                    <?php echo __('Insert your text before social icons.', 'pl8app'); ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('right side','pl8app') ?></th>
                        <td>
                            <textarea class="small-text" rows="5" cols="50" id="admin_recipients" name="pl8app_settings[infobox-text-right]">
                                <?PHP echo !empty($options['infobox-text-right']) ? $options['infobox-text-right'] : ''; ?>
                            </textarea>
                            <span class="help-text">Top bar right text area</span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" class="pl8app-label-section"><h3><?php echo __('Site identity','pl8app') ?></h3></th>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Store logo','pl8app') ?></th>
                        <td>
                            <label for="pl8app_settings[header_logo]">
                                <?php echo __('Upload your logo', 'pl8app'); ?> </label>
                            <div class="customize-control-content">
                                <div class="thumbnail placeholder <?PHP echo !empty($options['header_logo']) ? 'hidden' : ''; ?>">No File Selected</div>
                                <div class="thumbnail thumbnail-image <?PHP echo !empty($options['header_logo']) ? '' : 'hidden'; ?>"><img src="<?PHP echo !empty($options['header_logo']) ? $options['header_logo'] : ''; ?>" alt=""></div>
                                <input type="hidden" name="pl8app_settings[header_logo]" value="<?PHP echo !empty($options['header_logo']) ? $options['header_logo'] : ''; ?>">
                                <div class="actions">
                                    <input type="button" class="button image-upload-remove-button" style="display: inline-block;" value="Remove">
                                    <input type="button" class="pl8app_settings_upload_button button-secondary" value="Select a image">
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('QR Code for Store domain address','pl8app') ?></th>
                        <td>
                            <?php
                            $upload_dir = wp_upload_dir();
                            $image_relative_path = "/store_domain.png";
                            $image_path = $upload_dir['basedir'] . $image_relative_path;
                            $image_url = $upload_dir['baseurl'] . $image_relative_path;


                                // $ecc stores error correction capability('L')
                                $ecc = 'M';
                                $pixel_Size = 10;
                                $frame_Size = 0;
                                $text = get_site_url();
                                // Generates QR Code and Stores it in directory given
                                QRcode::png($text, $image_path, $ecc, $pixel_Size, $frame_Size);

                                $logo_image = PL8_PLUGIN_DIR.'/assets/images/pl8app_logo.png';

                                // Start DRAWING LOGO IN QRCODE

                                $QR = imagecreatefrompng($image_path);

                                // START TO DRAW THE IMAGE ON THE QR CODE
                                $logo = imagecreatefromstring(file_get_contents($logo_image));

                                $QR_width = imagesx($QR);
                                $QR_height = imagesy($QR);

                                $logo_width = imagesx($logo);
                                $logo_height = imagesy($logo);

                                // Scale logo to fit in the QR Code
                                $logo_qr_width = $QR_width/5;
                                $scale = $logo_width/$logo_qr_width;
                                $logo_qr_height = $logo_height/$scale;

                                imagecopyresampled($QR, $logo, $QR_width/2.5, $QR_height/2.5, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

                                // Save QR code again, but with logo on it
                                imagepng($QR,$image_path);



                            echo '<div class="customize-control-content"><div class="thumbnail thumbnail-image"><a download="store_domain.jpg" href="' . $image_url . '" title="Store Domain Address"><img src="' . $image_url . '"></a></div></div>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Site Title','pl8app') ?></th>
                        <td>
                            <input type="text" value="<?PHP echo !empty(get_option('blogname')) ? get_option('blogname') : ''; ?>" name="blogname"/>
                            <span class="help-text">Top bar right text area</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Tagline','pl8app') ?></th>
                        <td>
                            <input type="text" value="<?PHP echo !empty(get_option('blogdescription')) ? get_option('blogdescription') : ''; ?>" name="blogdescription"/>
                            <span class="help-text">Top bar right text area</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Site Icon','pl8app') ?></th>
                        <td>
                            <label for="pl8app_settings[site_icon]">
                                <?php echo __('Upload your icon', 'pl8app'); ?> </label>
                            <div class="customize-control-content">
                                <div class="thumbnail placeholder <?PHP echo !empty($options['site_icon']) ? 'hidden' : ''; ?>">No File Selected</div>
                                <div class="thumbnail thumbnail-image <?PHP echo !empty($options['site_icon']) ? '' : 'hidden'; ?>"><img src="<?PHP echo !empty($options['site_icon']) ? $options['site_icon'] : ''; ?>" alt=""></div>
                                <input type="hidden" name="pl8app_settings[site_icon]" value="<?PHP echo !empty($options['site_icon']) ? $options['site_icon'] : ''; ?>">
                                <div class="actions">
                                    <input type="button" class="button image-upload-remove-button" style="display: inline-block;" value="Remove">
                                    <input type="button" class="pl8app_settings_upload_button button-secondary" value="Select a icon">
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="pl8app-label-section"><h3><?php echo __('Styles','pl8app') ?></h3></th>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo __('Disable Styles','pl8app') ?></th>
                        <td>
                            <input type="hidden" name="pl8app_settings[disable_styles]" value="-1">
                            <input type="checkbox" id="pl8app_settings[disable_styles]"
                                name="pl8app_settings[disable_styles]" value="1" <?PHP echo ! empty( $options['disable_styles'] ) ? checked( 1, $options['disable_styles'], false ) : '' ;?>>
                            <label for="pl8app_settings[disable_styles]"> Check this to disable all included styling of
                                buttons, checkout fields, and all other elements.</label>
                            <span alt="f223" class="pl8app-help-tip dashicons dashicons-editor-help" title="<strong>Disabling Styles</strong><br />If your theme has a complete custom CSS file for pl8app, you may wish to disable our default styles. This is not recommended unless you're sure your theme has a complete custom CSS."></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Enable Image Placeholder</th>
                        <td>
                            <input type="hidden" name="pl8app_settings[enable_image_placeholder]" value="-1">
                            <input type="checkbox" id="pl8app_settings[enable_image_placeholder]"
                                name="pl8app_settings[enable_image_placeholder]" value="1" <?PHP echo ! empty( $options['enable_image_placeholder'] ) ? checked( 1, $options['enable_image_placeholder'], false ) : '' ;?>>
                            <label for="pl8app_settings[enable_image_placeholder]"> Check this to enable showing
                                placeholders where item image is not available.</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Menu Image Popup</th>
                        <td>
                            <input type="hidden" name="pl8app_settings[enable_menu_image_popup]" value="-1">
                            <input type="checkbox" id="pl8app_settings[enable_menu_image_popup]"
                                name="pl8app_settings[enable_menu_image_popup]" value="1" <?PHP echo ! empty( $options['enable_menu_image_popup'] ) ? checked( 1, $options['enable_menu_image_popup'], false ) : '' ;?>>
                            <label for="pl8app_settings[enable_menu_image_popup]"> If you want people to click on the menu
                                images to view the full menu image then enable this.</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Default Button Style</th>
                        <td>
                            <select id="pl8app_settings[button_style]" name="pl8app_settings[button_style]" class=""
                                    data-placeholder="">
                                <?php
                                foreach ($buttons as $key => $button){
                                    echo '<option value="'. $key.'" '. (!empty($options['button_style'])&& $options['button_style'] == $key? "selected": "").'>'. $button .'</option>';
                                }
                                ?>
                            </select>
                            <label for="pl8app_settings[button_style]"> Choose the style you want to use for
                                the buttons.</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Site Background Color</th>
                        <td>
                            <?php
                            $arg = array(
                                'section' => 'main',
                                'id' => 'site_background_color',
                                'desc' => 'Choose the color you want to use for the buttons and links.',
                                'name' => 'Theme Color',
                                'size' => '',
                                'options' => '',
                                'std' => '',
                                'min' => '',
                                'max' => '',
                                'step' => '',
                                'chosen' => '',
                                'multiple' => '',
                                'placeholder' => '',
                                'allow_blank' => 1,
                                'readonly' => '',
                                'faux' => '',
                                'tooltip_title' => '',
                                'tooltip_desc' => '',
                                'field_class' => '',
                                'type' => 'color'
                            );
                            pl8app_color_callback($arg);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Site Font</th>
                        <td>
                            <div class="font-wrapper">
                                <div class="font-family">
                                    <h5>Font Family</h5>
                                    <input type="text" name="pl8app_settings[site_font_family]"
                                           value="<?PHP echo !empty($options['site_font_family']) ? $options['site_font_family'] : ''; ?>">
                                </div>
                                <div class="variant">
                                    <h5>Variant</h5>
                                    <input type="text" name="pl8app_settings[site_font_variant]"
                                           value="<?PHP echo !empty($options['site_font_variant']) ? $options['site_font_variant'] : '300'; ?>">
                                </div>
                                <div class="font-size">
                                    <h5>Font Size</h5>
                                    <input type="text" name="pl8app_settings[site_font_size]"
                                           value="<?PHP echo !empty($options['site_font_size']) ? $options['site_font_size'] : '14px'; ?>">
                                </div>
                                <div class="line-height">
                                    <h5>Line Height</h5>
                                    <input type="text" name="pl8app_settings[site_font_line_height]"
                                           value="<?PHP echo !empty($options['site_font_line_height']) ? $options['site_font_line_height'] : 1.5; ?>">
                                </div>
                                <div class="letter-spacing">
                                    <h5>Letter Spacing</h5>
                                    <input type="text" name="pl8app_settings[site_font_letter_spacing]"
                                           value="<?PHP echo !empty($options['site_font_letter_spacing']) ? $options['site_font_letter_spacing'] : '0'; ?>">
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Site Content Color</th>
                        <td>
                            <?php
                            $arg = array(
                                'section' => 'main',
                                'id' => 'site_content_color',
                                'desc' => 'Choose the color you want to use for footer.',
                                'name' => 'Theme Color',
                                'size' => '',
                                'options' => '',
                                'std' => '',
                                'min' => '',
                                'max' => '',
                                'step' => '',
                                'chosen' => '',
                                'multiple' => '',
                                'placeholder' => '',
                                'allow_blank' => 1,
                                'readonly' => '',
                                'faux' => '',
                                'tooltip_title' => '',
                                'tooltip_desc' => '',
                                'field_class' => '',
                                'type' => 'color'
                            );
                            pl8app_color_callback($arg);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Main Color</th>
                        <td>
                            <?php
                            $arg = array(
                                'section' => 'main',
                                'id' => 'primary_color',
                                'desc' => 'Choose the color you want to use for the buttons and links.',
                                'name' => 'Theme Color',
                                'size' => '',
                                'options' => '',
                                'std' => '',
                                'min' => '',
                                'max' => '',
                                'step' => '',
                                'chosen' => '',
                                'multiple' => '',
                                'placeholder' => '',
                                'allow_blank' => 1,
                                'readonly' => '',
                                'faux' => '',
                                'tooltip_title' => '',
                                'tooltip_desc' => '',
                                'field_class' => '',
                                'type' => 'color'
                            );
                            pl8app_color_callback($arg);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Hover Color</th>
                        <td>
                            <?php
                            $arg = array(
                                'section' => 'main',
                                'id' => 'hover_color',
                                'desc' => 'Choose the color you want to use for the buttons and links\' hover.',
                                'name' => 'Theme Color',
                                'size' => '',
                                'options' => '',
                                'std' => '',
                                'min' => '',
                                'max' => '',
                                'step' => '',
                                'chosen' => '',
                                'multiple' => '',
                                'placeholder' => '',
                                'allow_blank' => 1,
                                'readonly' => '',
                                'faux' => '',
                                'tooltip_title' => '',
                                'tooltip_desc' => '',
                                'field_class' => '',
                                'type' => 'color'
                            );
                            pl8app_color_callback($arg);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Nav Menu Color</th>
                        <td>
                            <?php
                            $arg = array(
                                'section' => 'main',
                                'id' => 'nav_menu_color',
                                'desc' => 'Choose the color you want to use for Nav menu.',
                                'name' => 'Theme Color',
                                'size' => '',
                                'options' => '',
                                'std' => '',
                                'min' => '',
                                'max' => '',
                                'step' => '',
                                'chosen' => '',
                                'multiple' => '',
                                'placeholder' => '',
                                'allow_blank' => 1,
                                'readonly' => '',
                                'faux' => '',
                                'tooltip_title' => '',
                                'tooltip_desc' => '',
                                'field_class' => '',
                                'type' => 'color'
                            );
                            pl8app_color_callback($arg);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Body Content Background Color</th>
                        <td>
                            <?php
                            $arg = array(
                                'section' => 'main',
                                'id' => 'body_color',
                                'desc' => 'Choose the color you want to use for main body.',
                                'name' => 'Theme Color',
                                'size' => '',
                                'options' => '',
                                'std' => '',
                                'min' => '',
                                'max' => '',
                                'step' => '',
                                'chosen' => '',
                                'multiple' => '',
                                'placeholder' => '',
                                'allow_blank' => 1,
                                'readonly' => '',
                                'faux' => '',
                                'tooltip_title' => '',
                                'tooltip_desc' => '',
                                'field_class' => '',
                                'type' => 'color'
                            );
                            pl8app_color_callback($arg);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Footer Color</th>
                        <td>
                            <?php
                            $arg = array(
                                'section' => 'main',
                                'id' => 'footer_color',
                                'desc' => 'Choose the color you want to use for footer.',
                                'name' => 'Theme Color',
                                'size' => '',
                                'options' => '',
                                'std' => '',
                                'min' => '',
                                'max' => '',
                                'step' => '',
                                'chosen' => '',
                                'multiple' => '',
                                'placeholder' => '',
                                'allow_blank' => 1,
                                'readonly' => '',
                                'faux' => '',
                                'tooltip_title' => '',
                                'tooltip_desc' => '',
                                'field_class' => '',
                                'type' => 'color'
                            );
                            pl8app_color_callback($arg);
                            ?>
                        </td>
                    </tr>
                </table>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?PHP
    echo ob_get_clean();
}