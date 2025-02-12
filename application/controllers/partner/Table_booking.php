<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Table_booking extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model(['Table_booking_model', 'order_model']);
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $this->data['main_page'] = FORMS . 'add_floore';
            $settings = get_settings('system_settings', true);

            if (isset($_GET['edit_id'])) {
                $this->data['title'] = 'Update Floor | ' . $settings['app_name'];
                $this->data['meta_description'] = 'Update Floor | ' . $settings['app_name'];
                $this->data['fetched_data'] = fetch_details(['id' => $_GET['edit_id']], 'floore');
            } else {
                $this->data['title'] = 'Add Floor | ' . $settings['app_name'];
                $this->data['meta_description'] = 'Add Floor | ' . $settings['app_name'];
            }
            $this->load->view('partner/template', $this->data);
        } else {
            redirect('partner/login', 'refresh');
        }
    }

    public function manage_floore()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $this->data['main_page'] = TABLES . 'manage-floore';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Manage Floore | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Manage Floore  | ' . $settings['app_name'];
            $this->load->view('partner/template', $this->data);
        } else {
            redirect('partner/login', 'refresh');
        }
    }

    public function add_floore()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {

            $this->form_validation->set_rules('title', 'Title', 'trim|required|xss_clean');
            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = array(
                    'title' => form_error('title'),
                );
                print_r(json_encode($this->response));
            } else {
                if (isset($_POST['edit_floore'])) {
                    if (is_exist(['title' => $this->input->post('title', true)], 'floore', $this->input->post('edit_floore', true))) {
                        $response["error"]   = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["message"] = "This Floore Already Exist.";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                } else {
                    if (is_exist(['title' => $this->input->post('title', true)], 'floore')) {
                        $response["error"]   = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["message"] = "This Floore Already Exist.";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }
                $_POST['partner_id'] = $this->session->userdata('user_id');
                $floore_id = $this->Table_booking_model->add_floore($_POST);
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $message = (isset($_POST['edit_floore'])) ? 'Floore Updated Successfully' : 'Floore Added Successfully';
                $this->response['message'] = $message;
                print_r(json_encode($this->response));
            }
        } else {
            redirect('partner/login', 'refresh');
        }
    }

    public function floore_list()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $partner_id = $this->ion_auth->get_user_id();
            $status =  (isset($_GET['status']) && $_GET['status'] != "") ? $this->input->get('status', true) : NULL;
            return $this->Table_booking_model->get_floore_list($partner_id, $status);
        } else {
            redirect('partner/login', 'refresh');
        }
    }


    public function delete_floore()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $partner_id = $this->ion_auth->get_user_id();
            if (delete_details(['id' => $_GET['id'], "partner_id" => $partner_id], 'floore') == TRUE) {
                $this->response['error'] = false;
                $this->response['message'] = 'Deleted Succesfully';
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Something Went Wrong';
            }
            print_r(json_encode($this->response));
        } else {
            redirect('partner/login', 'refresh');
        }
    }
    //  Add Table

    public function manage_table()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $this->data['main_page'] = TABLES . 'manage-table';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Manage Tables | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Manage Tables  | ' . $settings['app_name'];
            $this->load->view('partner/template', $this->data);
        } else {
            redirect('partner/login', 'refresh');
        }
    }

    public function new_table()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $this->data['main_page'] = FORMS . 'add_table';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Add Table | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Add Table | ' . $settings['app_name'];
            $partner_id = $this->ion_auth->get_user_id();
            $this->data['floore'] = $this->db->select('id,title')
                ->where(['partner_id' => $partner_id])
                ->get('floore')->result_array();

            if (isset($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details(['id' => $_GET['edit_id']], 'tables');
            }
            $this->load->view('partner/template', $this->data);
        } else {
            redirect('partner/login', 'refresh');
        }
    }


    public function add_table()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {

            $this->form_validation->set_rules('title', 'Title', 'trim|required|xss_clean');
            $this->form_validation->set_rules('floore_id', 'Floore', 'trim|required|xss_clean');
            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = array(
                    'title' => form_error('title'),
                    'floore_id' => form_error('floore_id'),
                );
                print_r(json_encode($this->response));
            } else {
                if (isset($_POST['edit_table'])) {
                    if (is_exist(['title' => $this->input->post('title', true)], 'tables', $this->input->post('edit_table', true))) {
                        $response["error"]   = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["message"] = "This Table Already Exist.";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                } else {
                    if (is_exist(['title' => $this->input->post('title', true)], 'tables')) {
                        $response["error"]   = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["message"] = "This Table Already Exist.";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }
                $_POST['partner_id'] = $this->session->userdata('user_id');
                $table_id = $this->Table_booking_model->add_table($_POST);
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $message = (isset($_POST['edit_table'])) ? 'Table Updated Successfully' : 'Table Added Successfully';
                $this->response['message'] = $message;
                print_r(json_encode($this->response));
            }
        } else {
            redirect('partner/login', 'refresh');
        }
    }

    public function table_list()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $partner_id = $this->ion_auth->get_user_id();
            $status =  (isset($_GET['status']) && $_GET['status'] != "") ? $this->input->get('status', true) : NULL;
            return $this->Table_booking_model->get_table_list($partner_id, $status);
        } else {
            redirect('partner/login', 'refresh');
        }
    }

    public function delete_table()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $partner_id = $this->ion_auth->get_user_id();
            if (delete_details(['id' => $_GET['id'], "partner_id" => $partner_id], 'tables') == TRUE) {
                $this->response['error'] = false;
                $this->response['message'] = 'Deleted Succesfully';
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Something Went Wrong';
            }
            print_r(json_encode($this->response));
        } else {
            redirect('partner/login', 'refresh');
        }
    }

    public function dine_in_customers()
    {

        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner() && ($this->ion_auth->partner_status() == 1 || $this->ion_auth->partner_status() == 0)) {
            $this->data['main_page'] = FORMS . 'dine_in_customers';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Dine in Customers | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Dine in Customers | ' . $settings['app_name'];
            $partner_id = $this->ion_auth->get_user_id();

            $available_tables = $this->db->select('t.id as table_id,t.title as table_name,f.id as floor_id, f.title as floor_name')
                ->join('floore f ', 't.floore_id=f.id', 'left')
                ->where('t.partner_id', $partner_id)->where('t.status', '0')->get('tables t')->result_array();
            $this->data['tables'] = $available_tables;


            $this->load->view('partner/template', $this->data);
        } else {
            redirect('partner/login', 'refresh');
        }
    }


    public function get_cart()
    {
        $cart_details = $this->db->select('pv.*,p.*,dc.*')
            ->join('product_variants pv ', 'pv.id=dc.product_variant_id', 'left')
            ->join('products p ', 'pv.product_id=p.id', 'left')
            ->where('dc.table_id', $_GET['table_id'])->get('dine_in_cart dc')->result_array();

        print_r(json_encode($cart_details));
    }
}
