<?php
/**
 * This template is used to display the printer receipt
 */

defined( 'ABSPATH' ) || exit;

?>
<div style='font-family: {pl8p_choosen_font}; width: {pl8p_paper_size}; max-width: 80mm; margin: 2mm'>
  <div>
      <div style="text-align: center;">
        {pl8p_store_logo}
      </div>
      <div>
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;"><?php echo __('Order', 'pl8app-printer'); ?>: #<b>{pl8p_order_id}</b></p>
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;">{pl8p_customer_name} {pl8p_customer_phone}</p>
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;">{pl8p_customer_email}</p>
      </div>
      <div style="width:100%; padding:0; margin-bottom: 10px;">
          <table style="border-spacing: 0; border: 1px solid #000000; margin-top: 10px; width: 100%; padding: 10px;">
              <thead>
                  <tr>
                  <tr>
                      <td style="text-align: center; padding-bottom: 8px;"><strong style="text-transform: uppercase; font-size: 15pt;">{pl8p_order_type}</strong></td>
                  </tr>
                  <tr>
                      <td>
                          <p>{pl8p_order_time_text}&nbsp;<b>{pl8p_order_time} {pl8p_order_date}</b></p>
                      </td>
                  </tr>
                  <tr>
                      <td>
                          {pl8p_order_location}
                      </td>
                  </tr>
                  <tr>
                      <td>
                          {pl8p_order_payment_type}
                      </td>
                  </tr>
                  <tr>
                      <td>
                          {pl8p_order_note}
                      </td>
                  </tr>
              </thead>
          </table>
      </div>
      <div>{pl8p_order_items}</div>
  </div>
  <div style="padding: 0 10px;">
      {footer_note}
  </div>
  <hr style="width: 92%">
  <div style="padding: 0 10px;">
      {footer_complementary}
  </div>
</div>