<?php
/**
 * This template is used to display the printer receipt
 */

defined( 'ABSPATH' ) || exit;

?>

<style type="text/css">
table.page_header { border: none; font-size: 14pt; }
table.page_body { border-top: solid 0.2mm #000000; padding: 8px; margin-top: 10px; margin-left: 1mm; font-size: 14pt; }
table.page_items { border-top: solid 0.2mm #000000; border-bottom: solid 0.2mm #000000; padding: 8px; margin-top: 10px; margin-left: 1mm; border-spacing: 0; font-size: 14pt; }
table.page_totals { margin-top: 10px; padding: 3px; font-size: 14pt; }
table.page_footer { border: none; font-size: 14pt; }
</style>


<!--  width: 76mm; backtop="2mm" backbottom="3mm" backleft="2mm" backright="2mm" -->

<page style="{pl8p_choosen_font}" backleft="-2mm">
  <table class="page_header">
    <tr>
      <td width="256" style="text-align: center;">{pl8p_store_logo}</td>
    </tr>
    <tr>
      <td width="256">
        <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;"><?php echo __('Order', 'pl8app-printer'); ?>: <?php echo __('pl8app', 'pl8app-printer'); ?>#<b>{pl8p_order_id}</b></p>
        <p style="margin-top: 5px; margin-bottom: 0; text-align: center; font-size: 12pt;">{pl8p_customer_name} {pl8p_customer_phone}</p>
        <p style="margin-top: 5px; margin-bottom: 0; text-align: center; font-size: 12pt;">{pl8p_customer_email}</p>
      </td>
    </tr>
  </table>
  <table class="page_body">
    <tr>
      <td style="text-align: center;"><strong style="text-transform: uppercase; font-size: 14pt;">{pl8p_order_type}</strong></td>
    </tr>
    <tr>
      <td width="255"><p>{pl8p_order_time_text}&nbsp;<b>{pl8p_order_time} {pl8p_order_date}</b></p></td>
    </tr>
    <tr>
      <td width="255">{pl8p_order_location}</td>
    </tr>
    <tr>
      <td width="255">{pl8p_order_payment_type}</td>
    </tr>
    <tr>
      <td width="255">{pl8p_order_note}</td>
    </tr>
  </table>
  {pl8p_order_items}
  <table class="page_footer">
    <tr>
      <td width="256">{footer_note}</td>
    </tr>
    <tr>
      <td width="256">{footer_complementary}</td>
    </tr>
  </table>
</page>