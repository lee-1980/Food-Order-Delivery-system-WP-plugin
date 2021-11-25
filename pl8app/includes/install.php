<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'menuitems' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages. After successful install, the user is redirected to the pl8app Welcome
 * screen.
 *
 * @since 1.0
 * @global $wpdb
 * @global $pl8app_options
 * @param  bool $network_side If the plugin is being network-activated
 * @return void
 */
function pl8app_install( $network_wide = false ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {
			switch_to_blog( $blog_id );
			pl8app_run_install();
			restore_current_blog();
		}
	} else {
		pl8app_run_install();
	}

}
register_activation_hook( PL8_PLUGIN_FILE, 'pl8app_install' );

/**
 * Run the pl8app Install process
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_run_install() {

	global $wpdb, $pl8app_options;

	// Setup the pl8app Custom Post Type
	pl8app_setup_pl8app_post_types();

	// Setup the Taxonomies
	pl8app_setup_menuitem_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Add Upgraded From Option
	$current_version = get_option( 'pl8app_version' );
	if ( $current_version ) {
		update_option( 'pl8app_version_upgraded_from', $current_version );
	}

    if(wp_get_nav_menu_object('Main Menu')){
        $menu_object = wp_get_nav_menu_object('Main Menu');
        $new_menu_id = $menu_object->term_id;
    }
    else{
        $new_menu_id = wp_create_nav_menu('Main Menu');
    }



	// Setup some default options
	$options = array();

	// Pull options from WP, not pl8app's global
	$current_options = get_option( 'pl8app_settings', array() );


	// Checks if the purchase page option exists
	$purchase_page = array_key_exists( 'purchase_page', $current_options ) ? get_post( $current_options['purchase_page'] ) : false;
	if ( empty( $purchase_page ) ) {
		// Checkout Page
		$checkout = wp_insert_post(
			array(
				'post_title'     => __( 'Checkout', 'pl8app' ),
				'post_content'   => '[menuitem_checkout]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['purchase_page'] = $checkout;
	}

	$checkout = isset( $checkout ) ? $checkout : $current_options['purchase_page'];

	$success_page = array_key_exists( 'success_page', $current_options ) ? get_post( $current_options['success_page'] ) : false;
	if ( empty( $success_page ) ) {
		// Purchase Confirmation (Success) Page
		$success = wp_insert_post(
			array(
				'post_title'     => __( 'Order Confirmation', 'pl8app' ),
				'post_content'   => __( '[pl8app_receipt]', 'pl8app' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $checkout,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['success_page'] = $success;
	}

	$failure_page = array_key_exists( 'failure_page', $current_options ) ? get_post( $current_options['failure_page'] ) : false;
	if ( empty( $failure_page ) ) {
		// Failed Purchase Page
		$failed = wp_insert_post(
			array(
				'post_title'     => __( 'Transaction Failed', 'pl8app' ),
				'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'pl8app' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		$options['failure_page'] = $failed;
	}

	$history_page = array_key_exists( 'order_history_page', $current_options ) ? get_post( $current_options['order_history_page'] ) : false;
	if ( empty( $history_page ) ) {
		// Order History (History) Page
		$history = wp_insert_post(
			array(
				'post_title'     => __( 'Orders', 'pl8app' ),
				'post_content'   => '[order_history]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		$options['order_history_page'] = $history;
	}

	$menuitems = array_key_exists( 'menu_items_page', $current_options ) ? get_post( $current_options['menu_items_page'] ) : false;
	if ( empty( $menuitems ) ) {
		// Menu Item (Menu Item) Page
		$menuitem = wp_insert_post(
			array(
				'post_title'     => __( 'Menu Items', 'pl8app' ),
				'post_content'   => '[menuitems]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['menu_items_page'] = $menuitem;
		$options['login_redirect_page'] = $menuitem;
        update_option( 'page_on_front', $options['menu_items_page'] );
        update_option( 'show_on_front', 'page' );
    }



    $allergy = array_key_exists( 'allergy_page', $current_options ) ? get_post( $current_options['allergy_page'] ) : false;
    if ( empty( $allergy ) ) {

        $page_content = '<h3>Do you have a food allergy?</h3>';
        $page_content .= '<p>Your food and its packaging may come into contact with allergens during preparation, cooking or delivery.</p>';
        $page_content .= '<p>The food is produced in kitchens where allergens are handled and where equipment and utensils are used for multiple menu items, including those containing allergens.</p>';
        $page_content .= '<p>If you have a food allergy or intolerance (or someone you’re ordering for has), phone the restaurant on [pl8app_store_phone], use the Contact Form below.</p>';
        $page_content .= '[pl8app_allergyform]';
        $allergy = wp_insert_post(
            array(
                'post_title'     => __( 'Do you have a Food Allergy?', 'pl8app' ),
                'post_content'   => $page_content,
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );

        $options['allergy_page'] = $allergy;

    }

    $thank_you = array_key_exists( 'thank_you_page', $current_options ) ? get_post( $current_options['thank_you_page'] ) : false;
    if ( empty( $thank_you ) ) {

        $page_content = '<p>Thank you for your payment. Your transaction has been completed and we’ve emailed you a receipt for your purchase. Log in to your PayPal account to view transaction details.</p>';

        $thank_you = wp_insert_post(
            array(
                'post_title'     => __( 'Thank you for your payment', 'pl8app' ),
                'post_content'   => $page_content,
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );

        $options['thank_you_page'] = $thank_you;

    }

    $contact_us = array_key_exists( 'contact_us_page', $current_options ) ? get_post( $current_options['contact_us_page'] ) : false;
    if ( empty( $contact_us ) ) {

        $contact_us = wp_insert_post(
            array(
                'post_title'     => __( 'Contact Us', 'pl8app' ),
                'post_content'   => '[pl8app_contactform]',
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );

        $options['contact_us_page'] = $contact_us;

    }

    $faq = array_key_exists( 'faq_page', $current_options ) ? get_post( $current_options['faq_page'] ) : false;
    if ( empty( $faq ) ) {

        $faq = wp_insert_post(
            array(
                'post_title'     => __( 'FAQ', 'pl8app' ),
                'post_content'   => '[pl8app_faq]',
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );
        $options['faq_page'] = $faq;
    }

    $delivery_refund = array_key_exists( 'delivery_refund_page', $current_options ) ? get_post( $current_options['delivery_refund_page'] ) : false;
    if ( empty( $delivery_refund ) ) {

        $delivery_refund = wp_insert_post(
            array(
                'post_title'     => __( 'Delivery, Returns AND Refunds', 'pl8app' ),
                'post_content'   => '[pl8app_delivery_refund]',
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );
        $options['delivery_refund_page'] = $delivery_refund;
    }

    $privacy_policy = array_key_exists( 'privacy_page', $current_options ) ? get_post( $current_options['privacy_page'] ) : false;
    if ( empty( $privacy_policy ) ) {

        $page_content = '<h2>Who we are?</h2>';
        $page_content .= '<p>Our website address is: '.site_url() .'</p><br>';
        $page_content .= '<h2>What personal data we collect and why we collect it</h2>';
        $page_content .= '<p>This privacy policy has been compiled to better serve those who are concerned with how their ‘Personally Identifiable Information’ (PII) is being used online. PII, as described in US privacy law and information security, is information that can be used on its own or with other information to identify, contact, or locate a single person, or to identify an individual in context. Please read our privacy policy carefully to get a clear understanding of how we collect, use, protect or otherwise handle your Personally Identifiable Information in accordance with our website.</p>';
        $page_content .= '<p><strong>What personal information do we collect from the people that visit our blog, website or app?</strong> When ordering or registering on our site, as appropriate, you may be asked to enter your name, email address, mailing address, phone number or other details to help you with your experience.</p>';
        $page_content .= '<p><strong>When do we collect information?</strong> We collect information from you when you register on our site, place an order, subscribe to a newsletter, fill out a form, Open a Support Ticket or enter information on our site.</p>';
        $page_content .= '<p><strong>How do we use your information?</strong> We may use the information we collect from you when you register, make a purchase, sign up for our newsletter, respond to a survey or marketing communication, surf the website, or use certain other site features in the following ways:</p>';
        $page_content .= '<ul>
                           <li>To personalize your experience and to allow us to deliver the type of content and product offerings in which you are most interested.</li>
                           <li>To improve our website in order to better serve you.</li>
                           <li>To allow us to better service you in responding to your customer service requests.</li>
                           <li>To quickly process your transactions.</li>
                          </ul>';
        $page_content .= '<p><strong>How do we protect your information?</strong> We do not use vulnerability scanning and/or scanning to PCI standards.We only provide articles and information. We never ask for credit card numbers.We use regular Malware Scanning.</p>';
        $page_content .= '<p>Your personal information is contained behind secured networks and is only accessible by a limited number of persons who have special access rights to such systems, and are required to keep the information confidential. We implement a variety of security measures when a user places an order enters, submits, or accesses their information to maintain the safety of your personal information. All transactions are processed through a gateway provider and are not stored or processed on our servers. <strong>Do we use ‘cookies’?</strong> Yes. Cookies are small files that a site or its service provider transfers to your computer’s hard drive through your Web browser (if you allow) that enables the site’s or service provider’s systems to recognize your browser and capture and remember certain information. For instance, we use cookies to help us remember and process the items in your shopping cart. They are also used to help us understand your preferences based on previous or current site activity, which enables us to provide you with improved services. We also use cookies to help us compile aggregate data about site traffic and site interaction so that we can offer better site experiences and tools in the future. <strong>We use cookies to:</strong></p>';
        $page_content .= '<ul>
                           <li>Help remember and process the items in the shopping cart.</li>
                           <li>Understand and save user’s preferences for future visits. You can choose to have your computer warn you each time a cookie is being sent, or you can choose to turn off all cookies. You do this through your browser settings. Since browser is a little different, look at your browser’s Help Menu to learn the correct way to modify your cookies.</li>
                          </ul>';
        $page_content .= '<p><strong>If users disable cookies in their browser:</strong> If you turn cookies off, Some of the features that make your site experience more efficient may not function properly. Some of the features that make your site experience more efficient and may not function properly.</p>';
        $page_content .= '<p><strong>Third-party disclosure</strong> We do not sell, trade, or otherwise transfer to outside parties your Personally Identifiable Information.</p>';
        $page_content .= '<p><strong>Third-party links</strong> Occasionally, at our discretion, we may include or offer third-party products or services on our website. These third-party sites have separate and independent privacy policies. We therefore have no responsibility or liability for the content and activities of these linked sites. Nonetheless, we seek to protect the integrity of our site and welcome any feedback about these sites.</p>';
        $page_content .= '<p><strong>Google </strong> Google’s advertising requirements can be summed up by Google’s Advertising Principles. They are put in place to provide a positive experience for users. https://support.google.com/adwordspolicy/answer/1316548?hl=en</p>';
        $page_content .= '<p>We use Google AdSense Advertising on our website. Google, as a third-party vendor, uses cookies to serve ads on our site. Google’s use of the DART cookie enables it to serve ads to our users based on previous visits to our site and other sites on the Internet. Users may opt-out of the use of the DART cookie by visiting the Google Ad and Content Network privacy policy.</p>';
        $page_content .= '<p><strong>We have implemented the following:</strong></p>';
        $page_content .= '<ul>
                           <li>Google Display Network Impression Reporting</li>
                           <li>Demographics and Interests Reporting We, along with third-party vendors such as Google use first-party cookies (such as the Google Analytics cookies) and third-party cookies (such as the DoubleClick cookie) or other third-party identifiers together to compile data regarding user interactions with ad impressions and other ad service functions as they relate to our website.</li>
                          </ul>';
        $page_content .= '<p><strong>Opting out:</strong> Users can set preferences for how Google advertises to you using the Google Ad Settings page. Alternatively, you can opt out by visiting the Network Advertising Initiative Opt Out page or by using the Google Analytics Opt Out Browser add on.</p>';
        $page_content .= '<p><strong>California Online Privacy Protection Act </strong> CalOPPA is the first state law in the nation to require commercial websites and online services to post a privacy policy. The law’s reach stretches well beyond California to require any person or company in the United States (and conceivably the world) that operates websites collecting Personally Identifiable Information from California consumers to post a conspicuous privacy policy on its website stating exactly the information being collected and those individuals or companies with whom it is being shared. – See more at: http://consumercal.org/california-online-privacy-protection-act-caloppa/#sthash.0FdRbT51.dpuf</p>';
        $page_content .= '<p><strong>According to CalOPPA, we agree to the following:</strong> Users can visit our site anonymously.Once this privacy policy is created, we will add a link to it on our home page or as a minimum, on the first significant page after entering our website. Our Privacy Policy link includes the word ‘Privacy’ and can easily be found on the page specified above. You will be notified of any Privacy Policy changes:</p>';
        $page_content .= '<ul>
                           <li>On our Privacy Policy Page Can change your personal information:</li>
                           <li>By logging in to your account</li>
                          </ul>';
        $page_content .= '<p><strong>How does our site handle Do Not Track signals?</strong> We honor Do Not Track signals and Do Not Track, plant cookies, or use advertising when a Do Not Track (DNT) browser mechanism is in place. <strong>Does our site allow third-party behavioral tracking?</strong> It’s also important to note that we do not allow third-party behavioral tracking <strong>COPPA (Children Online Privacy Protection Act)</strong> When it comes to the collection of personal information from children under the age of 13 years old, the Children’s Online Privacy Protection Act (COPPA) puts parents in control. The Federal Trade Commission, United States’ consumer protection agency, enforces the COPPA Rule, which spells out what operators of websites and online services must do to protect children’s privacy and safety online.</p>';
        $page_content .= '<p>We do not market to people under the age of 18 years old. <strong>Fair Information Practices</strong> The Fair Information Practices Principles form the backbone of privacy law in the United States and the concepts they include have played a significant role in the development of data protection laws around the globe. Understanding the Fair Information Practice Principles and how they should be implemented is critical to comply with the various privacy laws that protect personal information.</p>';
        $page_content .= '<p><strong>In order to be in line with Fair Information Practices we will take the following responsive action, should a data breach occur:</strong>We will notify you via email</p>';
        $page_content .= '<ul>
                           <li>Within 1 business day We will notify the users via in-site notification</li>
                           <li>Within 1 business day</li>
                          </ul>';
        $page_content .= '<p>We also agree to the Individual Redress Principle which requires that individuals have the right to legally pursue enforceable rights against data collectors and processors who fail to adhere to the law. This principle requires not only that individuals have enforceable rights against data users, but also that individuals have recourse to courts or government agencies to investigate and/or prosecute non-compliance by data processors.</p>';
        $page_content .= '<p><strong>CAN SPAM Act</strong> The CAN-SPAM Act is a law that sets the rules for commercial email, establishes requirements for commercial messages, gives recipients the right to have emails stopped from being sent to them, and spells out tough penalties for violations.</p>';
        $page_content .= '<p><strong>We collect your email address in order to:</strong></p>';
        $page_content .= '<ul>
                           <li>Send information, respond to inquiries, and/or other requests or questions</li>
                           <li>Within 1 business day</li>
                           <li>Send you additional information related to your product and/or service
                               <ul>
                               <li>Send you a request to review the products after purchasing (with your explicit permission at time of purchase)</li>
                               </ul>
                           </li>
                          </ul>';
        $page_content .= '<p><strong>To be in accordance with CANSPAM, we agree to the following:</strong></p>';

        $page_content .= '<ul>
                           <li>Not use false or misleading subjects or email addresses.</li>
                           <li>Identify the message as an advertisement in some reasonable way.</li>
                           <li>Include the physical address of our business or site headquarters.</li>
                           <li>Monitor third-party email marketing services for compliance, if one is used.</li>
                           <li>Honor opt-out/unsubscribe requests quickly.</li>
                           <li>Allow users to unsubscribe by using the link at the bottom of each email.</li>
                          </ul>';

        $page_content .= '<p><strong>If at any time you would like to unsubscribe from receiving future emails, you can email us at</strong></p>';

        $page_content .= '<ul>
                           <li>Follow the instructions at the bottom of each email.and we will promptly remove you from <strong>ALL</strong> correspondence.</li>
                          </ul>';
        $page_content .= '<p><strong>Payment Gateway</strong> As our store is operated on a pl8app based platform, we use Paypal as our payment gateway, upon conducting a transaction this will migrate you to PayPal to securely process payment.</p>';
        $page_content .= '<p>Cash and In-Store Card Processing will always be an option. We also offer some Cryptocurrency payments however which coins/tokens we accept is constantly evolving, please check the checkout options for which coins/tokens currently accepted.</p>';
        $page_content .= '<p><strong>Contacting Us</strong> If there are any questions regarding this privacy policy, you may contact us using the information below.</p>';
        $page_content .= '{%pl8app_store_contact_information%}';

        $privacy_policy = wp_insert_post(
            array(
                'post_title'     => __( 'Privacy Policy', 'pl8app' ),
                'post_content'   => do_shortcode('[pl8app_privacy_policy_content]'),
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
                'comment_status' => 'closed'
            )
        );
        $options['privacy_page'] = $privacy_policy;
        $options['agree_text'] = $page_content;
        $options['privacy_policy_content'] = $page_content;
    }




    // Add Menu item page to menu

    if(isset($options['menu_items_page']))
    wp_update_nav_menu_item( $new_menu_id , 0, array(
        'menu-item-title' => 'Menu Items',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $options['menu_items_page'],
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish'
    ) );

    // Add Order page to menu
    if(isset($options['order_history_page']))
    wp_update_nav_menu_item( $new_menu_id , 0, array(
        'menu-item-title' => 'Orders',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $options['order_history_page'],
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish'
    ) );

    // Add Check page to menu
    if(isset($options['purchase_page']))
    wp_update_nav_menu_item( $new_menu_id , 0, array(
        'menu-item-title' => 'Checkout',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $options['purchase_page'],
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish'
    ) );

    // Add FAQ page to menu
    if(isset($options['faq_page']))
    wp_update_nav_menu_item( $new_menu_id , 0, array(
        'menu-item-title' => 'FAQ',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $options['faq_page'],
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish'
    ) );

    // Add Contact Us page to menu
    if(isset($options['contact_us_page']))
    wp_update_nav_menu_item( $new_menu_id , 0, array(
        'menu-item-title' => 'Contact Us',
        'menu-item-object' => 'page',
        'menu-item-object-id' => $options['contact_us_page'],
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish'
    ) );

    // Add Reviews page to menu
    if(isset($options['reviews_page']))
        wp_update_nav_menu_item( $new_menu_id , 0, array(
            'menu-item-title' => 'Reviews',
            'menu-item-object' => 'page',
            'menu-item-object-id' => $options['reviews_page'],
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish'
        ) );

    $locations = get_theme_mod('nav_menu_locations');
    $locations['pl8app_main_menu'] = $new_menu_id;
    // set our new MENU up at our theme's nav menu location
    set_theme_mod( 'nav_menu_locations' , $locations );


    // Set the Top bar

    // pl8app_socials
    $options['pl8app_socials'] = 1;
    // contact_us on the left side in the top bar
    if(isset($options['contact_us_page'])){
        $content_us_link = get_permalink($options['contact_us_page']);
        $options['twp_social_envelope-o'] = $content_us_link;
    }
    else if(isset($current_options['contact_us_page'])){
        $content_us_link = get_permalink($current_options['contact_us_page']);
        $options['twp_social_envelope-o'] = $content_us_link;
    }

    // allergies page on the right side in the top bar
    if(isset($options['allergy_page'])){
        $allergies_link = get_permalink($options['allergy_page']);
        $options['infobox-text-right'] = '<a href="'. $allergies_link .'">'. __('Do you have a Food Allergy?', 'pl8app') .'</a>';
    }
    else if(isset($current_options['allergy_page'])){
        $allergies_link = get_permalink($current_options['allergy_page']);
        $options['infobox-text-right'] = '<a href="'. $allergies_link .'">'. __('Do you have a Food Allergy?', 'pl8app') .'</a>';
    }

    // enable store notice

    $options['pl8app_enable_notice'] = 1;

    $options['pl8app_store_notice'] = __('Hello! everyone. pl8app starts it\'s way to provide the best service to all food business.', 'pl8app');


	// Populate some default values
	foreach( pl8app_get_registered_settings() as $tab => $sections ) {
		foreach( $sections as $section => $settings) {

			//Check for backwards compatibility
			$tab_sections = pl8app_get_settings_tab_sections( $tab );
			if( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
				$section = 'main';
				$settings = $sections;
			}

			foreach ( $settings as $option ) {

				if( ! empty( $option['type'] ) && 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
					$options[ $option['id'] ] = '1';
				}

			}
		}

	}

	// Set Complementary Close as Default

    $options['complementary_close'] = '<p style="color: #ffffff; text-align: center;">Service Provided by pl8app</p>
                                       <p style="color: #ffffff; text-align: center;">Service Provided by pl8app</p>
                                       <p style="color: #ffffff; text-align: center;">Service Provided by pl8app</p>
                                       &nbsp;</br>&nbsp;';

	// Set the delivery_refund as Default
    $options['delivery_refund'] = array();
    $options['delivery_refund']['delivery'] = '<p>Currently we do not operate a delivery service, this may change in the future.</p>';
    $options['delivery_refund']['refund'] = '<p>We only accept returns for errors with items whilst you and your order is still on the premises, whilst every care is taken to prepare and pack your food mistakes can happen on rare occasions, it is your responsibility to ensure your order is correct before stepping outside of the building, after this we will not be able to refund you due to health issues.</p><p>For questions about your order to us please <a href="'.get_permalink($options['contact_us_page']).'">Contact Us</a></p>';


	// Set the default store email
    $options['pl8app_st_email'] = 'orders@'.$_SERVER['HTTP_HOST'];
    $options['from_email'] = 'orders@'.$_SERVER['HTTP_HOST'];

    //create the pl8app default discount code
    $posted = array(
        'name'   => 'pl8app_default_discount',
        'code'   => 'pl8app_default_review_discount_5_percent',
        'type'   => 'percent',
        'amount' => '5'
    );

    // Ensure this discount doesn't already exist
    if ( ! pl8app_get_discount_by_code( $posted['code'] ) ) {
        // Set the discount code's default status to active
        $posted['status'] = 'active';
        pl8app_store_discount( $posted ) ;
    }

	$merged_options = array_merge( $pl8app_options, $options );
	$pl8app_options    = $merged_options;

	update_option( 'pl8app_settings', $merged_options );
	update_option( 'pl8app_version', PL8_VERSION );
    update_option( 'pl8app_current_version_copyright', date('Y') );

	// Create wp-content/uploads/pl8app/ folder and the .htaccess file
	// pl8app_create_protection_files( true );

	// Create pl8app shop roles
	$roles = new pl8app_Roles;
	$roles->add_roles();
	$roles->add_caps();

	// // Create the customer databases
	@PL8PRESS()->customers->create_table();
	@PL8PRESS()->customer_meta->create_table();

	// // Check for PHP Session support, and enable if available
	PL8PRESS()->session->use_php_sessions();

	//Make the SiteMap XML
    pl8app_xml_sitemap();
	// // Add a temporary option to note that pl8app pages have been created
	set_transient( '_pl8app_installed', $merged_options, 30 );
}

/**
 * When a new Blog is created in multisite, see if pl8app is network activated, and run the installer
 *
 * @since  1.0.0
 * @param  int    $blog_id The Blog ID created
 * @param  int    $user_id The User ID set as the admin
 * @param  string $domain  The URL
 * @param  string $path    Site Path
 * @param  int    $site_id The Site ID
 * @param  array  $meta    Blog Meta
 * @return void
 */
