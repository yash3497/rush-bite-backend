<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_code extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Setting_model');

        if (!has_permissions('read', 'contact_us')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'purchase-code';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'System Regsitration | Purchase Code Validation | ' . $settings['app_name'];
            $this->data['meta_description'] = 'System Regsitration | Purchase Code Validation |  | ' . $settings['app_name'];
            $this->data['doctor_brown'] = get_settings('doctor_brown');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function validator()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            
                if (isset($_POST['purchase_code_app']) && !empty($_POST['purchase_code_app'])) {

                    $purchase_code_app = $this->input->post("purchase_code_app", true);
                    $url = "https://wrteam.in/validator/home/validator_new?purchase_code=$purchase_code_app&domain_url=" . base_url() . "&item_id=" . ASHTANGA;
                    $result = curl($url);
                  
                    if (isset($result['body']) && !empty($result['body'])) {

                        if (isset($result['body']['error']) && $result['body']['error'] == 0) {

                            $doctor_brown = get_settings('doctor_brown');
                            if (empty($doctor_brown)) {
                                $doctor_brown['code_bravo'] = $result["body"]["purchase_code"];
                                $doctor_brown['time_check'] = $result["body"]["token"];
                                $doctor_brown['code_adam'] = $result["body"]["username"];
                                $doctor_brown['dr_firestone'] = $result["body"]["item_id"];

                                $data['variable'] = "doctor_brown";
                                $data['value'] = json_encode($doctor_brown);
                                insert_details($data, 'settings');
                            }
                            $this->response['error'] = false;
                            $this->response['csrfName'] = $this->security->get_csrf_token_name();
                            $this->response['csrfHash'] = $this->security->get_csrf_hash();
                            $this->response['message'] = $result['body']['message'];
                            print_r(json_encode($this->response));
                        } else {
                            $this->response['error'] = true;
                            $this->response['csrfName'] = $this->security->get_csrf_token_name();
                            $this->response['csrfHash'] = $this->security->get_csrf_hash();
                            $this->response['message'] = $result['body']['message'];
                            print_r(json_encode($this->response));
                        }
                    } else {
                        $this->response['error'] = true;
                        $this->response['csrfName'] = $this->security->get_csrf_token_name();
                        $this->response['csrfHash'] = $this->security->get_csrf_hash();
                        $this->response['message'] = "Somthing Went wrong. Please contact Super admin.";
                        print_r(json_encode($this->response));
                    }
                }
                if (isset($_POST['purchase_code_web']) && !empty($_POST['purchase_code_web'])) {
                    $purchase_code_web = $this->input->post("purchase_code_web", true);
                    $url = "https://wrteam.in/validator/home/validator_new?purchase_code=$purchase_code_web&domain_url=" . base_url() . "&item_id=" . ASHTANGAWEB;
                    $result = curl($url);
                    
                    if (isset($result['body']) && !empty($result['body'])) {

                        if (isset($result['body']['error']) && $result['body']['error'] == 0) {

                            $doctor_brown_web = get_settings('doctor_brown_web');
                            if (empty($doctor_brown_web)) {
                                $doctor_brown_web['code_bravo'] = $result["body"]["purchase_code"];
                                $doctor_brown_web['time_check'] = $result["body"]["token"];
                                $doctor_brown_web['code_adam'] = $result["body"]["username"];
                                $doctor_brown_web['dr_firestone'] = $result["body"]["item_id"];

                                $data['variable'] = "doctor_brown_web";
                                $data['value'] = json_encode($doctor_brown_web);
                                insert_details($data, 'settings');
                            }
                            $this->response['error'] = false;
                            $this->response['csrfName'] = $this->security->get_csrf_token_name();
                            $this->response['csrfHash'] = $this->security->get_csrf_hash();
                            $this->response['message'] = $result['body']['message'];
                            print_r(json_encode($this->response));
                        } else {
                            $this->response['error'] = true;
                            $this->response['csrfName'] = $this->security->get_csrf_token_name();
                            $this->response['csrfHash'] = $this->security->get_csrf_hash();
                            $this->response['message'] = $result['body']['message'];
                            print_r(json_encode($this->response));
                        }
                    } else {
                        $this->response['error'] = true;
                        $this->response['csrfName'] = $this->security->get_csrf_token_name();
                        $this->response['csrfHash'] = $this->security->get_csrf_hash();
                        $this->response['message'] = "Somthing Went wrong. Please contact Super admin.";
                        print_r(json_encode($this->response));
                    }
                }
           
        } else {
            redirect('admin/login', 'refresh');
        }
    }
    
    public function de_register()
    {

        $this->form_validation->set_rules('app_purchase_code', 'App Purchase Code', 'trim|xss_clean');
        $this->form_validation->set_rules('web_purchase_code', 'Web Purchase Code', 'trim|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] = array(
                'app_purchase_code' => form_error('app_purchase_code'),
                'web_purchase_code' => form_error('web_purchase_code'),
            );
            print_r(json_encode($this->response));
        } else {

            if(isset($_POST['app_purchase_code']) && !empty($_POST['app_purchase_code'])){

                $purchasecode = $this->input->post("app_purchase_code", true);
                $deregister_data = get_settings('doctor_brown');
            }elseif(isset($_POST['web_purchase_code']) && !empty($_POST['web_purchase_code'])){
                $purchasecode = $this->input->post("web_purchase_code", true);
                $deregister_data = get_settings('doctor_brown_web');
            }else{
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = "Something went wrong !";
                print_r(json_encode($this->response));
                return false;
            }
            $purchasecode_data = isset($deregister_data) ? json_decode($deregister_data, true) : "";
            $purchasecode_data['domain_url'] = base_url();
            if (!empty($purchasecode_data)) {

                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['data'] = $purchasecode_data;
                print_r(json_encode($this->response));
            } else {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = "Something went wrong!";
                print_r(json_encode($this->response));
            }

        }

    }

    public function delete_purchasecode_data()
    {
        

            $this->form_validation->set_rules('de_register_app_code', 'De-register App Code', 'trim|xss_clean');
            $this->form_validation->set_rules('de_register_web_code', 'De-register Web Code', 'trim|xss_clean');
        
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            $this->response['message'] = array(
                'de_register_app_code' => form_error('de_register_app_code'),
                'de_register_web_code' => form_error('de_register_web_code'),
            );
            print_r(json_encode($this->response));
        } else {

            if (isset($_POST['de_register_app_code']) && !empty($_POST['de_register_app_code'])) {
                
                if (delete_details(['variable' => 'doctor_brown'], 'settings')) {
                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = "Purchase code has been successfully de-registered!";
                    print_r(json_encode($this->response));
                } else {
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = "Purchase code de-registration failed!";
                    print_r(json_encode($this->response));
                }

            } elseif(isset($_POST['de_register_web_code']) && !empty($_POST['de_register_web_code'])){
                if (delete_details(['variable' => 'doctor_brown_web'], 'settings')) {
                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = "Purchase code de-registerd successfully!";
                    print_r(json_encode($this->response));
                } else {
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = "Purchase code de-register failed!";
                    print_r(json_encode($this->response));
                }
            }
            
            
            else {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = "Something went wrong!";
                print_r(json_encode($this->response));
            }

        }
    }
}
