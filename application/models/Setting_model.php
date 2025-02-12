<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setting_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper', 'timezone_helper']);
    }

    public function update_system_setting($post)
    {
        $post = escape_array($post);

        $system_data = [
            'system_configurations' => $post['system_configurations'],
            'system_timezone_gmt' => $post['system_timezone_gmt'],
            'system_configurations_id' => $post['system_configurations_id'],
            'app_name' => $post['app_name'],
            'support_number' => $post['support_number'],
            'support_email' => $post['support_email'],
            'current_version' => $post['current_version'],
            'current_version_ios' => $post['current_version_ios'],
            'is_version_system_on' => (isset($post['is_version_system_on'])) ? '1' : '0',
            'currency' => $post['currency'],
            'system_timezone' => $post['system_timezone'],
            'is_refer_earn_on' => (isset($post['is_refer_earn_on'])) ? '1' : '0',
            'tax' => (isset($post['tax'])) ? $post['tax'] : '0',
            'is_email_setting_on' => (isset($post['is_email_setting_on'])) ? '1' : '0',
            'google_map_api_key' => $post['google_map_api_key'],
            'google_map_javascript_api_key' => $post['google_map_javascript_api_key'],
            'customer_app_android_link' => $post['customer_app_android_link'],
            'partner_app_android_link' => $post['partner_app_android_link'],
            'rider_app_android_link' => $post['rider_app_android_link'],
            'customer_app_ios_link' => $post['customer_app_ios_link'],
            'partner_app_ios_link' => $post['partner_app_ios_link'],
            'rider_app_ios_link' => $post['rider_app_ios_link'],
            'min_refer_earn_order_amount' => $post['min_refer_earn_order_amount'],
            'refer_earn_bonus' => $post['refer_earn_bonus'],
            'refer_earn_method' => $post['refer_earn_method'],
            'max_refer_earn_amount' => $post['max_refer_earn_amount'],
            'refer_earn_bonus_times' => $post['refer_earn_bonus_times'],
            'minimum_cart_amt' => $post['minimum_cart_amt'],
            'low_stock_limit' => (isset($post['low_stock_limit'])) ? $post['low_stock_limit'] : '5',
            'max_items_cart' => $post['max_items_cart'],
            'distance_matrix_api' => (isset($post['distance_matrix_api'])) ? '1' : '0',
            'free_delivery_on_first_order' => (isset($post['free_delivery_on_first_order'])) ? '1' : '0',
            'is_rider_otp_setting_on' => (isset($post['is_rider_otp_setting_on'])) ? '1' : '0',
            'is_app_maintenance_mode_on' => (isset($post['is_app_maintenance_mode_on'])) ? '1' : '0',
            'is_rider_app_maintenance_mode_on' => (isset($post['is_rider_app_maintenance_mode_on'])) ? '1' : '0',
            'is_partner_app_maintenance_mode_on' => (isset($post['is_partner_app_maintenance_mode_on'])) ? '1' : '0',
            'is_web_maintenance_mode_on' => (isset($post['is_web_maintenance_mode_on'])) ? '1' : '0',
            'supported_locals' => (isset($post['supported_locals'])) ?  $post['supported_locals'] : '',
        ];

        $main_image_name = $post['logo'];
        $favicon_image_name = $post['favicon'];

        $system_data = json_encode($system_data);
        $query = $this->db->get_where('settings', array(
            'variable' => 'system_settings'
        ));
        $count = $query->num_rows();
        if ($main_image_name != NULL && !empty($main_image_name)) {
            $logo_res = $this->db->get_where('settings', array(
                'variable' => 'logo'
            ));
            $logo_count = $logo_res->num_rows();
            if ($logo_count == 0) {
                $this->db->insert('settings', ['value' => $main_image_name, 'variable' => 'logo']);
            } else {
                $this->db->set('value', $main_image_name)->where('variable', 'logo')->update('settings');
            }
        }
       
        if ($favicon_image_name != NULL && !empty($favicon_image_name)) {
            $favicon_res = $this->db->get_where('settings', array(
                'variable' => 'favicon'
            ));
            $favicon_count = $favicon_res->num_rows();
            if ($favicon_count == 0) {
                $this->db->insert('settings', ['value' => $favicon_image_name, 'variable' => 'favicon']);
            } else {
                $this->db->set('value', $favicon_image_name)->where('variable', 'favicon')->update('settings');
            }
        }
        if ($count === 0) {
            $data = array(
                'variable' => 'system_settings',
                'value' => $system_data
            );
            $this->db->insert('settings', $data);
            $this->db->insert('settings', ['value' => $post['currency']]);
        } else {
            $this->db->set('value', $system_data)->where('variable', 'system_settings')->update('settings');
            $this->db->set('value', $post['currency'])->where('variable', 'currency')->update('settings');
        }
    }

        public function update_smsgateway($post)
    {
        $post = escape_array($post);
        $smsgateway_data = array();

        $smsgateway_data['base_url'] = isset($post['base_url']) ? $post['base_url'] : '';
        $smsgateway_data['sms_gateway_method'] = isset($post['sms_gateway_method']) ? $post['sms_gateway_method'] : 'POST';
        $smsgateway_data['country_code_include'] = isset($post['country_code_include']) ? $post['country_code_include'] : '0';
        $smsgateway_data['header_key'] = isset($post['header_key']) && !empty($post['header_key']) ? $post['header_key'] : '';
        $smsgateway_data['header_value'] = isset($post['header_value']) && !empty($post['header_value']) ? $post['header_value'] : '';
        $smsgateway_data['text_format_data'] = isset($post['text_format_data']) && !empty($post['text_format_data']) ? $post['text_format_data'] : '';
        $smsgateway_data['params_key'] = isset($post['params_key']) && !empty($post['params_key']) ? $post['params_key'] : '';
        $smsgateway_data['params_value'] = isset($post['params_value']) && !empty($post['params_value']) ? $post['params_value'] : '';
        $smsgateway_data['body_key'] = isset($post['body_key']) && !empty($post['body_key']) ? $post['body_key'] : '';
        $smsgateway_data['body_value'] = isset($post['body_value']) && !empty($post['body_value']) ? $post['body_value'] : '';


        $smsgateway_data = json_encode($smsgateway_data);


        $query = $this->db->get_where('settings', array(
            'variable' => 'sms_gateway_settings'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'sms_gateway_settings',
                'value' => $smsgateway_data
            );
            $this->db->insert('settings', $smsgateway_data);
        } else {
            $this->db->set('value', $smsgateway_data)->where('variable', 'sms_gateway_settings')->update('settings');
        }
    }

     function update_authentication_setting($post)
    {
        $post = escape_array($post);
        $authentication_data = array();

        $authentication_data['authentication_method'] = isset($post['authentication_method']) && !empty($post['authentication_method']) ? $post['authentication_method'] : '';

        $authentication_data = json_encode($authentication_data);

        $query = $this->db->get_where('settings', array(
            'variable' => 'authentication_settings'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'authentication_settings',
                'value' => $authentication_data
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $authentication_data)->where('variable', 'authentication_settings')->update('settings');
        }
    }

    public function update_payment_method($post)
    {

        $post = escape_array($post);

        $payment_data = array();
        $payment_data['paypal_payment_method'] = isset($post['paypal_payment_method']) ? '1' : '0';
        $payment_data['paypal_mode'] = isset($post['paypal_mode']) && !empty($post['paypal_mode']) ? $post['paypal_mode'] : '';
        $payment_data['paypal_business_email'] = isset($post['paypal_business_email']) && !empty($post['paypal_business_email']) ? $post['paypal_business_email'] : '';
        $payment_data['paypal_client_id'] = isset($post['paypal_client_id']) && !empty($post['paypal_client_id']) ? $post['paypal_client_id'] : '';
        $payment_data['paypal_secret_key'] = isset($post['paypal_secret_key']) && !empty($post['paypal_secret_key']) ? $post['paypal_secret_key'] : '';
        $payment_data['currency_code'] = isset($post['currency_code']) && !empty($post['currency_code']) ? $post['currency_code'] : '';

        $payment_data['razorpay_payment_method'] = isset($post['razorpay_payment_method']) ? '1' : '0';
        $payment_data['razorpay_key_id'] = isset($post['razorpay_key_id']) && !empty($post['razorpay_key_id']) ? $post['razorpay_key_id'] : '';
        $payment_data['razorpay_secret_key'] = isset($post['razorpay_secret_key']) && !empty($post['razorpay_secret_key']) ? $post['razorpay_secret_key'] : '';
        $payment_data['refund_webhook_secret_key'] = isset($post['refund_webhook_secret_key']) && !empty($post['refund_webhook_secret_key']) ? $post['refund_webhook_secret_key'] : '';


        $payment_data['paystack_payment_method'] = isset($post['paystack_payment_method']) ? '1' : '0';
        $payment_data['paystack_key_id'] = isset($post['paystack_key_id']) && !empty($post['paystack_key_id']) ? $post['paystack_key_id'] : '';
        $payment_data['paystack_secret_key'] = isset($post['paystack_secret_key']) && !empty($post['paystack_secret_key']) ? $post['paystack_secret_key'] : '';


        $payment_data['stripe_payment_method'] = isset($post['stripe_payment_method']) ? '1' : '0';
        $payment_data['stripe_payment_mode'] = isset($post['stripe_payment_mode']) ? $post['stripe_payment_mode'] : 'test';
        $payment_data['stripe_publishable_key'] = isset($post['stripe_publishable_key']) && !empty($post['stripe_publishable_key']) ? $post['stripe_publishable_key'] : '';
        $payment_data['stripe_secret_key'] = isset($post['stripe_secret_key']) && !empty($post['stripe_secret_key']) ? $post['stripe_secret_key'] : '';
        $payment_data['stripe_webhook_secret_key'] = isset($post['stripe_webhook_secret_key']) && !empty($post['stripe_webhook_secret_key']) ? $post['stripe_webhook_secret_key'] : '';
        $payment_data['stripe_currency_code'] = isset($post['stripe_currency_code']) && !empty($post['stripe_currency_code']) ? $post['stripe_currency_code'] : '';

        $payment_data['flutterwave_payment_method'] = isset($post['flutterwave_payment_method']) ? '1' : '0';
        $payment_data['flutterwave_public_key'] = isset($post['flutterwave_public_key']) && !empty($post['flutterwave_public_key']) ? $post['flutterwave_public_key'] : '';
        $payment_data['flutterwave_secret_key'] = isset($post['flutterwave_secret_key']) && !empty($post['flutterwave_secret_key']) ? $post['flutterwave_secret_key'] : '';
        $payment_data['flutterwave_encryption_key'] = isset($post['flutterwave_encryption_key']) && !empty($post['flutterwave_encryption_key']) ? $post['flutterwave_encryption_key'] : '';
        $payment_data['flutterwave_currency_code'] = isset($post['flutterwave_currency_code']) && !empty($post['flutterwave_currency_code']) ? $post['flutterwave_currency_code'] : '';

        $payment_data['paytm_payment_method'] = isset($post['paytm_payment_method']) ? '1' : '0';
        $payment_data['paytm_payment_mode'] = isset($post['paytm_payment_mode']) && !empty($post['paytm_payment_mode']) ? $post['paytm_payment_mode'] : '';
        $payment_data['paytm_merchant_key'] = isset($post['paytm_merchant_key']) && !empty($post['paytm_merchant_key']) ? $post['paytm_merchant_key'] : '';
        $payment_data['paytm_merchant_id'] = isset($post['paytm_merchant_id']) && !empty($post['paytm_merchant_id']) ? $post['paytm_merchant_id'] : '';
        $payment_data['paytm_website'] = isset($post['paytm_payment_mode']) && $post['paytm_payment_mode'] == 'production' ? $post['paytm_website'] : 'WEBSTAGING';
        $payment_data['paytm_industry_type_id'] = isset($post['paytm_payment_mode']) && $post['paytm_payment_mode'] == 'production' ? $post['paytm_industry_type_id'] : 'Retail';

        $payment_data['midtrans_payment_mode'] = isset($post['midtrans_payment_mode']) && !empty($post['midtrans_payment_mode']) ? $post['midtrans_payment_mode'] : '';
        $payment_data['midtrans_payment_method'] = isset($post['midtrans_payment_method']) ? '1' : '0';
        $payment_data['midtrans_client_key'] = isset($post['midtrans_client_key']) && !empty($post['midtrans_client_key']) ? $post['midtrans_client_key'] : '';
        $payment_data['midtrans_merchant_id'] = isset($post['midtrans_merchant_id']) && !empty($post['midtrans_merchant_id']) ? $post['midtrans_merchant_id'] : '';
        $payment_data['midtrans_server_key'] = isset($post['midtrans_server_key']) && !empty($post['midtrans_server_key']) ? $post['midtrans_server_key'] : '';

        $payment_data['phonepe_payment_mode'] = isset($post['phonepe_payment_mode']) && !empty($post['phonepe_payment_mode']) ? $post['phonepe_payment_mode'] : '';
        $payment_data['phonepe_payment_method'] = isset($post['phonepe_payment_method']) ? '1' : '0';
        $payment_data['phonepe_webhook_url'] = isset($post['phonepe_webhook_url']) && !empty($post['phonepe_webhook_url']) ? $post['phonepe_webhook_url'] : '';
        $payment_data['phonepe_appid'] = isset($post['phonepe_appid']) && !empty($post['phonepe_appid']) ? $post['phonepe_appid'] : '';
        $payment_data['phonepe_marchant_id'] = isset($post['phonepe_marchant_id']) && !empty($post['phonepe_marchant_id']) ? $post['phonepe_marchant_id'] : '';
        $payment_data['phonepe_salt_index'] = isset($post['phonepe_salt_index']) && !empty($post['phonepe_salt_index']) ? $post['phonepe_salt_index'] : '';
        $payment_data['phonepe_salt_key'] = isset($post['phonepe_salt_key']) && !empty($post['phonepe_salt_key']) ? $post['phonepe_salt_key'] : '';
        
        $payment_data['cod_method'] = isset($post['cod_method']) ? '1' : '0';

        $payment_data = json_encode($payment_data);

        $query = $this->db->get_where('settings', array(
            'variable' => 'payment_method'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'payment_method',
                'value' => $payment_data
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $payment_data)->where('variable', 'payment_method')->update('settings');
        }
    }

    public function update_fcm_details($post)
    {
        $post = escape_array($post);

        $query = $this->db->get_where('settings', array(
            'variable' => 'fcm_server_key'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'fcm_server_key',
                'value' => $post['fcm_server_key']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $post['fcm_server_key'])->where('variable', 'fcm_server_key')->update('settings');
        }
    }

    public function custome_notifications($post){

        
        $post = escape_array($post);

        $notification_data = array();
        $notification_data['order_place_mode'] = isset($post['order_place_mode']) ? '1' : '0';
        $notification_data['order_place_title'] = isset($post['order_place_title']) && !empty($post['order_place_title']) ? $post['order_place_title'] : '';
        $notification_data['order_place_message'] = isset($post['order_place_message']) && !empty($post['order_place_message']) ? $post['order_place_message'] : '';

        $notification_data['oredr_pending_mode'] = isset($post['oredr_pending_mode']) ? '1' : '0';
        $notification_data['oredr_pending_title'] = isset($post['oredr_pending_title']) && !empty($post['oredr_pending_title']) ? $post['oredr_pending_title'] : '';
        $notification_data['oredr_pending_message'] = isset($post['oredr_pending_message']) && !empty($post['oredr_pending_message']) ? $post['oredr_pending_message'] : '';

        $notification_data['order_confirm_mode'] = isset($post['order_confirm_mode']) ? '1' : '0';
        $notification_data['oredr_confirm_title'] = isset($post['oredr_confirm_title']) && !empty($post['oredr_confirm_title']) ? $post['oredr_confirm_title'] : '';
        $notification_data['oredr_confirm_message'] = isset($post['oredr_confirm_message']) && !empty($post['oredr_confirm_message']) ? $post['oredr_confirm_message'] : '';

        $notification_data['order_preparing_mode'] = isset($post['order_preparing_mode']) ? '1' : '0';
        $notification_data['oredr_preparing_title'] = isset($post['oredr_preparing_title']) && !empty($post['oredr_preparing_title']) ? $post['oredr_preparing_title'] : '';
        $notification_data['oredr_preparing_message'] = isset($post['oredr_preparing_message']) && !empty($post['oredr_preparing_message']) ? $post['oredr_preparing_message'] : '';

        $notification_data['order_out_for_delivery_mode'] = isset($post['order_out_for_delivery_mode']) ? '1' : '0';
        $notification_data['order_out_for_delivery_title'] = isset($post['order_out_for_delivery_title']) && !empty($post['order_out_for_delivery_title']) ? $post['order_out_for_delivery_title'] : '';
        $notification_data['order_out_for_delivery_message'] = isset($post['order_out_for_delivery_message']) && !empty($post['order_out_for_delivery_message']) ? $post['order_out_for_delivery_message'] : '';

        $notification_data['oredr_cancel_mode'] = isset($post['oredr_cancel_mode']) ? '1' : '0';
        $notification_data['oredr_cancel_title'] = isset($post['oredr_cancel_title']) && !empty($post['oredr_cancel_title']) ? $post['oredr_cancel_title'] : '';
        $notification_data['oredr_cancel_message'] = isset($post['oredr_cancel_message']) && !empty($post['oredr_cancel_message']) ? $post['oredr_cancel_message'] : '';

        $notification_data['oredr_deliverd_mode'] = isset($post['oredr_deliverd_mode']) ? '1' : '0';
        $notification_data['oredr_deliverd_title'] = isset($post['oredr_deliverd_title']) && !empty($post['oredr_deliverd_title']) ? $post['oredr_deliverd_title'] : '';
        $notification_data['oredr_deliverd_message'] = isset($post['oredr_deliverd_message']) && !empty($post['oredr_deliverd_message']) ? $post['oredr_deliverd_message'] : '';

        $notification_data['rider_assign_order_mode'] = isset($post['rider_assign_order_mode']) ? '1' : '0';
        $notification_data['rider_assign_order_title'] = isset($post['rider_assign_order_title']) && !empty($post['rider_assign_order_title']) ? $post['rider_assign_order_title'] : '';
        $notification_data['rider_assign_order_message'] = isset($post['rider_assign_order_message']) && !empty($post['rider_assign_order_message']) ? $post['rider_assign_order_message'] : '';

        $notification_data['rider_pening_order_mode'] = isset($post['rider_pening_order_mode']) ? '1' : '0';
        $notification_data['rider_pening_order_title'] = isset($post['rider_pening_order_title']) && !empty($post['rider_pening_order_title']) ? $post['rider_pening_order_title'] : '';
        $notification_data['rider_pening_order_message'] = isset($post['rider_pening_order_message']) && !empty($post['rider_pening_order_message']) ? $post['rider_pening_order_message'] : '';

        $notification_data = json_encode($notification_data);

        $query = $this->db->get_where('settings', array(
            'variable' => 'custome_notifications'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'custome_notifications',
                'value' => $notification_data
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $notification_data)->where('variable', 'custome_notifications')->update('settings');
        }
   
    }

    public function update_contact_details($post)
    {
        $post = escape_array($post);

        $query = $this->db->get_where('settings', array(
            'variable' => 'contact_us'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'contact_us',
                'value' => $post['contact_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $post['contact_input_description'])->where('variable', 'contact_us')->update('settings');
        }
    }

    public function update_privacy_policy($post)
    {
        $post = escape_array($post);

        $query = $this->db->get_where('settings', array(
            'variable' => 'privacy_policy'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'privacy_policy',
                'value' => $post['privacy_policy_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $post['privacy_policy_input_description'])->where('variable', 'privacy_policy')->update('settings');
        }
    }

    public function update_terms_n_condtions($post)
    {
        $post = escape_array($post);

        $query = $this->db->get_where('settings', array(
            'variable' => 'terms_conditions'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'terms_conditions',
                'value' => $post['terms_n_conditions_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $post['terms_n_conditions_input_description'])->where('variable', 'terms_conditions')->update('settings');
        }
    }

    public function update_about_us($post)
    {
        $query = $this->db->get_where('settings', array(
            'variable' => 'about_us'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'about_us',
                'value' => $post['about_us_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $post['about_us_input_description'])->where('variable', 'about_us')->update('settings');
        }
    }

    public function update_email_settings($data)
    {
        $data = escape_array($data);
        $email_data = json_encode($data);
        $query = $this->db->get_where('settings', array(
            'variable' => 'email_settings'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'email_settings',
                'value' => $email_data
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $email_data)->where('variable', 'email_settings')->update('settings');
        }
    }



    public function update_rider_privacy_policy($data)
    {
        $data = escape_array($data);
        $query = $this->db->get_where('settings', array(
            'variable' => 'rider_privacy_policy'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'rider_privacy_policy',
                'value' => $data['privacy_policy_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $data['privacy_policy_input_description'])->where('variable', 'rider_privacy_policy')->update('settings');
        }
    }
    public function update_partner_privacy_policy($data)
    {
        $data = escape_array($data);
        $query = $this->db->get_where('settings', array(
            'variable' => 'partner_privacy_policy'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'partner_privacy_policy',
                'value' => $data['privacy_policy_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $data['privacy_policy_input_description'])->where('variable', 'partner_privacy_policy')->update('settings');
        }
    }

    public function update_rider_terms_n_condtions($data)
    {
        $data = escape_array($data);
        $query = $this->db->get_where('settings', array(
            'variable' => 'rider_terms_conditions'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'rider_terms_conditions',
                'value' => $data['terms_n_conditions_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $data['terms_n_conditions_input_description'])->where('variable', 'rider_terms_conditions')->update('settings');
        }
    }
    public function update_partner_terms_n_condtions($data)
    {
        $data = escape_array($data);
        $query = $this->db->get_where('settings', array(
            'variable' => 'partner_terms_conditions'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'partner_terms_conditions',
                'value' => $data['terms_n_conditions_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $data['terms_n_conditions_input_description'])->where('variable', 'partner_terms_conditions')->update('settings');
        }
    }
    public function update_admin_privacy_policy($data)
    {
        $data = escape_array($data);
        $query = $this->db->get_where('settings', array(
            'variable' => 'admin_privacy_policy'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'admin_privacy_policy',
                'value' => $data['privacy_policy_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $data['privacy_policy_input_description'])->where('variable', 'admin_privacy_policy')->update('settings');
        }
    }

    public function update_admin_terms_n_condtions($data)
    {
        $data = escape_array($data);
        $query = $this->db->get_where('settings', array(
            'variable' => 'admin_terms_conditions'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'admin_terms_conditions',
                'value' => $data['terms_n_conditions_input_description']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $data['terms_n_conditions_input_description'])->where('variable', 'admin_terms_conditions')->update('settings');
        }
    }

    public function firebase_setting($post)
    {
        $post = escape_array($post);

        $system_data = json_encode($post);
        $query = $this->db->get_where('settings', array(
            'variable' => 'firebase_settings'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'firebase_settings',
                'value' => $system_data
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $system_data)->where('variable', 'firebase_settings')->update('settings');
        }
    }

    public function update_web_setting($post)
    {
        // print_R($post);
        // die;
        $post = escape_array($post);
        $post['app_download_section'] = (isset($post['app_download_section']) && !empty($post['app_download_section'])) ?: 0;
        $post['shipping_mode'] = (isset($post['shipping_mode']) && !empty($post['shipping_mode'])) ?: 0;
        $post['return_mode'] = (isset($post['return_mode']) && !empty($post['return_mode'])) ?: 0;
        $post['support_mode'] = (isset($post['support_mode']) && !empty($post['support_mode'])) ?: 0;
        $post['safety_security_mode'] = (isset($post['safety_security_mode']) && !empty($post['safety_security_mode'])) ?: 0;
        $main_image_name = (isset($post['logo']) && !empty($post['logo'])) ? $post['logo'] : "";
        $light_image_name = (isset($post['light_logo']) && !empty($post['light_logo'])) ? $post['light_logo'] : "";
        $favicon_image_name = (isset($post['favicon']) && !empty($post['favicon'])) ? $post['favicon'] : "";
        $landing_page_main_image = (isset($post['landing_page_main_image']) && !empty($post['landing_page_main_image'])) ? $post['landing_page_main_image'] : "";
        
        $system_data = json_encode($post);
        $query = $this->db->get_where('settings', array(
            'variable' => 'web_settings'
        ));
        $count = $query->num_rows();
        if ($main_image_name != NULL && !empty($main_image_name)) {
            $logo_res = $this->db->get_where('settings', array(
                'variable' => 'web_logo'
            ));
            $logo_count = $logo_res->num_rows();
            if ($logo_count == 0) {
                $this->db->insert('settings', ['value' => $main_image_name, 'variable' => 'web_logo']);
            } else {
                $this->db->set('value', $main_image_name)->where('variable', 'web_logo')->update('settings');
            }
        }
        if ($light_image_name != NULL && !empty($light_image_name)) {
            $logo_res = $this->db->get_where('settings', array(
                'variable' => 'light_logo'
            ));
            $logo_count = $logo_res->num_rows();
            if ($logo_count == 0) {
                $this->db->insert('settings', ['value' => $light_image_name, 'variable' => 'light_logo']);
            } else {
                $this->db->set('value', $light_image_name)->where('variable', 'light_logo')->update('settings');
            }
        }
        if ($favicon_image_name != NULL && !empty($favicon_image_name)) {
            $favicon_res = $this->db->get_where('settings', array(
                'variable' => 'web_favicon'
            ));
            $favicon_count = $favicon_res->num_rows();
            if ($favicon_count == 0) {
                $this->db->insert('settings', ['value' => $favicon_image_name, 'variable' => 'web_favicon']);
            } else {
                $this->db->set('value', $favicon_image_name)->where('variable', 'web_favicon')->update('settings');
            }
        }
        if ($landing_page_main_image != NULL && !empty($landing_page_main_image)) {
            $landing_page_main_image_res = $this->db->get_where('settings', array(
                'variable' => 'landing_page_main_image'
            ));
            $landing_page_main_image_count = $landing_page_main_image_res->num_rows();
            // print_r($landing_page_main_image_count);
            // die;
            if ($landing_page_main_image_count == 0) {
                $this->db->insert('settings', ['value' => $landing_page_main_image, 'variable' => 'landing_page_main_image']);
            } else {
                $this->db->set('value', $landing_page_main_image)->where('variable', 'landing_page_main_image')->update('settings');
            }
        }
        if ($count === 0) {
            $data = array(
                'variable' => 'web_settings',
                'value' => $system_data
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $system_data)->where('variable', 'web_settings')->update('settings');
        }
    }

    
    // Push notification changes

    public function update_firebase_project_id($post)
    {
        $post = escape_array($post);

        $query = $this->db->get_where('settings', array(
            'variable' => 'firebase_project_id'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'firebase_project_id',
                'value' => $post['firebase_project_id']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $post['firebase_project_id'])->where('variable', 'firebase_project_id')->update('settings');
        }
    }

    public function update_vap_id_key($post)
    {
        $post = escape_array($post);

        $query = $this->db->get_where('settings', array(
            'variable' => 'vap_id_key'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'vap_id_key',
                'value' => $post['vap_id_key']
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $post['vap_id_key'])->where('variable', 'vap_id_key')->update('settings');
        }
    }

    public function update_service_account_file($post)
    {
        $post = escape_array($post);

        $query = $this->db->get_where('settings', array(
            'variable' => 'service_account_file'
        ));
        $count = $query->num_rows();
        if ($count === 0) {
            $data = array(
                'variable' => 'service_account_file',
                'value' => $post
            );
            $this->db->insert('settings', $data);
        } else {
            $this->db->set('value', $post)->where('variable', 'service_account_file')->update('settings');
        }
    }
}