function pl8app_new_blog_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	if ( is_plugin_active_for_network( plugin_basename( PL8_PLUGIN_FILE ) ) ) {
		switch_to_blog( $blog_id );
		pl8app_install();
		restore_current_blog();
	}

}
add_action( 'wpmu_new_blog', 'pl8app_new_blog_created', 10, 6 );


/**
 * Drop our custom tables when a mu site is deleted
 *
 * @since  1.0.0
 * @param  array $tables  The tables to drop
 * @param  int   $blog_id The Blog ID being deleted
 * @return array          The tables to drop
 */
function pl8app_wpmu_drop_tables( $tables, $blog_id ) {

	switch_to_blog( $blog_id );
	$customers_db     = new pl8app_DB_Customers();
	$customer_meta_db = new pl8app_DB_Customer_Meta();
	if ( $customers_db->installed() ) {
		$tables[] = $customers_db->table_name;
		$tables[] = $customer_meta_db->table_name;
	}
	restore_current_blog();

	return $tables;

}
add_filter( 'wpmu_drop_tables', 'pl8app_wpmu_drop_tables', 10, 2 );

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * pl8app_after_install hook.
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$pl8app_options     = get_transient( '_pl8app_installed' );
	$pl8app_table_check = get_option( '_pl8app_table_check', false );

	if ( false === $pl8app_table_check || current_time( 'timestamp' ) > $pl8app_table_check ) {

		if ( ! @PL8PRESS()->customer_meta->installed() ) {

			// Create the customer meta database (this ensures it creates it on multisite instances where it is network activated)
			@PL8PRESS()->customer_meta->create_table();

		}

		if ( ! @PL8PRESS()->customers->installed() ) {
			// Create the customers database (this ensures it creates it on multisite instances where it is network activated)
			@PL8PRESS()->customers->create_table();
			@PL8PRESS()->customer_meta->create_table();

			do_action( 'pl8app_after_install', $pl8app_options );
		}

		update_option( '_pl8app_table_check', ( current_time( 'timestamp' ) + WEEK_IN_SECONDS ) );

	}

	if ( false !== $pl8app_options ) {
		// Delete the transient
		delete_transient( '_pl8app_installed' );
	}


}
add_action( 'admin_init', 'pl8app_after_install' );

