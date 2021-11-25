<?php

?>
    <form class="pl8app-wrapper" id="store-contact-form" action="<?php echo admin_url( 'admin-post.php' ) ?>" method="POST">
        <p>
            <label> Your name<br>
                <span class="pl8app-wrap your-name">
            <input type="text" name="pl8app-your-name" id="pl8app-your-name" value="" size="40"
                   class="pl8app-form-control pl8app-m-text-wrapper" aria-required="true" aria-invalid="true">
            <span class="pl8app-valid hidden" aria-hidden="true">*The field is required.</span></span>
            </label>
        </p>

        <p>
            <label> Your Email<br>
                <span class="pl8app-wrap your-email">
            <input type="email" name="pl8app-your-email" id="pl8app-your-email" value="" size="40"
                   class="pl8app-form-control pl8app-m-text-wrapper" aria-required="true" aria-invalid="true">
            <span class="pl8app-valid hidden" aria-hidden="true">*The field is required.</span></span>
            </label>
        </p>

        <p>
            <label> Subject<br>
                <span class="pl8app-wrap subject">
            <input type="text" name="pl8app-subject" id="pl8app-subject" value="" size="40"
                   class="pl8app-form-control pl8app-m-text-wrapper" aria-required="true" aria-invalid="true">
            <span class="pl8app-valid hidden" aria-hidden="true">*The field is required.</span></span>
            </label>
        </p>

        <p>
            <label> Your message<br>
                <span class="pl8app-form-control-wrap your-message">
                    <textarea name="pl8app-your-message" cols="40" rows="40"
                              class="pl8app-form-control pl8app-textarea"
                              aria-invalid="false">
                    </textarea>
                    <span class="pl8app-valid hidden" aria-hidden="true">*The field is required.</span>
                </span>
            </label>
        </p>
        <?php
        if(isset($_GET['status'])) {
            switch($_GET['status']) {
                case 'success':
                    echo '<p style="background-color: #0bd000;color: white;padding: 7px;"><span>' . $_GET['message'] . '</span></p>';
                    break;
                case 'failed' :
                    echo '<p style="background-color: #d00000;color: white;padding: 7px;">' . $_GET['message'] . '</span></p>';
                    break;
            }
        }
        pl8app_form_recaptcha_render();?>
        <p>
            <?php wp_nonce_field('pl8app_contact_us_nonce', 'pl8app_contact_us_nonce_field'); ?>
            <input type="hidden" name="action" value="pl8app_contact_us_action"/>
            <input type="submit" value="Submit" id='pl8app-submit' class="pl8app-form-control pl8app-submit">
            <span class="ajax-loader"></span>
        </p>
    </form>
<?php