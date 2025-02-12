<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payment_settings extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Setting_model');

        if (!has_permissions('read', 'payment_settings')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }


    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'payment-settings';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Payment Methods Management | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Payment Methods Management  | ' . $settings['app_name'];
            $this->data['settings'] = get_settings('payment_method', true);
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function update_payment_settings()
    {

        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'payment_settings'), PERMISSION_ERROR_MSG, 'payment_settings')) {
                return false;
            }
            $_POST['temp'] = '1';
            $this->form_validation->set_rules('temp', '', 'trim|required|xss_clean');

            if (isset($_POST['paypal_payment_method'])) {
                $this->form_validation->set_rules('paypal_mode', 'Payyou Payment Mode', 'trim|required|xss_clean');
                $this->form_validation->set_rules('paypal_business_email', 'Paypal Business Email', 'trim|required|xss_clean|valid_email');
                $this->form_validation->set_rules('paypal_client_id', 'Paypal Client Id', 'trim|required|xss_clean');
                $this->form_validation->set_rules('paypal_secret_key', 'Paypal Secret Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('currency_code', 'Currency Code', 'trim|required|xss_clean');
            }
            if (isset($_POST['payumoney_payment_method'])) {
                $this->form_validation->set_rules('payumoney_mode', 'Payumoney Mode', 'trim|required|xss_clean');
                $this->form_validation->set_rules('payumoney_merchant_key', 'Payumoney Merchant Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('payumoney_merchant_id', 'Payumoney Merchant Id', 'trim|required|xss_clean');
                $this->form_validation->set_rules('payumoney_salt', 'Payumoney Salt', 'trim|required|xss_clean');
            }
            if (isset($_POST['razorpay_payment_method'])) {
                $this->form_validation->set_rules('razorpay_key_id', 'Razorpay Key Id', 'trim|required|xss_clean');
                $this->form_validation->set_rules('razorpay_secret_key', 'Razorpay Secret Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('refund_webhook_secret_key', 'Refund Webhook Secret Key', 'trim|required|xss_clean');
            }

            if (isset($_POST['paystack_payment_method'])) {
                $this->form_validation->set_rules('paystack_key_id', 'Paystack Key Id', 'trim|required|xss_clean');
                $this->form_validation->set_rules('paystack_secret_key', 'Paystack Secret Key', 'trim|required|xss_clean');
            }

            if (isset($_POST['flutterwave_payment_method'])) {
                $this->form_validation->set_rules('flutterwave_public_key', 'Flutterwave Public Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('flutterwave_secret_key', 'Flutterwave Secret Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('flutterwave_encryption_key', 'Flutterwave Encryption Key', 'trim|required|xss_clean');
            }

            if (isset($_POST['stripe_payment_method'])) {
                $this->form_validation->set_rules('stripe_publishable_key', 'Stripe Publishable Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('stripe_secret_key', 'Stripe Secret Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('stripe_webhook_secret_key', 'Stripe Webhook Secret Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('stripe_currency_code', 'Stripe Currency Code', 'trim|required|xss_clean');
            }
            if (isset($_POST['paytm_payment_method'])) {
                $this->form_validation->set_rules('paytm_payment_mode', 'Paytm Payment Mode', 'trim|required|xss_clean');
                $this->form_validation->set_rules('paytm_merchant_key', 'Paytm Merchant Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('paytm_merchant_id', 'Paytm Merchant ID', 'trim|required|xss_clean');
                if ($_POST['paytm_payment_mode'] == 'production') {
                    $this->form_validation->set_rules('paytm_website', 'Paytm website', 'trim|required|xss_clean');
                    $this->form_validation->set_rules('paytm_industry_type_id', 'Paytm Industry Type ID', 'trim|required|xss_clean');
                }
            }

            if (isset($_POST['midtrans_payment_method'])) {
                $this->form_validation->set_rules('midtrans_payment_mode', 'Midtrans Payment Mode', 'trim|required|xss_clean');
                $this->form_validation->set_rules('midtrans_client_key', 'Midtrans Client  Key', 'trim|required|xss_clean');
                $this->form_validation->set_rules('midtrans_merchant_id', 'Midtrans Merchant ID', 'trim|required|xss_clean');
                $this->form_validation->set_rules('midtrans_server_key', 'Midtrans Server Key', 'trim|required|xss_clean');
            }

            if (isset($_POST['phonepe_payment_method'])) {
                $this->form_validation->set_rules('phonepe_payment_mode', 'Phonepe Payment Mode', 'trim|required|xss_clean');
                $this->form_validation->set_rules('phonepe_webhook_url', 'Phonepe Webhook Url', 'trim|required|xss_clean');
                $this->form_validation->set_rules('phonepe_appid', 'Phonepe AppId', 'trim|required|xss_clean');
                $this->form_validation->set_rules('phonepe_marchant_id', 'Phonepe Marchant Id', 'trim|required|xss_clean');
                $this->form_validation->set_rules('phonepe_salt_index', 'Phonepe Salt Index', 'trim|required|xss_clean');
                $this->form_validation->set_rules('phonepe_salt_key', 'Phonepe Salt Key', 'trim|required|xss_clean');
            }

            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'paypal_mode' => form_error('paypal_mode'),
                    'paypal_business_email' => form_error('paypal_business_email'),
                    'paypal_client_id' => form_error('paypal_client_id'),
                    'paypal_secret_key' => form_error('paypal_secret_key'),
                    'currency_code' => form_error('currency_code'),
                    'payumoney_mode' => form_error('payumoney_mode'),
                    'payumoney_merchant_key' => form_error('payumoney_merchant_key'),
                    'payumoney_merchant_id' => form_error('payumoney_merchant_id'),
                    'payumoney_salt' => form_error('payumoney_salt'),
                    'razorpay_key_id' => form_error('razorpay_key_id'),
                    'razorpay_secret_key' => form_error('razorpay_secret_key'),
                    'refund_webhook_secret_key' => form_error('refund_webhook_secret_key'),
                    'paystack_key_id' => form_error('paystack_key_id'),
                    'paystack_secret_key' => form_error('paystack_secret_key'),
                    'flutterwave_public_key' => form_error('flutterwave_public_key'),
                    'flutterwave_secret_key' => form_error('flutterwave_secret_key'),
                    'flutterwave_encryption_key' => form_error('flutterwave_encryption_key'),
                    'stripe_publishable_key' => form_error('stripe_publishable_key'),
                    'stripe_secret_key' => form_error('stripe_secret_key'),
                    'stripe_webhook_secret_key' => form_error('stripe_webhook_secret_key'),
                    'stripe_currency_code' => form_error('stripe_currency_code'),
                    'paytm_payment_mode' => form_error('paytm_payment_mode'),
                    'paytm_merchant_key' => form_error('paytm_merchant_key'),
                    'paytm_merchant_id' => form_error('paytm_merchant_id'),
                    'paytm_website' => form_error('paytm_website'),
                    'paytm_industry_type_id' => form_error('paytm_industry_type_id'),
                    'midtrans_payment_mode' => form_error('midtrans_payment_mode'),
                    'midtrans_client_key' => form_error('midtrans_client_key'),
                    'midtrans_merchant_id' => form_error('midtrans_merchant_id'),
                    'midtrans_server_key' => form_error('midtrans_server_key'),
                    'phonepe_payment_mode' => form_error('phonepe_payment_mode'),
                    'phonepe_webhook_url' => form_error('phonepe_webhook_url'),
                    'phonepe_appid' => form_error('phonepe_appid'),
                    'phonepe_marchant_id' => form_error('phonepe_marchant_id'),
                    'phonepe_salt_index' => form_error('phonepe_salt_index'),
                    'phonepe_salt_key' => form_error('phonepe_salt_key'),
                );
                print_r(json_encode($this->response));
            } else {
                $this->Setting_model->update_payment_method($_POST);
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