/**
 *  Make the Site Map XML
 */

function pl8app_xml_sitemap() {
    $postsForSitemap = get_posts(array(
        'numberposts' => -1,
        'orderby' => 'modified',
        'post_type'  => array('page'),
        'order'    => 'DESC'
    ));

    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    foreach($postsForSitemap as $post) {
        setup_postdata($post);

        $postdate = explode(" ", $post->post_modified);

        $sitemap .= '<url>'.
            '<loc>'. get_permalink($post->ID) .'</loc>'.
            '<lastmod>'. $postdate[0] .'</lastmod>'.
            '<changefreq>monthly</changefreq>'.
            '</url>';
    }

    $sitemap .= '</urlset>';

    $fp = fopen(ABSPATH . "pl8app_sitemap.xml", 'w');
    fwrite($fp, $sitemap);
    fclose($fp);
}

add_action("publish_post", "pl8app_xml_sitemap");
add_action("publish_page", "pl8app_xml_sitemap");

/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when pl8app is network activation so we need to create them during admin_init
 *
 * @since  1.0.0
 * @return void
 */
function pl8app_install_roles_on_network() {

	global $wp_roles;

	if( ! is_object( $wp_roles ) ) {
		return;
	}


	if( empty( $wp_roles->roles ) || ! array_key_exists( 'shop_manager', $wp_roles->roles ) ) {

		// Create pl8app shop roles
		$roles = new pl8app_Roles;
		$roles->add_roles();
		$roles->add_caps();

	}

}
add_action( 'admin_init', 'pl8app_install_roles_on_network' );



