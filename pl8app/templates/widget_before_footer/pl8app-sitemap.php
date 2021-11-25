<?php
$pages = pla_get_default_pages();
$current_options = get_option( 'pl8app_settings', array() );
?>

    <h3 class="widget-title">Site Map</h3>
    <ul>
        <?PHP foreach($pages as $key => $page) {
            if($current_options[$key]){
                $link = get_permalink((int)$current_options[$key]);
                $title = get_the_title((int)$current_options[$key]);
                ?>
                <li class="page_item"><a href="<?PHP echo $link;?>"><?PHP echo $title;?></a></li>
                <?PHP
            }
        } ?>
    </ul>



