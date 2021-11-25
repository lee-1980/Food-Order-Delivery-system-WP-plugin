<?php
/**
 * Render the FAQ contents !
 */
$options = get_option('pl8app_settings');
$fqs = !empty($options['faq']['question']) && is_array($options['faq']['question']) ? $options['faq']['question'] : array();
$fas = !empty($options['faq']['answer']) && is_array($options['faq']['answer']) ? $options['faq']['answer'] : array();
ob_start();
?>
<style>
    .pl8app-col-xs-6{
        float: left;
        width: 50%;
    }
    .listing > div.faq {
        margin-bottom: 30px;
    }

    .listing > div.faq:hover {
        -webkit-box-shadow: 0 0 10px #312b2e;
        box-shadow: 0 0 20px #312b2e;
    }

    @media only screen and (max-width: 768px) {
        .pl8app-col-xs-6{
            width: 100% !important;
        }
    }

    div.faq.question, div.faq.answer {
        margin: 0;
        padding: 15px 20px;
        line-height: 1;
        cursor: pointer;
        outline: none;
        font-family: Roboto;
    }
    div.question p {
        font-size: 18px;
        font-weight: bold !important;
        text-transform: uppercase;
    }

</style>
<div class="faq-header">
    <h1 style="box-sizing: inherit; color: rgb(51, 51, 51); text-align: center; white-space: normal;">Frequently Asked Questions (FAQ)</h1>
</div>
<div class="pl8app-wrap container">
    <div class="faq-grid-content">
        <div class="row listing">
            <?php foreach ($fqs as $index=> $fq){
                if(!empty($fq) && !empty($fas[$index]) ) {?>
                <div class="faq pl8app-col-xs-6">
                    <div class="faq question">
                        <p><?php echo $fq; ?></p>
                    </div>
                    <div class="faq answer">
                        <?php echo $fas[$index]; ?>
                    </div>
                </div>
            <?php }}?>
        </div>
    </div>
</div>

<?php

$faq_display = ob_get_clean();
echo $faq_display;
