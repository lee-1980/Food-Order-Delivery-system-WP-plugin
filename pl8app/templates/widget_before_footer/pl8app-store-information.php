<?php

$countries = pl8app_get_country_list();
$options = get_option('pl8app_settings');

if(!empty($options['base_country'])){
    $states = pl8app_get_states($options['base_country']);
}
else{
    $options['base_country'] = '';
}

?>


    <h3 class="widget-title">Business Information</h3>
    <div class="pl8app-store-information">
        <table class="pl8app-store-information">
            <tbody>
            <tr>
                <th>Name</th>
                <td><?PHP echo !empty($options['pl8app_store_name'])?$options['pl8app_store_name']: ''; ?></td>
            </tr>
            <tr>
                <th>Street <i class="fa fa-road"></i></th>
                <td><?PHP echo !empty($options['pl8app_street_address'])?$options['pl8app_street_address']: ''; ?></td>
            </tr>
            <tr>
                <th>Town <i class="fa fa-building"></i></th>
                <td><?PHP echo !empty($options['pl8app_town'])?$options['pl8app_town']: ''; ?></td>
            </tr>
            <tr>
                <th>State <i class="fa fa-globe"></i></th>
                <td><?PHP echo !empty($options['base_country'])&&!empty($options['base_state'])?$states[$options['base_state']]: ''; ?></td>
            </tr>
            <tr>
                <th>Country <i class="fa fa-globe"></i></th>
                <td><?PHP echo !empty($options['base_country'])?$countries[$options['base_country']]: ''; ?></td>
            </tr>

            <tr>
                <th>Postcode <i class="fa fa-map-pin"></i></th>
                <td><?PHP echo !empty($options['pl8app_pz_code'])?$options['pl8app_pz_code']: ''; ?></td>
            </tr>
            <tr>
                <th>Phone <i class="fa fa-phone"></i></th>
                <td><?PHP echo !empty($options['pl8app_phone_number'])?$options['pl8app_phone_number']: ''; ?></td>
            </tr>
            </tbody>
        </table>
    </div>

