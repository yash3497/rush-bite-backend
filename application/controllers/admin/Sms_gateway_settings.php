<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SMS_gateway_settings extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper', 'sms_helper']);
        $this->load->model(['Setting_model', 'notification_model', 'category_model']);
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            
            $this->data['main_page'] = FORMS . 'sms-gateway-settings';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'SMS Gateway Settings | ' . $settings['app_name'];
            $this->data['meta_description'] = ' SMS Gateway Settings  | ' . $settings['app_name'];
            $this->data['sms_gateway_settings'] = get_settings('sms_gateway_settings', true);
            $this->data['send_notification_settings'] = get_settings('send_notification_settings', true);
            
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function add_sms_data()
    {
      
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
           
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $this->response['error'] = true;
                $this->response['message'] = SEMI_DEMO_MODE_MSG;
                echo json_encode($this->response);
                return false;
                exit();
            }
            if (print_msg(!has_permissions('update', 'sms-gateway-settings'), PERMISSION_ERROR_MSG, 'sms-gateway-settings')) {
                return false;
            }

            $this->form_validation->set_rules('base_url', 'Base URL', 'trim|required|xss_clean');
            $this->form_validation->set_rules('sms_gateway_method', 'Method ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('var_header_key', 'Header', 'trim|required|xss_clean');
            $this->form_validation->set_rules('var_header_value', 'Header value', 'trim|required|xss_clean');
            $this->form_validation->set_rules('mobile_no_val', 'Mobile number', 'trim|required|xss_clean');
            $this->form_validation->set_rules('country_code_val', 'country code', 'trim|required|xss_clean');
            $this->form_validation->set_rules('mobile_with_country_key_val', 'Mobile number with country code', 'trim|required|xss_clean');
            $this->form_validation->set_rules('message_val', 'Message value', 'trim|required|xss_clean');

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = validation_errors();
                print_r(json_encode($this->response));
            } else {
            $this->Setting_model->update_smsgateway($_POST);
            $this->response['error'] = false;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] = 'System Setting Updated Successfully';
            print_r(json_encode($this->response));
            }   


        }
    }

    public function update_notification_module()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
           
            if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $this->response['error'] = true;
                $this->response['message'] = SEMI_DEMO_MODE_MSG;
                echo json_encode($this->response);
                return false;
                exit();
            }

            $this->Setting_model->update_notification_setting($_POST);
            $this->response['error'] = false;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] = (isset($edit_id)) ? ' Data Updated Successfully' : 'Data Added Successfully';

            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }

}
