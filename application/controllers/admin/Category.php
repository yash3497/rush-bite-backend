<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Category extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model(['category_model']);

        if (!has_permissions('read', 'categories')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'manage-category';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Category Management | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Category Management | ' . $settings['app_name'];
            $id = $this->input->get('id', true);
            if (isset($id) && !empty($id)) {
                $this->data['base_category_url'] = base_url() . 'admin/category/category_list?id=' . $id;
            } else {
                $this->data['base_category_url']  = base_url() . 'admin/category/category_list';
            }
            $this->data['category_result'] = $this->category_model->get_categories();
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function get_categories()
    {
        $ignore_status = isset($_GET['ignore_status']) && $_GET['ignore_status'] == 1 ? 1 : '';
        $search =  (isset($_GET['search'])) ? $this->input->get('search') : null;
        $response['data'] = $this->data['category_result'] = $this->category_model->get_categories(NULL, '', '', 'row_order', 'ASC', 'true', '', $ignore_status, $search);
        echo json_encode($response);
        return;
    }

    public function create_category()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'category';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) ? 'Edit Category | ' . $settings['app_name'] : 'Add Category | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Add Category , Create Category | ' . $settings['app_name'];
            if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details(['id' => $_GET['edit_id']], 'categories');
            }
            $this->data['categories'] = $this->category_model->get_categories();
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function category_order()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = TABLES . 'category-order';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Category Order | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Category Order | ' . $settings['app_name'];
            $this->data['categories'] = $this->category_model->get_categories();
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    function delete_category()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (print_msg(!has_permissions('delete', 'categories'), PERMISSION_ERROR_MSG, 'categories')) {
                return false;
            }

            $category_products = fetch_details(['category_id' => $_GET['id']], 'products', 'id,category_id');
            if(!empty($category_products)){
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'This category cannot be deleted because it is associated with product data.';
                    print_r(json_encode($this->response));
                
            }else{
                if ($this->category_model->delete_category($_GET['id']) == TRUE) {
                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Deleted Succesfully';
                    print_r(json_encode($this->response));
                }
            }

        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function category_list()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            return $this->category_model->get_category_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }



    public function add_category()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (isset($_POST['edit_category'])) {
                if (print_msg(!has_permissions('update', 'categories'), PERMISSION_ERROR_MSG, 'categories')) {
                    return false;
                }
            } else {
                if (print_msg(!has_permissions('create', 'categories'), PERMISSION_ERROR_MSG, 'categories')) {
                    return false;
                }
            }

            $this->form_validation->set_rules('category_input_name', 'Category Name', 'trim|required|xss_clean');
            $this->form_validation->set_rules('banner', 'Banner', 'trim|xss_clean');

            if (isset($_POST['edit_category'])) {
                $this->form_validation->set_rules('category_input_image', 'Image', 'trim|xss_clean');
            } else {
                $this->form_validation->set_rules('category_input_image', 'Image', 'trim|required|xss_clean', array('required' => 'Category image is required'));
            }

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'category_input_name' => form_error('category_input_name'),
                    'category_input_image' => form_error('category_input_image')
                );
                print_r(json_encode($this->response));
            } else {
                if (!isset($_POST['edit_category'])) {
                    if (is_exist(['name' => $_POST['category_input_name']], 'categories')) {
                        $response["error"]   = true;
                        $response["message"] = "Category Already Exist! Provide another category name.";
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }

                 if (isset($_POST['edit_category'])) {
                        if (is_exist(['name' => $_POST['category_input_name']], 'categories')) {
                            $get_id = fetch_details(['name' => $_POST['category_input_name']],'categories','id');
                            if($get_id[0]['id'] !== $_POST['edit_category']){
                                $response["error"]    = true;
                                $response["message"]  = "Category Already Exist! Provide another category name.";
                                $response['csrfName'] = $this->security->get_csrf_token_name();
                                $response['csrfHash'] = $this->security->get_csrf_hash();
                                $response["data"] = array();
                                echo json_encode($response);
                                return false;                                  
                            }          
                        }
                    }

                $this->category_model->add_category($_POST);
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $message = (isset($_POST['edit_category'])) ? 'Category Updated Successfully' : 'Category Added Successfully';
                $this->response['location'] = base_url('admin/category');
                $this->response['message'] = $message;
                print_r(json_encode($this->response));
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function update_category_order()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('update', 'category_order'), PERMISSION_ERROR_MSG, 'category_order', false)) {
                return false;
            }
            $i = 0;
            $temp = array();
            foreach ($_GET['category_id'] as $row) {
                $temp[$row] = $i;
                $data = [
                    'row_order' => $i
                ];
                $data = escape_array($data);
                $this->db->where(['id' => $row])->update('categories', $data);
                $i++;
            }

            $response['error'] = false;
            $response['message'] = 'Category Order Saved !';

            print_r(json_encode($response));
        } else {
            redirect('admin/login', 'refresh');
        }
    }


    public function bulk_upload()
    {

        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'category-bulk-upload';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Bulk Upload | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Bulk Upload | ' . $settings['app_name'];

            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function top_category(){
        $this->category_model->top_category();
    }

    public function process_bulk_upload()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (print_msg(!has_permissions('create', 'product'), PERMISSION_ERROR_MSG, 'product')) {
                return false;
            }
            $this->form_validation->set_rules('bulk_upload', '', 'xss_clean');
            $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
            if (empty($_FILES['upload_file']['name'])) {
                $this->form_validation->set_rules('upload_file', 'File', 'trim|required|xss_clean', array('required' => 'Please choose file'));
            }

            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'type' => form_error('type'),
                    'upload_file' => form_error('upload_file'),                                   
                );
                print_r(json_encode($this->response));
            } else {
                $allowed_mime_type_arr = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv');
                $mime = get_mime_by_extension($_FILES['upload_file']['name']);
                if (!in_array($mime, $allowed_mime_type_arr)) {
                    $this->response['error'] = true;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Invalid file format!';
                    print_r(json_encode($this->response));
                    return false;
                }
                $csv = $_FILES['upload_file']['tmp_name'];
                $temp = 0;
                $temp1 = 0;
                $handle = fopen($csv, "r");
                $this->response['message'] = '';
                $type = $_POST['type'];
                if ($type == 'upload') {
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
                    {
                        if ($temp != 0) {
                            if (empty($row[0])) {
                                $this->response['error'] = true;
                                $this->response['message'] = 'Name is empty at row ' . $temp;
                                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                                print_r(json_encode($this->response));
                                return false;
                            }
                            if (!empty($row[0])) {
                                if (is_exist(['name' => $row[0]], 'categories')) {
                                    $response["error"]   = true;
                                    $response["message"] = "Category Already Exist! Provide another category name at row." . $temp;
                                    $response['csrfName'] = $this->security->get_csrf_token_name();
                                    $response['csrfHash'] = $this->security->get_csrf_hash();
                                    $response["data"] = array();
                                    echo json_encode($response);
                                    return false;
                                }
                            }
                            if (empty($row[1])) {
                                $this->response['error'] = true;
                                $this->response['message'] = 'Image is empty at row ' . $temp;
                                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                                print_r(json_encode($this->response));
                                return false;
                            }
                            if (empty($row[2])) {
                                $this->response['error'] = true;
                                $this->response['message'] = 'Banner image is empty at row ' . $temp;
                                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                                print_r(json_encode($this->response));
                                return false;
                            }
                        }
                        $temp++;
                    }

                    fclose($handle);
                    $handle = fopen($csv, "r");
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
                    {
                        if ($temp1 != 0) {
                            $data['name'] = $row[0];
                            $data['slug'] = create_unique_slug($row[0], 'categories');
                            $data['image'] = $row[1];
                            $data['banner'] = $row[2];
                            $data['parent_id'] = 0;
                            $data['status'] = 1;
                            $this->db->insert('categories', $data);
                        }
                        $temp1++;
                    }
                    fclose($handle);
                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Categories uploaded successfully!';
                    print_r(json_encode($this->response));
                    return false;
                } else { // bulk_update
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row vales
                    {
                        if ($temp != 0) {
                            if (empty($row[0])) {
                                $this->response['error'] = true;
                                $this->response['message'] = 'Category id is empty at row ' . $temp;
                                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                                print_r(json_encode($this->response));
                                return false;
                            }
                            if (!empty($row[0])) {
                                if (!is_exist(['id' => $row[0]], 'categories')) {
                                    $response["error"]   = true;
                                    $response["message"] = "Category is not exist Provide another category id at row." . $temp;
                                    $response['csrfName'] = $this->security->get_csrf_token_name();
                                    $response['csrfHash'] = $this->security->get_csrf_hash();
                                    $response["data"] = array();
                                    print_r(json_encode($response));
                                    return false;
                                }
                            }
                            if (empty($row[1])) {
                                $this->response['error'] = true;
                                $this->response['message'] = 'Name is empty at row ' . $temp;
                                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                                print_r(json_encode($this->response));
                                return false;
                            }
                           
                            if (empty($row[2])) {
                                $this->response['error'] = true;
                                $this->response['message'] = 'Image is empty at row ' . $temp;
                                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                                print_r(json_encode($this->response));
                                return false;
                            }
                            if (empty($row[3])) {
                                $this->response['error'] = true;
                                $this->response['message'] = 'Banner image is empty at row ' . $temp;
                                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                                print_r(json_encode($this->response));
                                return false;
                            }
                        }
                        $temp++;
                    }

                    fclose($handle);
                    $handle = fopen($csv, "r");
                    while (($row = fgetcsv($handle, 10000, ",")) != FALSE) //get row values
                    {
                        if ($temp1 != 0) {
                            $category_id = $row[0];
                            $categories = fetch_details(['id' => $category_id], 'categories', '*');
                            if (isset($categories[0]) && !empty($categories[0])) {
                                if (!empty($row[1])) {
                                    $data['name'] = $row[1];
                                    $data['slug'] = create_unique_slug($row[1], 'categories');
                                } else {
                                    $data['name'] = $categories[0]['name'];
                                }
                                if (!empty($row[2])) {
                                    $data['image'] = $row[2];
                                } else {
                                    $data['image'] = $categories[0]['image'];
                                }
                                if (!empty($row[3])) {
                                    $data['banner'] = $row[3];
                                } else {
                                    $data['banner'] = $categories[0]['banner'];
                                }
                                $this->db->where('id', $row[0])->update('categories', $data);
                            }
                        }
                        $temp1++;
                    }
                    fclose($handle);
                    $this->response['error'] = false;
                    $this->response['csrfName'] = $this->security->get_csrf_token_name();
                    $this->response['csrfHash'] = $this->security->get_csrf_hash();
                    $this->response['message'] = 'Categories updated successfully!';
                    print_r(json_encode($this->response));
                    return false;
                }
            }
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
