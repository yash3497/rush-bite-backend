<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setting extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Setting_model');

        if (!has_permissions('read', 'settings')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        } else {
            $this->session->set_flashdata('authorize_flag', "");
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'settings';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Settings | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Settings  | ' . $settings['app_name'];
            $this->data['timezone'] = timezone_list();
            $this->data['logo'] = get_settings('logo');
            $this->data['favicon'] = get_settings('favicon');
            $this->data['settings'] = get_settings('system_settings', true);
            $this->data['currency'] = get_settings('currency');
            $this->data['taxes'] = fetch_details(null, 'taxes', '*');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function system_status()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = VIEW . 'system-status';
            $settings = get_settings('system_settings', true);

            $client_api_keys = fetch_details(null, "client_api_keys", "*");
            $firebase_project_id = get_settings('firebase_project_id');
            $service_account_file = get_settings('service_account_file');
            $email_setup = get_settings('email_settings');
            $email_setup = json_decode($email_setup, true);
            $payment_settings = get_settings('payment_method', true);
            $this->data['title'] = 'System Status | ' . $settings['app_name'];
            $this->data['meta_description'] = 'System Status  | ' . $settings['app_name'];
            $system_status['system_status'] = [
                0 =>  array((isset($settings['google_map_api_key']) && $settings['google_map_api_key'] != "" && $settings['google_map_api_key'] != "google_map_api_key") ? true : false, 'Google API Key', 'You need to create API KEY from google console. Used in selecting city deliverable area and city location.'),
                1 =>  array((function_exists('curl_init')) ? true : false, 'CURL Extension', 'Needs to enable this extension on your server(cPanel).This is used for payment methods.'),
                2 =>  array((class_exists('ZipArchive')) ? true : false, 'Zip Extension', 'Needs to enable this extension on your server(cPanel).This is used for update system using zip files.'),
                3 =>  array((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? true : false, 'Open SSL Extension', 'Needs to enable this extension on your server(cPanel).'),
                4 =>  array((isset($firebase_project_id) && !empty($firebase_project_id) && isset($service_account_file) && !empty($service_account_file)) ? true : false, 'Notification Settings', 'To enable Application Push Notifications, please complete these steps: <br> 1. Set your Firebase project ID. <br> 2. Upload the service account JSON file associated with your Firebase account. <br>
                      These actions are necessary to ensure proper configuration and functionality of push notifications within the application.'),
                5 =>  array(((isset($email_setup) && trim($email_setup['password']) != "your_mail_password") && !empty($email_setup['password']) && $email_setup['password'] != "") ? true : false, 'Email Settings <a href="https://www.gmass.co/smtp-test" target="_BLANK">Test</a>', 'You need to set SMTP Email Settings for Email Notification.For this setting you need to check your server SMTP Email settings. If that is not working then Ask your support to check your SMTP settings.'),
                6 =>  array((isset($payment_settings) && $payment_settings['paypal_payment_method'] == 1 && $payment_settings['paypal_business_email'] != "") ? true : 2, 'Paypal Payments <a href="https://www.paypal.com/in/business" target="_BLANK"><i class="fa fa-link"></i></a>', 'You need to create Paypal Payments Account on official bussiness site.'),
                7 =>  array((isset($payment_settings) && $payment_settings['razorpay_payment_method'] == 1 && $payment_settings['razorpay_key_id'] != "" && $payment_settings['razorpay_secret_key'] != "") ? true : 2, 'Razorpay Payments <a href="https://razorpay.com/" target="_BLANK"><i class="fa fa-link"></i></a>', 'You need to create Razorpay Payments Account on official bussiness site.'),
                8 =>  array((isset($payment_settings) && $payment_settings['paystack_payment_method'] == 1 && $payment_settings['paystack_key_id'] != "" && $payment_settings['paystack_secret_key'] != "") ? true : 2, 'Paystack Payments <a href="https://paystack.com/" target="_BLANK"><i class="fa fa-link"></i></a>', 'You need to create Paystack Payments Account on official bussiness site.'),
                9 =>  array((isset($payment_settings) && $payment_settings['stripe_payment_method'] == 1 && $payment_settings['stripe_publishable_key'] != "" && $payment_settings['stripe_secret_key'] != "" && $payment_settings['stripe_webhook_secret_key'] != "") ? true : 2, 'Stripe Payments <a href="https://stripe.com/" target="_BLANK"><i class="fa fa-link"></i></a>', 'You need to create Stripe Payments Account on official bussiness site.'),
                10 =>  array((isset($payment_settings) && $payment_settings['flutterwave_payment_method'] == 1 && $payment_settings['flutterwave_public_key'] != "" && $payment_settings['flutterwave_secret_key'] != "" && $payment_settings['flutterwave_encryption_key'] != "") ? true : 2, 'Flutterwave Payments <a href="https://flutterwave.com/us/" target="_BLANK"><i class="fa fa-link"></i></a>', 'You need to create Flutterwave Payments Account on official bussiness site.'),
                11 =>  array((isset($payment_settings) && $payment_settings['paytm_payment_method'] == 1 && $payment_settings['paytm_merchant_key'] != "" && $payment_settings['paytm_merchant_id'] != "") ? true : 2, 'Paytm Payments <a href="https://business.paytm.com/" target="_BLANK"><i class="fa fa-link"></i></a>', 'You need to create Paytm Payments Account on official bussiness site.'),
                12 =>  array((isset($payment_settings) &&  isset($payment_settings['midtrans_payment_method']) && $payment_settings['midtrans_payment_method'] == 1 && $payment_settings['midtrans_merchant_id'] != "" && $payment_settings['midtrans_merchant_id'] != "") ? true : 2, 'Midtrans Payments <a href="https://midtrans.com/" target="_BLANK"><i class="fa fa-link"></i></a>', 'You need to create Midtrans Payments Account on official bussiness site.'),
                13 =>  array((isset($payment_settings) &&  isset($payment_settings['phonepe_payment_method']) && $payment_settings['phonepe_payment_method'] == 1 && $payment_settings['phonepe_marchant_id'] != "" && $payment_settings['phonepe_marchant_id'] != "") ? true : 2, 'Phone pe Payments <a href="https://www.phonepe.com/" target="_BLANK"><i class="fa fa-link"></i></a>', 'You need to create Phone pe Payments Account on official bussiness site.'),
                14 =>  array((isset($payment_settings) &&  isset($payment_settings['cod_method']) && $payment_settings['cod_method'] == 1) ? true : 2, 'Cash on Delivery', 'Cash on Delivery Payment Method.'),
            ];
            $this->data['system_status'] = $system_status['system_status'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }


    public function update_system_settings()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'settings'), PERMISSION_ERROR_MSG, 'settings')) {
                return false;
            }

            $this->form_validation->set_rules('app_name', 'App Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('support_number', 'Support number', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('support_email', 'Support Email', 'trim|required|xss_clean|valid_email');
            $this->form_validation->set_rules('current_version', 'Current Version Of Android APP', 'trim|required|xss_clean');
            $this->form_validation->set_rules('current_version_ios', 'Current Version Of IOS APP', 'trim|required|xss_clean');
            $this->form_validation->set_rules('is_email_setting_on', 'Email Setting', 'trim|xss_clean');
            $this->form_validation->set_rules('distance_matrix_api', 'Distance Matrix API', 'trim|xss_clean');
            $this->form_validation->set_rules('free_delivery_on_first_order', 'Free Delivery On First Order', 'trim|xss_clean');
            $this->form_validation->set_rules('google_map_api_key', 'Google Map API Key', 'required|trim|xss_clean');
            $this->form_validation->set_rules('google_map_javascript_api_key', 'Google Map JavaScript API Key', 'required|trim|xss_clean');
            $this->form_validation->set_rules('system_timezone_gmt', 'System GMT timezone', 'trim|required|xss_clean');
            $this->form_validation->set_rules('system_timezone', 'System timezone', 'trim|required|xss_clean');
            $this->form_validation->set_rules('is_version_system_on', 'Version System', 'trim|xss_clean');

            $this->form_validation->set_rules('customer_app_android_link', 'Customer app link for android', 'trim|xss_clean');
            $this->form_validation->set_rules('partner_app_android_link', 'Partner app link for android', 'trim|xss_clean');
            $this->form_validation->set_rules('rider_app_android_link', 'Rider app link for android', 'trim|xss_clean');

            $this->form_validation->set_rules('customer_app_ios_link', 'Customer app link for ios', 'trim|xss_clean');
            $this->form_validation->set_rules('partner_app_ios_link', 'Partner app link for ios', 'trim|xss_clean');
            $this->form_validation->set_rules('rider_app_ios_link', 'Rider app link for ios', 'trim|xss_clean');

            $this->form_validation->set_rules('flat_delivery_charge', 'Distance Wise Delivery Charges', 'trim|xss_clean');
            $this->form_validation->set_rules('distance_unit', 'Distance Unit', 'trim|xss_clean');
            $this->form_validation->set_rules('distance_delivery_charge', 'Distance Delivery Charge', 'trim|xss_clean|numeric');

            $this->form_validation->set_rules('currency', 'Currency', 'trim|required|xss_clean');
            $this->form_validation->set_rules('minimum_cart_amt', 'Minimum Cart Amount', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('low_stock_limit', 'Low stock limit', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('max_items_cart', 'Max items Allowed In Cart', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('is_app_maintenance_mode_on', 'App Maintenance Mode', 'trim|xss_clean');
            $this->form_validation->set_rules('is_rider_app_maintenance_mode_on', 'Rider App Maintenance Mode', 'trim|xss_clean');
            $this->form_validation->set_rules('is_partner_app_maintenance_mode_on', 'Partner App Maintenance Mode', 'trim|xss_clean');
            $this->form_validation->set_rules('is_web_maintenance_mode_on', 'Web Maintenance Mode', 'trim|xss_clean');
            $this->form_validation->set_rules('tax', 'Tax', 'trim|xss_clean');
            $this->form_validation->set_rules('is_refer_earn_on', 'Refer and Earn system', 'trim|xss_clean');
            $this->form_validation->set_rules('logo', 'Logo', 'trim|required|xss_clean', array('required' => 'Logo is required'));
            $this->form_validation->set_rules('favicon', 'Favicon', 'trim|required|xss_clean', array('required' => 'Favicon is required'));
            $this->form_validation->set_rules('supported_locals', 'Supported Locals', 'trim|xss_clean');

            if (isset($_POST['is_refer_earn_on']) && $_POST['is_refer_earn_on']) {
                $this->form_validation->set_rules('min_refer_earn_order_amount', 'Minimum Refer & Earn Order Amount', 'trim|required|numeric|xss_clean');
                $this->form_validation->set_rules('refer_earn_bonus', 'Refer & Earn Bonus', 'trim|required|numeric|xss_clean');
                $this->form_validation->set_rules('refer_earn_method', 'Refer Earn method', 'trim|required|xss_clean');
                $this->form_validation->set_rules('max_refer_earn_amount', 'Maximum Refer & Earn Bonus', 'trim|required|xss_clean');
                $this->form_validation->set_rules('refer_earn_bonus_times', 'Refer & Earn Bonus times', 'trim|required|xss_clean');
            }

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'app_name' => form_error('app_name'),
                    'support_number' => form_error('support_number'),
                    'support_email' => form_error('support_email'),
                    'current_version' => form_error('current_version'),
                    'current_version_ios' => form_error('current_version_ios'),
                    'is_email_setting_on' => form_error('is_email_setting_on'),
                    'google_map_api_key' => form_error('google_map_api_key'),
                    'google_map_javascript_api_key' => form_error('google_map_javascript_api_key'),
                    'system_timezone_gmt' => form_error('system_timezone_gmt'),
                    'system_timezone' => form_error('system_timezone'),
                    'is_version_system_on' => form_error('is_version_system_on'),
                    'flat_delivery_charge' => form_error('flat_delivery_charge'),
                    'distance_unit' => form_error('distance_unit'),
                    'distance_delivery_charge' => form_error('distance_delivery_charge'),
                    'currency' => form_error('currency'),
                    'minimum_cart_amt' => form_error('minimum_cart_amt'),
                    'low_stock_limit' => form_error('low_stock_limit'),
                    'max_items_cart' => form_error('max_items_cart'),
                    'distance_matrix_api' => form_error('distance_matrix_api'),
                    'free_delivery_on_first_order' => form_error('free_delivery_on_first_order'),    
                    'is_app_maintenance_mode_on' => form_error('is_app_maintenance_mode_on'),
                    'is_rider_app_maintenance_mode_on' => form_error('is_rider_app_maintenance_mode_on'),
                    'is_partner_app_maintenance_mode_on' => form_error('is_partner_app_maintenance_mode_on'),
                    'is_web_maintenance_mode_on' => form_error('is_web_maintenance_mode_on'),
                    'tax' => form_error('tax'),
                    'is_refer_earn_on' => form_error('is_refer_earn_on'),
                    'logo' => form_error('logo'),
                    'favicon' => form_error('favicon'),
                    'supported_locals' => form_error('supported_locals'),
                    'min_refer_earn_order_amount' => form_error('min_refer_earn_order_amount'),
                    'refer_earn_bonus' => form_error('refer_earn_bonus'),
                    'refer_earn_method' => form_error('refer_earn_method'),
                    'max_refer_earn_amount' => form_error('max_refer_earn_amount'),
                    'refer_earn_bonus_times' => form_error('refer_earn_bonus_times'),
                    'customer_app_android_link' => form_error('customer_app_android_link'),
                    'partner_app_android_link' => form_error('partner_app_android_link'),
                    'rider_app_android_link' => form_error('rider_app_android_link'),
                    'customer_app_ios_link' => form_error('customer_app_ios_link'),
                    'partner_app_ios_link' => form_error('partner_app_ios_link'),
                    'rider_app_ios_link' => form_error('rider_app_ios_link'),
                );
                print_r(json_encode($this->response));
            } else {
                if($_POST['refer_earn_method'] == 'percentage'){
                    if($_POST['refer_earn_bonus'] > 100){
                        $this->response['error'] = true;
                        $this->response['csrfName'] = $this->security->get_csrf_token_name();
                        $this->response['csrfHash'] = $this->security->get_csrf_hash();
                        $this->response['message'] = 'Bonus percentage can not be grater then 100.';
                        print_r(json_encode($this->response));
                        return false;
                    }
                }
                
                $_POST['system_timezone_gmt'] = preg_replace('/\s+/', '', $_POST['system_timezone_gmt']);
                $_POST['system_timezone_gmt'] = ($_POST['system_timezone_gmt'] == '00:00') ? "+" . $_POST['system_timezone_gmt'] : $_POST['system_timezone_gmt'];
                $this->Setting_model->update_system_setting($_POST);
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = 'System Setting Updated Successfully';
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    public function update_web_settings()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'settings'), PERMISSION_ERROR_MSG, 'settings')) {
                return false;
            }
            $this->form_validation->set_rules('site_title', 'Site Title', 'trim|required|xss_clean');
            $this->form_validation->set_rules('support_number', 'Support number', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('support_email', 'Support Email', 'trim|required|xss_clean|valid_email');
            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = array(
                    'site_title' => form_error('site_title'),
                    'support_number' => form_error('support_number'),
                    'support_email' => form_error('support_email'),
                );
                print_r(json_encode($this->response));
            } else {
                $this->Setting_model->update_web_setting($this->input->post(null, true));
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = 'System Setting Updated Successfully';
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
