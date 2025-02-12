<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Promo_code extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model('Promo_code_model');

        if (!has_permissions('read', 'promo_code')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'promo-code';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Add Promo code | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Add Promo code  | ' . $settings['app_name'];
            if (isset($_GET['edit_id'])) {
                $this->data['fetched_details'] = fetch_details(['id' => $_GET['edit_id']], 'promo_codes');
            }
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_promo_code()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-promo-code';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Promo Code Management | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Promo Code Management  | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_promo_code()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Promo_code_model->get_promo_code_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function delete_promo_code()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('delete', 'promo_code')) {
                return false;
            }

            if (delete_details(['id' => $_GET['id']], 'promo_codes') == TRUE) {
                $this->response['error'] = false;
                $this->response['message'] = 'Deleted Succesfully';
                print_r(json_encode($this->response));
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Something Went Wrong';
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function add_promo_code()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('create', 'promo_code')) {
                $response["error"]   = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response["message"] = "You don't have permission to create / update promo code !";
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }

            $this->form_validation->set_rules('promo_code', 'Promo Code ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('message', 'Message ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('start_date', 'Start date ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('end_date', 'End date ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('no_of_users', 'No of Users ', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('minimum_order_amount', 'Minimum Order Amount ', 'trim|numeric|required|xss_clean');
            $this->form_validation->set_rules('discount', 'Discount ', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('discount_type', 'Discount Type ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('max_discount_amount', 'Maximum Discount Amount ', 'trim|numeric|required|xss_clean');
            $this->form_validation->set_rules('repeat_usage', 'Repeat Usage ', 'trim|required|xss_clean');
            if ($_POST['repeat_usage'] == '1') {
                $this->form_validation->set_rules('no_of_repeat_usage', 'No. of Repeat Usage ', 'trim|required|numeric|xss_clean');
            }
    

            $this->form_validation->set_rules('status', 'Status ', 'trim|required|xss_clean');

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
             
                $this->response['messages'] = array(
                    'promo_code' => form_error('promo_code'),
                    'message' => form_error('message'),
                    'start_date' => form_error('start_date'),
                    'end_date' => form_error('end_date'),
                    'no_of_users' => form_error('no_of_users'),
                    'minimum_order_amount' => form_error('minimum_order_amount'),
                    'discount' => form_error('discount'),
                    'discount_type' => form_error('discount_type'),
                    'max_discount_amount' => form_error('max_discount_amount'),
                    'repeat_usage' => form_error('repeat_usage'),
                    'no_of_repeat_usage' => form_error('no_of_repeat_usage'),    
                );
                print_r(json_encode($this->response));
            } else {

                if (isset($_POST['edit_promo_code'])) {

                    if (is_exist(['promo_code' => $_POST['promo_code']], 'promo_codes', $_POST['edit_promo_code'])) {
                        $response["error"]   = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["message"] = "Promo Code Already Exists !";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                } else {
                    if (is_exist(['promo_code' => $_POST['promo_code']], 'promo_codes')) {
                        $response["error"]   = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["message"] = "Promo Code Already Exists !";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }

                $minimum_order_amount = $_POST['minimum_order_amount'];
                $discount = $_POST['discount'];

                if ($_POST['discount_type'] == 'percentage') {
                    if ($discount <= 0 || $discount > 100) {
                        $response["error"]   = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["message"] = "Discount percentage cannot exceed 100 !";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                } elseif ($_POST['discount_type'] == 'amount') {
                    if ($discount >= $minimum_order_amount) {
                        $response["error"]   = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["message"] = "Discount can not be grater then or equal to the Minimum order amount !";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }

                $startDate = $_POST["start_date"];
                $endDate = $_POST["end_date"];


                $startDateTime = new DateTime($startDate);
                $endDateTime = new DateTime($endDate);


                if ($endDateTime <= $startDateTime) {

                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'The start date must be earlier than the end date.';
                    print_r(json_encode($this->response));
                } else {

                    $this->Promo_code_model->add_promo_code_details($_POST);
                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $message = (isset($_POST['edit_promo_code'])) ? 'Promo code Updated Successfully' : 'Promo code Added Successfully';
                    $this->response['message'] = $message;
                    $this->response['location'] = base_url('admin/promo-code/manage-promo-code');
                    print_r(json_encode($this->response));
                }
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
