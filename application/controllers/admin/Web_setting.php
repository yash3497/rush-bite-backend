<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Web_setting extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper', 'file']);
        $this->load->model('Setting_model');

        if (!has_permissions('read', 'settings')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'web-settings';
            $settings = get_settings('system_settings', true);
            $this->data['logo'] = get_settings('web_logo');
            $this->data['favicon'] = get_settings('web_favicon');
            $this->data['landing_page_main_image'] = get_settings('landing_page_main_image');
            $this->data['light_logo'] = get_settings('light_logo');
            $this->data['title'] = 'Web Settings | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Web Settings | ' . $settings['app_name'];
            $this->data['web_settings'] = get_settings('web_settings', true);
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
            $this->form_validation->set_rules('current_version', 'Current Version', 'trim|required|xss_clean');
            $this->form_validation->set_rules('minimum_version_required', 'Minimum version required', 'trim|required|xss_clean');
            $this->form_validation->set_rules('delivery_charge', 'Delivery charge', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('min_amount', 'Minimum amount', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('system_timezone_gmt', 'System GMT timezone', 'trim|required|xss_clean');
            $this->form_validation->set_rules('system_timezone', 'System timezone', 'trim|required|xss_clean');
            $this->form_validation->set_rules('is_version_system_on', 'Version System', 'trim|xss_clean');
            $this->form_validation->set_rules('area_wise_delivery_charge', 'Area Wise Delivery Charges', 'trim|xss_clean');
            $this->form_validation->set_rules('currency', 'Currency', 'trim|required|xss_clean');
            $this->form_validation->set_rules('max_product_return_days', 'Maximum Product Return Day', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('rider_bonus_percentage', 'Rider Bonus', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('minimum_cart_amt', 'Minimum Cart Amount', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('max_items_cart', 'Max items Allowed In Cart', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('logo', 'Logo', 'trim|required|xss_clean', array('required' => 'Logo is required'));
            $this->form_validation->set_rules('favicon', 'Favicon', 'trim|required|xss_clean', array('required' => 'Favicon is required'));

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = array(
                    'app_name' => form_error('app_name'),
                    'support_number' => form_error('support_number'),
                    'support_email' => form_error('support_email'),
                    'current_version' => form_error('current_version'),
                    'minimum_version_required' => form_error('minimum_version_required'),
                    'delivery_charge' => form_error('delivery_charge'),
                    'min_amount' => form_error('min_amount'),
                    'system_timezone_gmt' => form_error('system_timezone_gmt'),
                    'system_timezone' => form_error('system_timezone'),
                    'is_version_system_on' => form_error('is_version_system_on'),
                    'area_wise_delivery_charge' => form_error('area_wise_delivery_charge'),
                    'currency' => form_error('currency'),
                    'max_product_return_days' => form_error('max_product_return_days'),
                    'rider_bonus_percentage' => form_error('rider_bonus_percentage'),
                    'minimum_cart_amt' => form_error('minimum_cart_amt'),
                    'max_items_cart' => form_error('max_items_cart'),
                    'logo' => form_error('logo'),
                    'favicon' => form_error('favicon'),
                );
                print_r(json_encode($this->response));
            } else {
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

    public function web()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'web-settings';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Settings | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Settings  | ' . $settings['app_name'];
            $this->data['timezone'] = get_timezone_array();
            $this->data['logo'] = get_settings('logo');
            $this->data['light_logo'] = get_settings('light_logo');
            $this->data['favicon'] = get_settings('favicon');
            $this->data['settings'] = get_settings('system_settings', true);
            $this->data['currency'] = get_settings('currency');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function firebase()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'firebase-settings';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Web Settings | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Web Settings | ' . $settings['app_name'];
            $this->data['firebase_settings'] = get_settings('firebase_settings', true);
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function store_firebase()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'settings'), PERMISSION_ERROR_MSG, 'settings')) {
                return false;
            }
            $this->form_validation->set_rules('apiKey', 'API Key', 'trim|required|xss_clean');
            $this->form_validation->set_rules('authDomain', 'Auth Domain', 'trim|required|xss_clean');
            $this->form_validation->set_rules('databaseURL', 'Database URL', 'trim|required|xss_clean');
            $this->form_validation->set_rules('projectId', 'Project ID', 'trim|required|xss_clean');
            $this->form_validation->set_rules('storageBucket', 'Storage Bucket', 'trim|required|xss_clean');
            $this->form_validation->set_rules('messagingSenderId', 'Messaging Sender ID', 'trim|required|xss_clean');
            $this->form_validation->set_rules('appId', 'APP Id', 'trim|required|xss_clean');
            $this->form_validation->set_rules('measurementId', 'Measurement ID', 'trim|required|xss_clean');
            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = array(
                    'apiKey' => form_error('apiKey'),
                    'authDomain' => form_error('authDomain'),
                    'databaseURL' => form_error('databaseURL'),
                    'projectId' => form_error('projectId'),
                    'storageBucket' => form_error('storageBucket'),
                    'messagingSenderId' => form_error('messagingSenderId'),
                    'appId' => form_error('appId'),
                    'measurementId' => form_error('measurementId'),
                );
                print_r(json_encode($this->response));
            } else {

                $apiKey = $this->input->post('apiKey', true);
                $authDomain = $this->input->post('authDomain', true);
                $databaseURL = $this->input->post('databaseURL', true);
                $projectId = $this->input->post('projectId', true);
                $storageBucket = $this->input->post('storageBucket', true);
                $messagingSenderId = $this->input->post('messagingSenderId', true);
                $appId = $this->input->post('appId', true);
                $measurementId = $this->input->post('measurementId', true);

                $data_json = array(
                    'apiKey' => !empty($apiKey) ? $apiKey : '',
                    'authDomain' => !empty($authDomain) ? $authDomain : '',
                    'databaseURL' => !empty($databaseURL) ? $databaseURL : '',
                    'storageBucket' => !empty($storageBucket) ? $storageBucket : '',
                    'projectId' => !empty($projectId) ? $projectId : '',
                    'messagingSenderId' => !empty($messagingSenderId) ? $messagingSenderId : '',
                    'appId' => !empty($appId) ? $appId : '',
                    'measurementId' => !empty($measurementId) ? $measurementId : '',
                );

                $data = array(
                    'data' => json_encode($data_json)
                );

                $template_path     = 'assets/admin/js/fcm_settings.js';
                $template_path2     = 'assets/admin/js/fcm_config.js';

                $output_path     = 'firebase-messaging-sw.js';
                $output_path2     = 'firebase-config.js';
            // ===============================================
                $database_file = file_get_contents($template_path);
                

                $new  = str_replace("%APIKEY%", $apiKey, $database_file);
                $new  = str_replace("%AUTHDOMAIN%", $authDomain, $new);
                $new  = str_replace("%DATABASEURL%", $databaseURL, $new);
                $new  = str_replace("%PROJECTID%", $projectId, $new);
                $new  = str_replace("%STRORAGEBUCKET%", $storageBucket, $new);
                $new  = str_replace("%MESSAGINGSENDERID%", $messagingSenderId, $new);
                $new  = str_replace("%APPID%", $appId, $new);
                $new  = str_replace("%MEASUREMENTID%", $measurementId, $new);
                write_file($output_path, $new);

                $database_file = file_get_contents($template_path2);

                $new  = str_replace("%APIKEY%", $apiKey, $database_file);
                $new  = str_replace("%AUTHDOMAIN%", $authDomain, $new);
                $new  = str_replace("%DATABASEURL%", $databaseURL, $new);
                $new  = str_replace("%PROJECTID%", $projectId, $new);
                $new  = str_replace("%STRORAGEBUCKET%", $storageBucket, $new);
                $new  = str_replace("%MESSAGINGSENDERID%", $messagingSenderId, $new);
                $new  = str_replace("%APPID%", $appId, $new);
                $new  = str_replace("%MEASUREMENTID%", $measurementId, $new);
                write_file($output_path2, $new);
            // ================================================
                $this->Setting_model->firebase_setting($_POST);
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = 'Firebase Setting Updated Successfully';
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
