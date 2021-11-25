<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
    <?php wp_head(); ?>
</head>
<?php
$site_content_color = pl8app_get_option('site_content_color', '#a09c9c');
$site_background_color = pl8app_get_option('site_background_color', '#a09c9c');
$site_font_family = pl8app_get_option('site_font_family', '');
$site_font_variant = pl8app_get_option('site_font_variant', '300');
$site_font_size = pl8app_get_option('site_font_size', '14px');
$site_font_line_height = pl8app_get_option('site_font_line_height', '1.5');
$site_font_letter_spacing = pl8app_get_option('site_font_letter_spacing', '0px');
?>
<body id="blog" <?php body_class(); ?>
      style="
              color: <?php echo $site_content_color; ?>;
              background-color: <?php echo $site_background_color;?>;
              font-family: <?php echo $site_font_family;?>;
              font-variant: <?php echo $site_font_variant;?>;
              font-size: <?php echo $site_font_size;?>;
              line-height: <?php echo $site_font_line_height;?>;
              letter-spacing: <?php echo $site_font_letter_spacing;?>;
              ">
<?php wp_body_open(); ?>