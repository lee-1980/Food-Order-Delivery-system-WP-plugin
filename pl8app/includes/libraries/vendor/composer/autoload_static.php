<?php
/**
 * WP SendGrid Mailer Plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */

namespace Composer\Autoload;

class ComposerStaticInitb5e99aa7222f66a1a651b5dbe90dd87d
{
    public static $files = array (
        '3f8bdd3b35094c73a26f0106e3c0f8b2' => __DIR__ . '/..' . '/sendgrid/sendgrid/lib/SendGrid.php',
        '9dda55337a76a24e949fbcc5d905a2c7' => __DIR__ . '/..' . '/sendgrid/sendgrid/lib/helpers/mail/Mail.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPMailPlus\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPMailPlus\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'SendGrid' => 
            array (
                0 => __DIR__ . '/..' . '/sendgrid/php-http-client/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb5e99aa7222f66a1a651b5dbe90dd87d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb5e99aa7222f66a1a651b5dbe90dd87d::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitb5e99aa7222f66a1a651b5dbe90dd87d::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
