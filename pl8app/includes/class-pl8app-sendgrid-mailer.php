<?php

/**
 * sendgrid-mailer, SMTP server configuration
 *
 * @package PL8_sendgrid_SMTP
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class PL8_sendgrid_SMTP {

    /**
     * PL8_sendgrid_SMTP version.
     *
     * @var string
     */
    public $version = '1.0';


    /**
     * The single instance of the class.
     *
     * @var PL8_sendgrid_SMTP
     * @since 1.0
     */
    protected static $_instance = null;

    /**
     * SendGrid SMTP configuration
     * @var array
     */

    public $smtp_setting = array();
    public $pl8app_setting = array();

    /**
     * Main PL8_sendgrid_SMTP Instance.
     *
     * Ensures only one instance of PL8_sendgrid_SMTP is loaded or can be loaded.
     *
     * @since 1.0
     * @static
     * @return PL8_sendgrid_SMTP - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();

        }
        return self::$_instance;
    }


    public function __construct()
    {
        $this->define_constants();
        $this->initHooks();
        $this->includes();
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }
    /**
     * Define Constants
     */
    private function define_constants() {
        $this->define( 'SENDGRID_CATEGORY', 'wp_sendgrid_plugin' );
    }


    /**
     * Include required files for settings
     *
     * @since 1.0
     */
    private function includes() {
        require_once PL8_PLUGIN_DIR . 'includes/libraries/vendor/autoload.php';
    }

    /**
     * Initiate Hooks
     */
    public function initHooks()
    {
        if(is_admin())
        {
            // Build Menu
            add_action('admin_menu', array($this, 'build_menu'));
        }

        $options = get_option('pl8app_settings');
        $this->smtp_setting = isset($options['smtp_config'])?$options['smtp_config']: array();
        $this->pl8app_setting = $options;

    }

    /**
     * Build admin menu
     */
    public function build_menu()

    {
        add_submenu_page( 'Yspl8app', __( 'pl8app Email SMTP Configuration', 'pl8app' ), __( 'Store Email SMTP Configuration', 'pl8app' ), 'manage_options', 'pl8app-store-email-smtp', array($this, 'output_setting') );
    }

    /**
     * render the SMTP configuration setting page
     */
    public function output_setting()
    {
        ob_start();
        $this->get_template_part( 'sendgrid_smtp' );
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }

    /**
     * Include template file
     *
     * @since  1.0
     * @param  string file name which would be included
     */
    public function get_template_part($template)
    {
        if (!empty($template)) {
            require PL8_PLUGIN_DIR . 'templates/emails/' . $template . '.php';
        }
    }
    /**
     * Retrieve token/key needed for Service
     * @return mixed
     */
    public function get_token()
    {
        return isset($this->smtp_setting['sendgrid_api_key'])?$this->smtp_setting['sendgrid_api_key']:'';
    }
    /**
     * Send Mail
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $headers
     * @param array $attachments
     */
    public function send_mail($to, $subject, $message, $headers, $attachments = '')
    {


        // Compact the input, apply the filters, and extract them back out
        extract( apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );


        $method = 'api';



        $api_key = $this->get_token();

        $mail = new SendGrid\Mail();
        $personalization = new SendGrid\Personalization();



        // Headers
        $cc  = array();
        $bcc = array();

        if (empty($headers)) {
            $headers = array();
        } else {
            if (!is_array($headers)) {
                // Explode the headers out, so this function can take both
                // string headers and an array of headers.
                $tempheaders = explode("\n", str_replace("\r\n", "\n", $headers));
            } else {
                $tempheaders = $headers;
            }
            $headers = array();

            // If it's actually got contents
            if (!empty($tempheaders)) {
                // Iterate through the raw headers
                foreach ((array)$tempheaders as $header) {
                    if (false === strpos($header, ':')) {
                        if (false !== stripos($header, 'boundary=')) {
                            $parts = preg_split('/boundary=/i', trim($header));
                            $boundary = trim(str_replace(array("'", '"'), '', $parts[1]));
                        }
                        continue;
                    }
                    // Explode them out
                    list($name, $content) = explode(':', trim($header), 2);

                    // Cleanup crew
                    $name = trim($name);
                    $content = trim($content);

                    switch (strtolower($name)) {
                        // Mainly for legacy -- process a From: header if it's there
                        case 'from':
                            if (false !== strpos($content, '<')) {
                                // So... making my life hard again?
                                $from_name = substr($content, 0, strpos($content, '<') - 1);
                                $from_name = str_replace('"', '', $from_name);
                                $from_name = trim($from_name);

                                $from_email = substr($content, strpos($content, '<') + 1);
                                $from_email = str_replace('>', '', $from_email);
                                $from_email = trim($from_email);
                            } else {
                                $from_email = trim($content);
                            }
                            break;
                        case 'content-type':
                            if (false !== strpos($content, ';')) {
                                list($type, $charset) = explode(';', $content);
                                $content_type = trim($type);
                                if (false !== stripos($charset, 'charset=')) {
                                    $charset = trim(str_replace(array('charset=', '"'), '', $charset));
                                } elseif (false !== stripos($charset, 'boundary=')) {
                                    $boundary = trim(str_replace(array('BOUNDARY=', 'boundary=', '"'), '', $charset));
                                    $charset = '';
                                }
                            } else {
                                $content_type = trim($content);
                            }
                            break;
                        case 'cc':
                            $cc = array_merge((array)$cc, explode(',', $content));
                            foreach ($cc as $key => $recipient) {
                                $cc[$key] = trim($recipient);
                            }
                            break;
                        case 'bcc':
                            $bcc = array_merge((array)$bcc, explode(',', $content));
                            foreach ($bcc as $key => $recipient) {
                                $bcc[$key] = trim($recipient);
                            }
                            break;
                        case 'reply-to':
                            $replyto = $content;
                            break;
                        case 'unique-args':
                            if ( false !== strpos( $content, ';' ) ) {
                                $unique_args = explode( ';', $content );
                            }
                            else {
                                $unique_args = (array) trim( $content );
                            }
                            foreach ( $unique_args as $unique_arg ) {
                                if ( false !== strpos( $content, '=' ) ) {
                                    list( $key, $val ) = explode( '=', $unique_arg );
                                    $personalization->addCustomArg( trim( $key ), trim( $val ) );
                                }
                            }
                            break;
                        case 'template':
                            $mail->setTemplateId(trim($content));
                            break;
                        case 'categories':
                            $categories = explode(',', trim($content));
                            foreach ($categories as $category) {
                                $mail->addCategory($category);
                            }
                            break;
                        case 'substitutions':
                            if (false !== strpos($content, ';')) {
                                $substitutions = explode(';', $content);
                            } else {
                                $substitutions = (array)trim($content);
                            }
                            foreach ($substitutions as $substitution) {
                                if (false !== strpos($content, '=')) {
                                    list($key, $val) = explode('=', $substitution);
                                    $personalization->addSubstitution('%' . trim($key) . '%', explode(',', trim($val)));
                                }
                            }
                            break;
                        case 'sections':
                            if (false !== strpos($content, ';')) {
                                $sections = explode(';', $content);
                            } else {
                                $sections = (array)trim($content);
                            }
                            foreach ($sections as $section) {
                                if (false !== strpos($content, '=')) {
                                    list($key, $val) = explode('=', $section);
                                    $mail->addSection('%' . trim($key) . '%', trim($val));
                                }
                            }
                            break;
                        default:
                            // Add it to our grand headers array
                            $headers[trim($name)] = trim($content);
                            break;
                    }
                }
            }

        }


        // From email and name
        // If we don't have a name from the input headers
        if ( ! isset( $from_name ) or ! $from_name ) {
            $from_name = htmlspecialchars_decode( isset($this->pl8app_setting['pl8app_store_name'])?$this->pl8app_setting['pl8app_store_name']:'' );
        }

        /* If we don't have an email from the input headers default to wordpress@$sitename
         * Some hosts will block outgoing mail from this address if it doesn't exist but
         * there's no easy alternative. Defaulting to admin_email might appear to be another
         * option but some hosts may refuse to relay mail from an unknown domain. See
         * http://trac.wordpress.org/ticket/5007.
         */

        if ( ! isset( $from_email ) ) {
            $from_email = trim( isset($this->pl8app_setting['pl8app_st_email'])?$this->pl8app_setting['pl8app_st_email']:'' );
            if (! $from_email) {
                // Get the site domain and get rid of www.
                $sitename = strtolower( $_SERVER['SERVER_NAME'] );
                if ( ! $sitename and ( 'smtp' == $method ) ) {
                    return false;
                }

                if ( 'www.' == substr( $sitename, 0, 4 ) ) {
                    $sitename = substr( $sitename, 4 );
                }

                $from_email = "wordpress@$sitename";
            }
        }

        // Plugin authors can override the potentially troublesome default
        $from_email = apply_filters( 'wp_mail_from'     , $from_email );
        $from_name  = apply_filters( 'wp_mail_from_name', $from_name  );



        // Add any CC and BCC recipients
        if ( ! empty( $cc ) ) {
            foreach ( (array) $cc as $key => $recipient ) {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( count( $matches ) == 3 ) {
                        $cc[ $key ] = trim( $matches[2] );
                    }
                }
                $mail_cc = new SendGrid\Email(null, $cc[ $key ]);
                $personalization->addCc($mail_cc);
            }
        }

        if ( ! empty( $bcc ) ) {
            foreach ( (array) $bcc as $key => $recipient ) {
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( 3 == count( $matches ) ) {
                        $bcc[ $key ] = trim( $matches[2] );
                    }
                }
                $mail_bcc = new SendGrid\Email(null, $bcc[ $key ]);
                $personalization->addBcc($mail_bcc);
            }
        }


        if(is_array($to)){
            foreach ( (array) $to as $key => $recipient ) {
                $toname[ $key ] = " ";
                // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
                if ( preg_match(  '/(.*)<(.+)>/', $recipient, $matches ) ) {
                    if ( 3 == count( $matches ) ) {
                        $to[ $key ] = trim( $matches[2] );
                    }
                }
                $mail_to = new SendGrid\Email(null, $to[ $key ]);

                $personalization->addTo($mail_to);
            }
        }
        else{
            $mail_to = new SendGrid\Email(null, $to);
            $personalization->addTo($mail_to);
        }



        // Set the content of Email

        if ( ! isset( $content_type ) ) {
            $content_type = 'text/plain';
        }

        $content_type = apply_filters( 'wp_mail_content_type', $content_type );

        $content = new SendGrid\Content($content_type, $message);



        // set the attachments
        if(!is_array($attachments) && !empty($attachments)) {
            $attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
        }

        if(!empty($attachments))    {
            foreach($attachments as $single_attachment) {
                $attachment = $this->prepareAttachment($single_attachment);
                if($attachment)
                    $mail->addAttachment($attachment);

            }
        }


        $from = new SendGrid\Email($from_name, $from_email);


        $mail->setSubject( $subject );
        $mail->setFrom( $from );
        $mail->addContent($content);
        $mail->addPersonalization($personalization);


        if (  !empty( $replyto )) {
            $reply_to_found = preg_match( '/.*<(.*)>.*/i', $replyto, $result );
            if ( $reply_to_found ) {
                $replyto = $result[1];
            }
            $replyto = new SendGrid\ReplyTo($replyto);
            $mail->setReplyTo( $replyto );
        }


        $sendGrid = new \SendGrid($api_key);

        if ( ! $sendGrid ) {
            return false;
        }

        $log_data = array(
            'status' => 'Success',
            'message' => 'Mail sent successfully'
        );

        $response = $sendGrid->client->mail()->send()->post($mail);
        $status_code = $response->statusCode();
        if($status_code != 202) {
            $response_body = json_decode($response->body());
            if($response_body->errors)  {
                $error_count = count($response_body->errors);
                if($error_count > 0)    {
                    foreach($response_body->errors as $error_key => $error_info)    {
                        $log_data['message'] = $log_data['message'] . ' ' . $error_info->message;
                    }
                    $log_data['status'] = 'Failed';
                    $log_data['message'] = trim($log_data['message']);
                }
            }
        }

        if($log_data['status'] == 'Success'){
            return true;
        }
        else{
            error_log(print_r($response_body->errors, 1));
            return false;
        }
    }

    /**
     * Prepare attachment object
     * @param $attachment_path
     * @return SendGrid\Attachment
     */
    public function prepareAttachment($attachment_path)
    {
        $file_info = pathinfo($attachment_path);
        $attachment = new SendGrid\Attachment();
        $attachment->setContent(base64_encode(file_get_contents($attachment_path, FILE_USE_INCLUDE_PATH)));
        $attachment->setFilename($file_info['basename']);
        $attachment->setType(mime_content_type($attachment_path));
        $attachment->setDisposition('attachment');
        return $attachment;
    }



}

$options = get_option('pl8app_settings');
$enabled_email_service = isset($options['smtp_config'])?$options['smtp_config']: array();

// Replacing wp_mail function if enabled email service is other than default and smtp
if(!function_exists('wp_mail') && isset($enabled_email_service['enable_disable']) && $enabled_email_service['enable_disable'] == '1')
{

    function wp_mail($to, $subject, $message, $headers = '', $attachments = array())
    {
        $emailService = new PL8_sendgrid_SMTP();

        return $emailService->send_mail($to, $subject, $message, $headers, $attachments);
    }
}