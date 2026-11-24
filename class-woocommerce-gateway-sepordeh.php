<?php

if (!defined('ABSPATH'))
  exit;

if (class_exists('WC_Payment_Gateway') && !class_exists('WC_SpDeh')) {

  class WC_SpDeh extends WC_Payment_Gateway
  {

    private $merchantcode;
    private $automaticGateway;
    private $success_massage;
    private $failed_massage;
    private $logo_url;
    private $author;

    public function __construct()
    {

      $this->author = 'sepordeh.com';

      $this->id = 'WC_SpDeh';
      $this->method_title = __('پرداخت آنلاین سپرده', 'woocommerce');
      $this->method_description = __('تنظیمات درگاه پرداخت سپرده برای افزونه فروشگاه ساز ووکامرس', 'woocommerce');
      $this->logo_url = plugin_dir_url(__FILE__) . '/assets/images/logo.svg';
      $this->icon = apply_filters('woo_sepordeh_logo', $this->logo_url);
      $this->has_fields = false;

      $this->init_form_fields();
      $this->init_settings();

      $this->title = $this->settings['title'];
      $this->description = $this->settings['description'];
      $this->merchantcode = $this->settings['merchantcode'];
      $this->automaticGateway = $this->settings['automaticGateway'] == "yes";
      $this->success_massage = $this->settings['success_massage'];
      $this->failed_massage = $this->settings['failed_massage'];

      if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>='))
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
      else
        add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
      add_action('woocommerce_receipt_' . $this->id . '', array($this, 'Send_to_sepordeh_Gateway'));
      add_action('woocommerce_api_' . strtolower(get_class($this)) . '', array($this, 'Return_from_sepordeh_Gateway'));
      add_action('admin_notices', array($this, 'admin_notice_add_merchantcode'));
    }


    public function admin_options()
    {
      parent::admin_options();
    }

    public function init_form_fields()
    {
      $this->form_fields = apply_filters(
        'WC_SpDeh_Config',
        array(
          'gateway_logo' => array(
            'title' => '',
            'type'  => 'title',
            'description' => sprintf(
              '<div style="text-align:center; margin:20px 0;"><img src="%s" alt="لوگوی سپرده" style="max-width:180px; height:auto;" /></div>',
              apply_filters('woo_sepordeh_logo', $this->logo_url)
            ),
          ),

          'base_confing' => array(
            'title' => __('تنظیمات پایه ای', 'woocommerce'),
            'type' => 'title',
            'description' => '',
          ),
          'enabled' => array(
            'title' => __('فعالسازی/غیرفعالسازی', 'woocommerce'),
            'type' => 'checkbox',
            'label' => __('فعالسازی درگاه سپرده', 'woocommerce'),
            'description' => __('برای فعالسازی درگاه پرداخت سپرده باید چک باکس را تیک بزنید', 'woocommerce'),
            'default' => 'yes',
            'desc_tip' => true,
          ),
          'title' => array(
            'title' => __('عنوان درگاه', 'woocommerce'),
            'type' => 'text',
            'description' => __('عنوان درگاه که در طی خرید به مشتری نمایش داده میشود', 'woocommerce'),
            'default' => __('پرداخت آنلاین سپرده ', 'woocommerce'),
            'desc_tip' => true,
          ),
          'description' => array(
            'title' => __('توضیحات درگاه', 'woocommerce'),
            'type' => 'text',
            'desc_tip' => true,
            'description' => __('توضیحاتی که در طی عملیات پرداخت برای درگاه نمایش داده خواهد شد', 'woocommerce'),
            'default' => __('پرداخت امن به وسیله کلیه کارت های عضو شتاب از طریق درگاه سپرده', 'woocommerce')
          ),
          'account_confing' => array(
            'title' => __('تنظیمات حساب سپرده', 'woocommerce'),
            'type' => 'title',
            'description' => '',
          ),
          'merchantcode' => array(
            'title' => __('مرچنت کد', 'woocommerce'),
            'type' => 'text',
            'description' => __('مرچنت کد درگاه سپرده', 'woocommerce'),
            'default' => '',
            'desc_tip' => true
          ),
          'automaticGateway' => array(
            'title' => __('فعالسازی درگاه مستقیم', 'woocommerce'),
            'type' => 'checkbox',
            'label' => __('در این حالت مستقیما به درگاه پرداخت منتقل خواهید شد', 'woocommerce'),
            'description' => __('درگاه مستقیم سپرده ', 'woocommerce'),
            'default' => 'yes',
            'desc_tip' => true,
          ),
          'payment_confing' => array(
            'title' => __('تنظیمات عملیات پرداخت', 'woocommerce'),
            'type' => 'title',
            'description' => '',
          ),
          'success_massage' => array(
            'title' => __('پیام پرداخت موفق', 'woocommerce'),
            'type' => 'textarea',
            'description' => __('متن پیامی که میخواهید بعد از پرداخت موفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {transaction_id} برای نمایش کد رهگیری (کد تراکنش سپرده) استفاده نمایید .', 'woocommerce'),
            'default' => __('با تشکر از شما . سفارش شما با موفقیت پرداخت شد .', 'woocommerce'),
          ),
          'failed_massage' => array(
            'title' => __('پیام پرداخت ناموفق', 'woocommerce'),
            'type' => 'textarea',
            'description' => __('متن پیامی که میخواهید بعد از پرداخت ناموفق به کاربر نمایش دهید را وارد نمایید . همچنین می توانید از شورت کد {fault} برای نمایش دلیل خطای رخ داده استفاده نمایید . این دلیل خطا از سایت سپرده ارسال میگردد .', 'woocommerce'),
            'default' => __('پرداخت شما ناموفق بوده است . لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید .', 'woocommerce'),
          )
        )
      );
    }

    public function process_payment($order_id)
    {
      $order = new WC_Order($order_id);
      return array(
        'result' => 'success',
        'redirect' => $order->get_checkout_payment_url(true)
      );
    }

    public function Send_to_sepordeh_Gateway($order_id)
    {
      global $woocommerce;
      $woocommerce->session->order_id_sepordeh = $order_id;
      $order = new WC_Order($order_id);
      $currency = $order->get_currency();
      $currency = apply_filters('WC_SpDeh_Currency', $currency, $order_id);

      $form = '<form action="" method="POST" class="sepordeh-checkout-form" id="sepordeh-checkout-form">
						<input type="submit" name="sepordeh_submit" class="button alt" id="sepordeh-payment-button" value="' . __('پرداخت', 'woocommerce') . '"/>
						<a class="button cancel" href="' . wc_get_checkout_url() . '">' . __('بازگشت', 'woocommerce') . '</a>
					 </form><br/>';
      $form = apply_filters('WC_SpDeh_Form', $form, $order_id, $woocommerce);

      do_action('WC_SpDeh_Gateway_Before_Form', $order_id, $woocommerce);
      if ( did_action('WC_SpDeh_Form_Printed') ) return;
      echo $form;
      do_action('WC_SpDeh_Form_Printed');
      do_action('WC_SpDeh_Gateway_After_Form', $order_id, $woocommerce);

      if (!extension_loaded('curl')) {
        $order->add_order_note(__('تابع cURL روی هاست شما فعال نیست .', 'woocommerce'));
        wc_add_notice(__('تابع cURL روی هاست فروشنده فعال نیست .', 'woocommerce'), 'error');
        return false;
      }
      $Amount = intval($order->order_total);
      $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $Amount, $currency);
      if (strtolower($currency) == strtolower('IRT') || strtolower($currency) == strtolower('TOMAN') || strtolower($currency) == strtolower('Iran TOMAN') || strtolower($currency) == strtolower('Iranian TOMAN') || strtolower($currency) == strtolower('Iran-TOMAN') || strtolower($currency) == strtolower('Iranian-TOMAN') || strtolower($currency) == strtolower('Iran_TOMAN') || strtolower($currency) == strtolower('Iranian_TOMAN') || strtolower($currency) == strtolower('تومان') || strtolower($currency) == strtolower('تومان ایران'))
        $Amount = $Amount;
      else if (strtolower($currency) == strtolower('IRHT'))
        $Amount = $Amount * 1000;
      else if (strtolower($currency) == strtolower('IRHR'))
        $Amount = $Amount * 100;
      else if (strtolower($currency) == strtolower('IRR'))
        $Amount = $Amount / 10;
      $Amount = (int) round($Amount);

      $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_after_check_currency', $Amount, $currency);
      $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_irt', $Amount, $currency);
      $Amount = apply_filters('woocommerce_order_amount_total_Sepordeh_gateway', $Amount, $currency);
      $products = array();
      $order_items = $order->get_items();
      foreach ($order_items as $product) {
        $products[] = $product['name'] . ' (' . $product['qty'] . ') ';
      }
      $products = implode(' - ', $products);
      $Description = 'خریدار : ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . ' | محصولات : ' . $products;
      $Tell = intval($order->get_billing_phone());
      $Email = $order->get_billing_email();


      $Description = apply_filters('WC_SpDeh_Description', $Description, $order_id);
      do_action('WC_SpDeh_Gateway_Payment', $order_id, $Description);

      $CallbackURL = add_query_arg('wc_order', $order_id, WC()->api_request_url(strtolower(get_class($this))));

      $url = 'https://api.sepordeh.com/merchant/invoices/add';

      $data = array(
        'merchant'          => $this->merchantcode,
        'amount'       => $Amount,
        'callback'     => $CallbackURL,
        'orderId' => $order_id,
        'phone' => $Tell,
        'email' => $Email,
        'description'  => $Description,
      );


      $args = array(
        'timeout' => 20,
        'body' => $data,
        'httpversion' => '1.1',
        'user-agent' => 'Official Sepordeh Woocommerce Plugin'
      );

      $number_of_connection_tries = 4;
      while ($number_of_connection_tries) {
        $response = wp_safe_remote_post($url, $args);
        if (is_wp_error($response)) {
          $number_of_connection_tries--;
          continue;
        } else {
          break;
        }
      }

      $result = json_decode($response["body"]);
      if ($result->status === 200) {
        if ($this->automaticGateway)
          wp_redirect("https://api.sepordeh.com/merchant/invoices/pay/automatic:true/id:" . $result->information->invoice_id);
        else
          wp_redirect("https://api.sepordeh.com/merchant/invoices/pay/id:" . $result->information->invoice_id);
        exit;
      } else {
        $Message = 'خطا : <br />' . $result->message;
        $Fault = '';
      }

      if (!empty($Message) && $Message) {

        $Note = sprintf(__('خطا در هنگام ارسال به بانک : %s', 'woocommerce'), $Message);
        $Note = apply_filters('WC_SpDeh_Send_to_Gateway_Failed_Note', $Note, $order_id, $Fault);
        $order->add_order_note($Note);


        $Notice = sprintf(__('در هنگام اتصال به بانک خطای زیر رخ داده است : <br/>%s', 'woocommerce'), $Message);
        $Notice = apply_filters('WC_SpDeh_Send_to_Gateway_Failed_Notice', $Notice, $order_id, $Fault);
        if ($Notice)
          wc_add_notice($Notice, 'error');

        do_action('WC_SpDeh_Send_to_Gateway_Failed', $order_id, $Fault);
      }
    }

    public function Return_from_sepordeh_Gateway()
    {


      $Authority = sanitize_text_field($_GET['authority']);

      global $woocommerce;


      if (isset($_GET['wc_order']))
        $order_id = sanitize_text_field($_GET['wc_order']);
      else
        $order_id = $woocommerce->session->order_id_sepordeh;
      unset($woocommerce->session->order_id_sepordeh);

      if ($order_id) {

        $order = new WC_Order($order_id);
        $currency = $order->get_currency();
        $currency = apply_filters('WC_SpDeh_Currency', $currency, $order_id);


        $Amount = intval($order->order_total);
        $Amount = apply_filters('woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $Amount, $currency);
        if (strtolower($currency) == strtolower('IRT') || strtolower($currency) == strtolower('TOMAN') || strtolower($currency) == strtolower('Iran TOMAN') || strtolower($currency) == strtolower('Iranian TOMAN') || strtolower($currency) == strtolower('Iran-TOMAN') || strtolower($currency) == strtolower('Iranian-TOMAN') || strtolower($currency) == strtolower('Iran_TOMAN') || strtolower($currency) == strtolower('Iranian_TOMAN') || strtolower($currency) == strtolower('تومان') || strtolower($currency) == strtolower('تومان ایران'))
          $Amount = $Amount;
        else if (strtolower($currency) == strtolower('IRHT'))
          $Amount = $Amount * 1000;
        else if (strtolower($currency) == strtolower('IRHR'))
          $Amount = $Amount * 100;
        else if (strtolower($currency) == strtolower('IRR'))
          $Amount = $Amount / 10;
        $Amount = (int) round($Amount);

        if ($Authority) {
          if ($order->status != 'completed') {

            $url = 'https://api.sepordeh.com/merchant/invoices/verify';
            $data = [
              'merchant'   => $this->merchantcode,
              'authority' => $Authority,
            ];
            $args = array(
              'timeout' => 20,
              'body' => $data,
              'httpversion' => '1.1',
              'user-agent' => 'Official Sepordeh Woocommerce Plugin'
            );

            $number_of_connection_tries = 4;
            while ($number_of_connection_tries) {
              $response = wp_safe_remote_post($url, $args);
              if (is_wp_error($response)) {
                $number_of_connection_tries--;
                continue;
              } else {
                break;
              }
            }
            $result = json_decode($response["body"]);

            if ($result->status === 200) {
              $Status = 'completed';
              $Transaction_ID = $result->information->invoice_id;
              $verify_cardnum = $result->information->card;
			  $verify_tracking	=	$Authority;
              $Fault = '';
              $Message = '';
            } elseif ($result->status === 101) {
              $Message = 'این تراکنش قبلا تایید شده است';
              $Notice = wpautop(wptexturize($Message));
              wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
              exit;
            } else {
              $Status = 'failed';
              $Fault = $result->status;
              $Message = $result->message;
            }
          } else {
            $Status = 'failed';
            $Fault = '';
            $Message = 'تراکنش انجام نشد .';
          }
          if ($Status == 'completed' && isset($Transaction_ID) && $Transaction_ID != 0) {
            update_post_meta($order_id, '_transaction_id', $Transaction_ID);
            update_post_meta($order_id, '_card_number', $verify_cardnum);
            update_post_meta($order_id, '_tracking_number', $verify_tracking);

            $order->payment_complete($Transaction_ID);
            $woocommerce->cart->empty_cart();

            $Note = sprintf(__('پرداخت موفقیت آمیز بود .<br/> کد رهگیری : %s', 'woocommerce'), $Transaction_ID);
            $Note .= sprintf(__('<br/> شماره کارت پرداخت کننده : %s', 'woocommerce'), $verify_cardnum);
            $Note .= sprintf(__('<br/> شماره تراکنش : %s', 'woocommerce'), $verify_tracking);
            $Note = apply_filters('WC_SpDeh_Return_from_Gateway_Success_Note', $Note, $order_id, $Transaction_ID, $verify_cardnum, $verify_tracking);
            if ($Note)
              $order->add_order_note($Note, 1);


            $Notice = wpautop(wptexturize($this->success_massage));

            $Notice = str_replace("{transaction_id}", $Transaction_ID, $Notice);

            $Notice = apply_filters('WC_SpDeh_Return_from_Gateway_Success_Notice', $Notice, $order_id, $Transaction_ID);
            if ($Notice)
              wc_add_notice($Notice, 'success');

            do_action('WC_SpDeh_Return_from_Gateway_Success', $order_id, $Transaction_ID);

            wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
            exit;
          } else {
            $tr_id = ($Transaction_ID && $Transaction_ID != 0) ? ('<br/>کد تراکنش : ' . $Transaction_ID) : '';

            $Note = sprintf(__('خطا در هنگام بازگشت از بانک : %s %s', 'woocommerce'), $Message, $tr_id);

            $Note = apply_filters('WC_SpDeh_Return_from_Gateway_Failed_Note', $Note, $order_id, $Transaction_ID, $Fault);
            if ($Note)
              $order->add_order_note($Note, 1);

            $Notice = wpautop(wptexturize($this->failed_massage));

            $Notice = str_replace("{transaction_id}", $Transaction_ID, $Notice);

            $Notice = str_replace("{fault}", $Message, $Notice);
            $Notice = apply_filters('WC_SpDeh_Return_from_Gateway_Failed_Notice', $Notice, $order_id, $Transaction_ID, $Fault);
            if ($Notice)
              wc_add_notice($Notice, 'error');

            do_action('WC_SpDeh_Return_from_Gateway_Failed', $order_id, $Transaction_ID, $Fault);

            wp_redirect(wc_get_checkout_url());
            exit;
          }
        } else {
          $Transaction_ID = get_post_meta($order_id, '_transaction_id', true);
          $Notice = apply_filters('WC_SpDeh_Return_from_Gateway_ReSuccess_Notice', str_replace("{transaction_id}", $Transaction_ID, wpautop(wptexturize($this->success_massage))), $order_id, $Transaction_ID);
          if ($Notice)
            wc_add_notice($Notice, 'success');

          do_action('WC_SpDeh_Return_from_Gateway_ReSuccess', $order_id, $Transaction_ID);
          wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
          exit;
        }
      } else {

        $Fault = __('شماره سفارش وجود ندارد .', 'woocommerce');
        $Notice = wpautop(wptexturize($this->failed_massage));
        $Notice = str_replace("{fault}", $Fault, $Notice);
        $Notice = apply_filters('WC_SpDeh_Return_from_Gateway_No_Order_ID_Notice', $Notice, $order_id, $Fault);
        if ($Notice)
          wc_add_notice($Notice, 'error');

        do_action('WC_SpDeh_Return_from_Gateway_No_Order_ID', $order_id, $Transaction_ID, $Fault);

        wp_redirect(wc_get_checkout_url());
        exit;
      }
    }

    public function admin_notice_add_merchantcode()
    {
      $merchantcode = $this->settings['merchantcode'];

      if (empty($merchantcode) && 'yes' === $this->settings['enabled']) {


        $message = sprintf(
          __('مرچنت کد درگاه سپرده خالی است. برای تکمیل مورد مربوطه به تنظیمات درگاه <a href="%s">اینجا</a> مراجعه کنید.'),
          admin_url('admin.php?page=wc-settings&tab=checkout&section=WC_SpDeh')
        );

        echo '<div class="notice notice-warning is-dismissible" style="display:flex; align-items:center;padding:10px">';
        echo '<img src="' . esc_url($this->logo_url) . '" style="max-width:120px; margin-left:10px;" alt="لوگوی سپرده" />';
        echo '<p style="margin:0;">' . $message . '</p>';
        echo '</div>';
      }
    }
  }
}
