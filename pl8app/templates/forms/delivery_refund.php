<?php
/**
 * Render the DELIVERY, RETURNS AND REFUNDS contents !
 */

$options = get_option('pl8app_settings');
$delivery = !empty($options['delivery_refund']['delivery'])? $options['delivery_refund']['delivery'] : '';
$refund = !empty($options['delivery_refund']['refund']) ? $options['delivery_refund']['refund'] : '';
ob_start();
?>

<style>
    div.delivery-refund-content{
        padding: 20px;
    }

</style>

<div class="pl8app-wrap container">
    <div class="delivery-refund-content">
        <div class="row">
            <h3><?php echo __('Delivery', 'pl8app'); ?></h3>
            <div>
                <?php echo $delivery; ?>
            </div>
        </div>
        <div class="row">
            <h3><?php echo __('Returns and Refunds', 'pl8app'); ?></h3>
            <div>
                <?php echo $refund; ?>
            </div>
        </div>
    </div>
</div>
