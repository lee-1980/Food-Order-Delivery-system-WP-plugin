<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

require_once dirname( __FILE__ ) . '/pl8app-meta-box-functions.php';

//Add menuitem metaboxes
require_once dirname( __FILE__ ) . '/menuitems/class-pl8app-menuitem-metaboxes.php';
require_once dirname( __FILE__ ) . '/class-pl8app-admin-assets.php';