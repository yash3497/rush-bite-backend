<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Riders extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model(['Rider_model', 'rating_model']);
        if (!has_permissions('read', 'rider')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (isset($_GET['edit_rider_request'])) {
                $this->data['main_page'] = FORMS . 'rider';
                $settings = get_settings('system_settings', true);
                if (isset($_GET['edit_rider_request']) && !empty($_GET['edit_rider_request'])) {
                    $this->data['title'] = ' Rider Request | ' . $settings['app_name'];
                } else {

                    $this->data['title'] = 'Add Rider | ' . $settings['app_name'];
                }
                $this->data['meta_description'] = 'Add Rider  | ' . $settings['app_name'];
                if (isset($_GET['edit_rider_request']) && !empty($_GET['edit_rider_request'])) {
                    $this->data['fetched_data'] = $this->db->select(' u.* ')
                        ->join('users_groups ug', ' ug.user_id = u.id ')
                        ->where(['ug.group_id' => '3', 'ug.user_id' => $_GET['edit_rider_request']])
                        ->get('users u')
                        ->result_array();

                    $this->data['rider_request'] = 1;  // while rider request is 1 then only branch select option will be visible.
                }
            }else{
                $this->data['main_page'] = FORMS . 'rider';
                $settings = get_settings('system_settings', true);
                $this->data['title'] = 'Add Rider | ' . $settings['app_name'];
                $this->data['meta_description'] = 'Add Rider  | ' . $settings['app_name'];
                if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
                    $this->data['fetched_data'] = $this->db->select(' u.* ')
                        ->join('users_groups ug', ' ug.user_id = u.id ')
                        ->where(['ug.group_id' => '3', 'ug.user_id' => $_GET['edit_id']])
                        ->get('users u')
                        ->result_array();
                }

            }
            $this->data['currency'] = get_settings('currency');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_rider()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-rider';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Rider Management | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Rider Management  | ' . $settings['app_name'];
            $this->data['currency'] = get_settings('currency');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_riders()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            return $this->Rider_model->get_riders_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }

        public function view_cash_collection_riders()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            return $this->Rider_model->get_riders_list($cash_collection = true);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function delete_riders()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (print_msg(!has_permissions('delete', 'rider'), PERMISSION_ERROR_MSG, 'rider', false)) {
                return true;
            }

            if (update_details(['group_id' => '2'], ['user_id' => $_GET['id'], 'group_id' => 3], 'users_groups') == TRUE) {
                $this->response['error'] = false;
                $this->response['message'] = 'User removed from Rider succesfully';
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


    public function add_rider()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (isset($_POST['edit_rider'])) {
                if (print_msg(!has_permissions('update', 'rider'), PERMISSION_ERROR_MSG, 'rider')) {
                    return true;
                }
            } else {
                if (print_msg(!has_permissions('create', 'rider'), PERMISSION_ERROR_MSG, 'rider')) {
                    return true;
                }
            }

            $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean|valid_email');
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]');
            if (!isset($_POST['edit_rider']) && !isset($_POST['edit_rider_request'])) {
                $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
                $this->form_validation->set_rules('confirm_password', 'Confirm password', 'trim|required|matches[password]|xss_clean');
            }
            $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');
            $this->form_validation->set_rules('serviceable_city[]', 'Serviceable city', 'trim|required|xss_clean');
            $this->form_validation->set_rules('active', 'Status', 'trim|xss_clean');
            $this->form_validation->set_rules('commission_method', 'Commission Method', 'trim|required|xss_clean');
            if (isset($_POST['commission_method']) && !empty($_POST['commission_method']) && $_POST['commission_method'] == "percentage_on_delivery_charges") {
                $this->form_validation->set_rules('percentage', 'Percentage', 'trim|xss_clean|required');
            }
            if (isset($_POST['commission_method']) && !empty($_POST['commission_method']) && $_POST['commission_method'] == "fixed_commission_per_order") {
                $this->form_validation->set_rules('commission', 'Commission', 'trim|xss_clean|required');
            }
            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'name' => form_error('name'),
                    'email' => form_error('email'),
                    'password' => form_error('password'),
                    'confirm_password' => form_error('confirm_password'),
                    'address' => form_error('address'),
                    'serviceable_city' => form_error('serviceable_city'),
                    'active' => form_error('active'),
                    'commission_method' => form_error('commission_method'),
                    'percentage' => form_error('percentage'),
                    'commission' => form_error('commission'),
                );
                print_r(json_encode($this->response));
            } else {
                /* process commission params */
               $serviceable_city = isset($_POST['serviceable_city']) && !empty($_POST['serviceable_city']) ? implode("," , $_POST['serviceable_city']) : "";

                if (isset($_POST['commission']) && !empty($_POST['commission'])) {
                } else {

                    if (isset($_POST['percentage']) && !empty($_POST['percentage'])) {

                        if ($_POST['percentage'] <= 0 || $_POST['percentage'] > 100) {
                            $response["error"]   = true;
                            $response["message"] = "Percentage on Delivery Charges is not valid";
                            $response['csrfName'] = $this->security->get_csrf_token_name();
                            $response['csrfHash'] = $this->security->get_csrf_hash();
                            $response["data"] = array();
                            echo json_encode($response);
                            return false;
                        }
                    }
                }

                $commission_method = $this->input->post("commission_method", true);
                $commission = 0;
                if (isset($commission_method) && !empty($commission_method) && $commission_method == "percentage_on_delivery_charges") {
                    $commission = $this->input->post("percentage");
                }
                if (isset($commission_method) && !empty($commission_method) && $commission_method == "fixed_commission_per_order") {
                    $commission = $this->input->post("commission");
                }

                $_POST['commission'] = $commission;
                $_POST['percentage'] = $this->input->post("percentage", true);

                if (isset($_POST['edit_rider'])) {
                      if ($_POST['commission_method'] == 'percentage_on_delivery_charges') {
                        if (isset($_POST['percentage']) && !empty($_POST['percentage'])) {
                            if ($_POST['percentage'] <= 0 || $_POST['percentage'] > 100) {
                                $response["error"]   = true;
                                $response["message"] = "Percentage on Delivery Charges is not valid";
                                $response['csrfName'] = $this->security->get_csrf_token_name();
                                $response['csrfHash'] = $this->security->get_csrf_hash();
                                $response["data"] = array();
                                echo json_encode($response);
                                return false;
                            }
                        }
                    }
                    if (isset($_POST['edit_rider_request'])) {
                        if (!edit_unique($this->input->post('email', true), 'users.email.' . $this->input->post('edit_rider_request', true) . '') || !edit_unique($this->input->post('mobile', true), 'users.mobile.' . $this->input->post('edit_rider_request', true) . '')) {
                            $response["error"]   = true;
                            $response["message"] = "Email or mobile already exists !";
                            $response['csrfName'] = $this->security->get_csrf_token_name();
                            $response['csrfHash'] = $this->security->get_csrf_hash();
                            $response["data"] = array();
                            echo json_encode($response);
                            return false;
                        }
                    } else {
                        if (isset($_POST['edit_rider'])) {
                            // print_r($_POST);
                            // die;
                            if($_POST['old_rider_email'] == $_POST['email'] && $_POST['old_rider_mobile'] == $_POST['mobile']){
                                // print_r("if");
                            }else{
                                // print_r("else");
                                if (!edit_unique($this->input->post('email', true), 'users.email.' . $this->input->post('edit_rider', true) . '') || !edit_unique($this->input->post('mobile', true), 'users.mobile.' . $this->input->post('edit_rider', true) . '')) {
                                    $response["error"]   = true;
                                    $response["message"] = "Email or mobile already exists !";
                                    $response['csrfName'] = $this->security->get_csrf_token_name();
                                    $response['csrfHash'] = $this->security->get_csrf_hash();
                                    $response["data"] = array();
                                    echo json_encode($response);
                                    return false;
                                }
                            }
                        }
                    }
                    $_POST['serviceable_city'] = $this->input->post('serviceable_city', true);
                    $_POST['active'] = $this->input->post("active", true);
                    $this->Rider_model->update_rider($_POST);
                } else {
                    if (isset($_POST['edit_rider'])) {
                    if (!$this->form_validation->is_unique($_POST['mobile'], 'users.mobile') || !$this->form_validation->is_unique($_POST['email'], 'users.email')) {
                        $response["error"]   = true;
                        $response["message"] = "Email or mobile already exists !";
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }

                    $identity_column = $this->config->item('identity', 'ion_auth');
                    $email = strtolower($this->input->post('email'));
                    $mobile = $this->input->post('mobile');
                    $identity = ($identity_column == 'mobile') ? $mobile : $email;
                    $password = $this->input->post('password');
                    if (validatePassword($password)) {

                        $additional_data = [
                            'username' => $this->input->post('name'),
                            'address' => $this->input->post('address'),
                            'serviceable_city' => $serviceable_city,
                            'commission_method' => $commission_method,
                            'commission' => $commission,
                        ];

                        $this->ion_auth->register($identity, $password, $email, $additional_data, ['3']);
                        update_details(['active' => 1], [$identity_column => $identity], 'users');
                    }else{
                        $response["error"]   = true;
                        $response["message"] = "Password Should be atleast 8 character, one upparcase letter, one lowercase letter and one number!";
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                        }
                }

                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $message = (isset($_POST['edit_rider'])) ? 'Rider Update Successfully' : 'Rider Added Successfully';
                $this->response['message'] = $message;
                $this->response['location'] = base_url('admin/riders/manage_rider');
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function get_rating_list()
    {

        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            return $this->rating_model->get_rider_rating();
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_cash()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'cash-collection';
            $settings = get_settings('system_settings', true);
            $this->data['curreny'] = $settings['currency'];
            $this->data['riders'] = $this->db->where(['ug.group_id' => '3', 'u.active' => 1])->join('users_groups ug', 'ug.user_id = u.id')->get('users u')->result_array();
            $this->data['title'] = 'View Cash Collection | ' . $settings['app_name'];
            $this->data['meta_description'] = ' View Cash Collection  | ' . $settings['app_name'];
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function get_cash_collection()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->Rider_model->get_cash_collection_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function manage_cash_collection()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('create', 'fund_transfer'), PERMISSION_ERROR_MSG, 'fund_transfer')) {
                return false;
            }

            $this->form_validation->set_rules('rider_id', 'Rider', 'trim|required|xss_clean|numeric');
            $this->form_validation->set_rules('amount', 'Amount', 'trim|required|xss_clean|numeric|greater_than[0]');
            $this->form_validation->set_rules('date', 'Date', 'trim|required|xss_clean');
            $this->form_validation->set_rules('message', 'Message', 'trim|xss_clean');
            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'rider_id' => form_error('rider_id'),
                    'amount' => form_error('amount'),
                    'date' => form_error('date'),
                    'message' => form_error('message'),
                );
                echo json_encode($this->response);
                return false;
            } else {
                $rider_id = $this->input->post('rider_id', true);
                if (!is_exist(['id' => $rider_id], 'users')) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Rider is not exist in your database';
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    print_r(json_encode($this->response));
                    return false;
                }
                $res = fetch_details(['id' => $rider_id], 'users', 'cash_received');
                $amount = $this->input->post('amount', true);
                $date = $this->input->post('date', true);
                $message = (isset($_POST['message']) && !empty($_POST['message'])) ? $this->input->post('message', true) : "Rider cash collection by admin";

                if ($res[0]['cash_received'] < $amount) {
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Amount must be not be greater than cash';
                    echo json_encode($this->response);
                    return false;
                }

                if ($res[0]['cash_received'] > 0 && $res[0]['cash_received'] != null) {
                    update_cash_received($amount, $rider_id, "deduct");
                    $this->load->model("transaction_model");
                    $transaction_data = [
                        'transaction_type' => "transaction",
                        'user_id' => $rider_id,
                        'order_id' => "",
                        'type' => "rider_cash_collection",
                        'txn_id' => "",
                        'amount' => $amount,
                        'status' => "1",
                        'message' => $message,
                        'transaction_date' => $date,
                    ];
                    $this->transaction_model->add_transaction($transaction_data);
                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Amount Successfully Collected';
                } else {
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Cash should be greater than 0';
                }

                echo json_encode($this->response);
                return false;
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

     public function delete_rider_rating()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (print_msg(!has_permissions('delete', 'product'), PERMISSION_ERROR_MSG, 'product', false)) {
                return false;
            }

            $this->rating_model->delete_rider_rating($_GET['id']);

            $this->response['error'] = false;
            $this->response['message'] = 'Deleted Succesfully';

            print_r(json_encode($this->response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function rider_registration_request()
    {

        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
          
            $this->data['main_page'] = TABLES . 'rider-registration-request';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Rider Registration Request | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Rider Registration Request  | ' . $settings['app_name'];
            $this->data['currency'] = get_settings('currency');
            $this->load->view('admin/template', $this->data);
          
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function view_rider_requests()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            return $this->Rider_model->get_riders_list(false, $rider_requests = true);
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