/**
 * Checks whether migration is needed or not
 *
 *
 * @since  2.6
 * @return bool
 */
function pl8app_needs_migration() {

	$current_version = get_option( 'pl8app_version', true  );

	if ( empty( $current_version ) ) {
		$current_version = '2.5';
	}

	if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( $current_version, PL8PRESS()->version, '<' ) ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Update post meta with terms
 *
 *
 * @since  2.6
 * @return mixed
 */
function pl8app_db_migration() {

	global $wpdb;

	$get_menuitems = $wpdb->get_results( "SELECT ID FROM {$wpdb->prefix}posts WHERE `post_type` = 'menuitem' ", ARRAY_A );

	if ( is_array( $get_menuitems ) && !empty( $get_menuitems ) ) {

		foreach( $get_menuitems as $key => $get_menuitem ) {

			$menuitem_id = $get_menuitem['ID'];

			//Get post terms
			$get_menuitems_terms = wp_get_post_terms( $menuitem_id, 'addon_category', array( 'fields' => 'id=>parent' ) );

			if( is_array( $get_menuitems_terms ) ) {

				$meta_term = array();

				foreach( $get_menuitems_terms as $term_id => $parent_id ) {

					if( $parent_id != 0 )
						continue;

					$meta_term[$term_id]['category'] = $term_id;
					$meta_term[$term_id]['items'] = array();
				}

				foreach( $get_menuitems_terms as $term_id => $parent_id ) {
					if( $parent_id == 0 )
						continue;

					if( isset( $meta_term[$parent_id]['items'] ) )
						array_push( $meta_term[$parent_id]['items'], $term_id );
				}
			}

			// Update Post Meta
			update_post_meta( $menuitem_id, '_addon_items', $meta_term );
		}
	}
}



function pl8app_check_migartion() {
	if ( pl8app_needs_migration() ) {
  	pl8app_db_migration();
    delete_option( 'pl8app_version' );
    add_option( 'pl8app_version', PL8PRESS()->version );
  }
}

add_action( 'admin_init', 'pl8app_check_migartion' );

