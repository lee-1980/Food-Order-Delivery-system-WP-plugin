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

function pl8app_store_location_page()
{

    $countries = pl8app_get_country_list();
    $options = get_option('pl8app_settings');

    if(!empty($options['base_country'])){
        $states = pl8app_get_states($options['base_country']);
    }
    else{
        $options['base_country'] = '';
    }

    ob_start();

    ?>
    <div class="wrap wrap-st-location">
        <h2><?php _e('Pl8App Store Location', 'pl8app'); ?></h2>

        <div id="tab_container">
            <form method="post" action="options.php">
                <table class="form-table pl8app-settings">

                    <?PHP settings_fields( 'pl8app_settings' );?>
                </table>
                <div class="pl8app-wrapper">
                    <div class="pl8app-store-information">
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row">Store Name</th>
                                <td>
                                    <input type="text" class="regular-text" id="pl8app_settings[pl8app_store_name]"
                                           name="pl8app_settings[pl8app_store_name]" placeholder="" value="<?PHP echo !empty($options['pl8app_store_name'])?$options['pl8app_store_name']:''; ?>">
                                    <label for="pl8app_settings[pl8app_store_name]"></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Street Address</th>
                                <td>
                                    <input type="text" class="regular-text" id="pl8app_settings[pl8app_street_address]"
                                           name="pl8app_settings[pl8app_street_address]" placeholder="" value="<?PHP echo !empty($options['pl8app_street_address'])?$options['pl8app_street_address']:''; ?>">
                                    <label for="pl8app_settings[pl8app_street_address]"></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Town</th>
                                <td>
                                    <input type="text" class=" regular-text" id="pl8app_settings[pl8app_town]"
                                           name="pl8app_settings[pl8app_town]" placeholder="" value="<?PHP echo !empty($options['pl8app_town'])?$options['pl8app_town']:''; ?>">
                                    <label for="pl8app_settings[pl8app_town]"></label>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">Country</th>
                                <td>
                                    <select class="pl8app-select-chosen" id="pl8app_settings[base_country]"
                                            name="pl8app_settings[base_country]" data-placeholder="Select a country"
                                            readonly="readonly" value="<?PHP echo !empty($options['base_country'])?$options['base_country']:''; ?>">
                                        <?php
                                        foreach ($countries as $key => $country){
                                            echo '<option value="'. $key.'" '. (isset($options['base_country'])&& $options['base_country'] === $key? "selected": "").'>'. $country .'</option>';
                                        }
                                        ?>
                                    </select>
                                    <label for="pl8app_settings[base_country]"></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">County/State/Provance</th>
                                <td>
                                    <select id="pl8app_settings[base_state]" name="pl8app_settings[base_state]" pl8app-chosendata-placeholder="Select a state">
                                        <?php
                                        if(isset($states)&&!empty($states))
                                            foreach ($states as $key => $state){
                                                echo '<option value="'. $key.'" '. (isset($options['base_state'])&& $options['base_state'] === $key? "selected": "").'>'. $state .'</option>';
                                            }
                                        ?>
                                    </select>
                                    <label for="pl8app_settings[base_state]"></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Postcode/Zipcode</th>
                                <td>
                                    <input type="text" class=" regular-text" id="pl8app_settings[pl8app_pz_code]"
                                           name="pl8app_settings[pl8app_pz_code]" value="<?PHP echo !empty($options['pl8app_pz_code'])?$options['pl8app_pz_code']:''; ?>">
                                    <label for="pl8app_settings[pl8app_pz_code]"></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Phone Number</th>
                                <td>
                                    <input type="tel" class=" regular-text" id="pl8app_settings[pl8app_phone_number]"
                                           name="pl8app_settings[pl8app_phone_number]" value="<?PHP echo !empty($options['pl8app_phone_number'])?$options['pl8app_phone_number']:''; ?>">
                                    <label for="pl8app_settings[pl8app_phone_number]"></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Store Email</th>
                                <td>
                                    <input type="Email" class=" regular-text" id="pl8app_settings[pl8app_st_email]"
                                           name="pl8app_settings[pl8app_st_email]" value="<?PHP echo !empty($options['pl8app_st_email'])?$options['pl8app_st_email']:''; ?>">
                                    <label for="pl8app_settings[pl8app_st_email]"></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><h2>GeoLocation</h2></th>
                                <td>
                                    *Store latitude/longitude is detected and autogenerated when type or change address(Street, Town, Country, State, Code).
                                    <br>
                                    <br>
                                    Default Location is [53.958332, -1.080278].
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Store latitude</th>
                                <td>
                                    <input type="text" class=" regular-text" id="pl8app_settings[pl8app_st_latitude]"
                                           name="pl8app_settings[pl8app_st_latitude]" value="<?PHP echo !empty($options['pl8app_st_latitude'])?$options['pl8app_st_latitude']:''; ?>">
                                    <label for="pl8app_settings[pl8app_st_latitude]"></label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Store longitude</th>
                                <td>
                                    <input type="text" class=" regular-text" id="pl8app_settings[pl8app_st_longitude]"
                                           name="pl8app_settings[pl8app_st_longitude]" value="<?PHP echo !empty($options['pl8app_st_longitude'])?$options['pl8app_st_longitude']:''; ?>">
                                    <label for="pl8app_settings[pl8app_st_longitude]"></label>
                                </td>
                            </tr>

                        </table>
                    </div>
                    <div class="pl8app-store-information">
                        <div id="pl8app-store-location-map" style="width: 100%; height: 450px;"></div>
                        <span class="pl8app-osm-autodetect pl8app-hidden">With current address, can't detect latitude/longitude. Please try another address or type latitude/longitude manually.</span>
                        <span class="pl8app-osm-autodetect-text pl8app-hidden"></span>
                    </div>
                </div>
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    <?php
    echo ob_get_clean();
}