<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notification_settings extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model(['Setting_model', 'notification_model', 'category_model']);
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('read', 'notification_setting')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }
            $this->data['main_page'] = FORMS . 'notification-settings';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Update Notification Settings | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Update Notification Settings  | ' . $settings['app_name'];
            $this->data['firebase_project_id'] = get_settings('firebase_project_id');
            $this->data['vap_id_key'] = get_settings('vap_id_key');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function custome_notification()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = FORMS . 'custome-notification-settings';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Custome Notification Management | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Custome Notification Management | ' . $settings['app_name'];
            $this->data['settings'] = get_settings('custome_notifications', true);
           


            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function update_custome_notification(){
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
        
            if (print_msg(!has_permissions('update', 'notification_setting'), PERMISSION_ERROR_MSG, 'notification_setting')) {
                return false;
            }
            $_POST['temp'] = '1';
            $this->form_validation->set_rules('temp', '', 'trim|required|xss_clean');
            
            if (isset($_POST['order_place_mode'])) {
                $this->form_validation->set_rules('order_place_title', 'Order Place Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('order_place_message', 'Order Place Message', 'trim|required|xss_clean');
            }
            if (isset($_POST['oredr_pending_mode'])) {
                $this->form_validation->set_rules('oredr_pending_title', 'Order Pending Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('oredr_pending_message', 'Order Panding Message', 'trim|required|xss_clean');
            }
            if (isset($_POST['order_confirm_mode'])) {
                $this->form_validation->set_rules('oredr_confirm_title', 'Order Confirm Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('oredr_confirm_message', 'Order Confirm Message', 'trim|required|xss_clean');
            }
            if (isset($_POST['order_preparing_mode'])) {
                $this->form_validation->set_rules('oredr_preparing_title', 'Order Preparing Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('oredr_preparing_message', 'Order Preparing Message', 'trim|required|xss_clean');
            }
            if (isset($_POST['order_out_for_delivery_mode'])) {
                $this->form_validation->set_rules('order_out_for_delivery_title', 'Order Out for delivery Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('order_out_for_delivery_message', 'Order Out for delivery Message', 'trim|required|xss_clean');
            }
            if (isset($_POST['oredr_cancel_mode'])) {
                $this->form_validation->set_rules('oredr_cancel_title', 'Order Cancel Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('oredr_cancel_message', 'Order Cancel Message', 'trim|required|xss_clean');
            }
            if (isset($_POST['oredr_deliverd_mode'])) {
                $this->form_validation->set_rules('oredr_deliverd_title', 'Order Delivery Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('oredr_deliverd_message', 'Order Delivery Message', 'trim|required|xss_clean');
            }
            if (isset($_POST['rider_assign_order_mode'])) {
                $this->form_validation->set_rules('rider_assign_order_title', 'Rider Assign Order Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('rider_assign_order_message', 'Rieder Assign Order Message', 'trim|required|xss_clean');
            }
            if (isset($_POST['rider_pening_order_mode'])) {
                $this->form_validation->set_rules('rider_pening_order_title', 'Rider Pending Oredr Title', 'trim|required|xss_clean');
                $this->form_validation->set_rules('rider_pening_order_message', 'Rider Panding Order Message', 'trim|required|xss_clean');
            }


            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'order_place_title' => form_error('order_place_title'),
                    'order_place_message' => form_error('order_place_message'),
                    'oredr_pending_title' => form_error('oredr_pending_title'),
                    'oredr_pending_message' => form_error('oredr_pending_message'),
                    'oredr_confirm_title' => form_error('oredr_confirm_title'),
                    'oredr_confirm_message' => form_error('oredr_confirm_message'),
                    'oredr_preparing_title' => form_error('oredr_preparing_title'),
                    'oredr_preparing_message' => form_error('oredr_preparing_message'),
                    'order_out_for_delivery_title' => form_error('order_out_for_delivery_title'),
                    'order_out_for_delivery_message' => form_error('order_out_for_delivery_message'),
                    'oredr_cancel_title' => form_error('oredr_cancel_title'),
                    'oredr_cancel_message' => form_error('oredr_cancel_message'),
                    'oredr_deliverd_title' => form_error('oredr_deliverd_title'),
                    'oredr_deliverd_message' => form_error('oredr_deliverd_message'),
                    'rider_assign_order_title' => form_error('rider_assign_order_title'),
                    'rider_assign_order_message' => form_error('rider_assign_order_message'),
                    'rider_pening_order_title' => form_error('rider_pening_order_title'),
                    'rider_pening_order_message' => form_error('rider_pening_order_message'),
                );
                print_r(json_encode($this->response));
            } else {
                $this->Setting_model->custome_notifications($_POST);
                $this->response['error'] = false;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] = 'Custome Notifications Updated Successfully';
                print_r(json_encode($this->response));
            }
        }else{

            redirect('admin/login', 'refresh');
        }
    }

    public function manage_notifications()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('read', 'send_notification')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }

            $this->data['main_page'] = TABLES . 'manage-notifications';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Send Notification | ' . $settings['app_name'];
            $this->data['meta_description'] = ' Send Notification | ' . $settings['app_name'];
            $this->data['categories'] = $this->category_model->get_categories();
            if (isset($_GET['edit_id'])) {
                $this->data['fetched_data'] = fetch_details(['id' => $_GET['edit_id']], 'notifications');
            }
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function get_notification_list()
    {
        if ($this->ion_auth->logged_in()) {
            return $this->notification_model->get_notification_list();
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    public function delete_notification()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (print_msg(!has_permissions('delete', 'send_notification'), PERMISSION_ERROR_MSG, 'send_notification', false)) {
                return true;
            }

            if (delete_details(['id' => $_GET['id']], 'notifications')) {
                $response['error'] = false;
                $response['message'] = 'Deleted Succesfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Something Went Wrong';
            }
            echo json_encode($response);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

      public function update_notification_settings()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            if (!has_permissions('read', 'notification_setting')) {
                $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
                redirect('admin/home', 'refresh');
            }
            
            if (print_msg(!has_permissions('update', 'notification_setting'), PERMISSION_ERROR_MSG, 'notification_setting')) {
                return false;
            }

            $this->form_validation->set_rules('vap_id_key', 'Vap Id Key', 'trim|xss_clean');
            $this->form_validation->set_rules('firebase_project_id', 'Firebase Project Id', 'trim|required|xss_clean');

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['messages'] = array(
                    'firebase_project_id' => form_error('firebase_project_id'),
                    'vap_id_key' => form_error('vap_id_key'),
                   
                );
                print_r(json_encode($this->response));
            } else {

                if (isset($_FILES['service_account_file'])) {
               
                    // Check if file was uploaded without errors
                    if ($_FILES['service_account_file']['error'] === UPLOAD_ERR_OK) {
                        // Get file details
                        $fileTmpPath = $_FILES['service_account_file']['tmp_name'];
                        $fileName = $_FILES['service_account_file']['name'];
                        $fileSize = $_FILES['service_account_file']['size'];
                        $fileType = $_FILES['service_account_file']['type'];
                        $fileNameCmps = explode(".", $fileName);
                        $fileExtension = strtolower(end($fileNameCmps));
                
                        // Check if the file has a JSON extension
                        if ($fileExtension === 'json') {
                            // Move the uploaded file to a directory on the server
                            $uploadFileDir = SERVICE_JSON_FILE;
                            $dest_path = $uploadFileDir . $fileName;
                
                            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                                $this->response['message'] =  "File is successfully uploaded.";
                
                                // Read and process the JSON file
                                $jsonData = file_get_contents($dest_path);
                                $data = json_decode($jsonData, true);
                
                                if (json_last_error() !== JSON_ERROR_NONE) {
                                    $this->response['message'] = "Error decoding JSON file.";
                                }
                            } else {
                                $this->response['message'] =  "Error moving the uploaded file.";
                            }
                        } else {
                            $this->response['message'] =  "Uploaded file is not a valid JSON file.";
                        }
                    } else {
                        $this->response['message'] =  "Error during file upload: " . $_FILES['service_account_file']['error'];
                    }
                } else {
                    $this->response['message'] =  "No file uploaded.";
                }
                
                $this->Setting_model->update_vap_id_key($_POST);
                $this->Setting_model->update_firebase_project_id($_POST);
                $this->Setting_model->update_service_account_file($_FILES['service_account_file']['name']);
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



    public function send_notifications()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {

            if (print_msg(!has_permissions('create', 'send_notification'), PERMISSION_ERROR_MSG, 'send_notification')) {
                return false;
            }
            $is_image_included = (isset($_POST['image_checkbox']) && $_POST['image_checkbox'] == 'on') ? TRUE : FALSE;
            if ($is_image_included) {
                $this->form_validation->set_rules('image', 'Image', 'trim|required|xss_clean', array('required' => 'Image is required'));
            }
            $this->form_validation->set_rules('title', 'Title', 'trim|required|xss_clean');
            $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');

            if (isset($_POST['type']) && $_POST['type'] == 'categories') {
                $this->form_validation->set_rules('category_id', 'Category', 'trim|required|xss_clean');
            }

            $this->form_validation->set_rules('message', 'Message', 'trim|required|xss_clean');

            if (!$this->form_validation->run()) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                 $this->response['messages'] = array(
                    'image' => form_error('image'),
                    'title' => form_error('title'),
                    'type' => form_error('type'),
                    'category_id' => form_error('category_id'),
                    'message' => form_error('message'),
                   
                );
                print_r(json_encode($this->response));
                return;
            }

            //creating a new push
            $firebase_project_id = get_settings('firebase_project_id');
            $service_account_file = get_settings('service_account_file');

            $data = $this->input->post(null, true);
            $title = $this->input->post('title', true);
            $type = $this->input->post('type', true);
            if (isset($_POST['type']) && $_POST['type'] == 'categories') {
                $type_id = $this->input->post('category_id', true);
            }  else {
                $type_id = '';
            }
            $message = $this->input->post('message', true);
            $users = 'all';
            $this->ion_auth->select(["fcm_id", "web_fcm_id","platform"]);
            $results = $this->ion_auth->users('members')->result_array();
            $result = array();
            for ($i = 0; $i <= count($results); $i++) {
                if (isset($results[$i]['fcm_id']) && !empty($results[$i]['fcm_id']) && ($results[$i]['fcm_id'] != 'NULL')) {
                    $res = array_merge($result, $results);
                }
            }

            if (empty($res)) {
                $this->response['notification'] = [];
                $this->response['data'] = [];
                $this->response['error'] = true;
                $this->response['message'] = 'There is no users to send notification.';
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                echo json_encode($this->response);
                return;
            }

            $groupedByPlatform = [];
            foreach ($res as $item) {
                $platform = $item['platform'];
                $groupedByPlatform[$platform][] = $item['fcm_id'];
                $groupedByPlatform['web'][] = $item['web_fcm_id'];
            }

            // Step 2: Chunk each platform group into arrays of 1000
            $fcm_ids = [];
            foreach ($groupedByPlatform as $platform => $fcmIds) {
                $fcm_ids[$platform] = array_chunk($fcmIds, 1000);
            }
            // Output the result to check
            $registrationIDs = $fcm_ids;

            if ($is_image_included) {
                $notification_image_name =  $_POST['image'];
                $data['image'] = $_POST['image'];
                $this->notification_model->add_notification($data);
            } else {
                $this->notification_model->add_notification($data);
            }
            //first check if the push has an image with it
            if ($is_image_included) {
                $fcmMsg = array(
                    'title' => (isset($title) && !empty($title) ? (string)$title : ''),
                    'body' => (isset($message) && !empty($message) ? (string)$message : ''),
                    'type' => (isset($type) && !empty($type) ? (string)$type : ''),
                    'type_id' => (isset($type_ids) && !empty($type_ids) ? (string)$type_ids : ''),
                    'image' => base_url()  . $notification_image_name,
                );
            } else {
                //if the push don't have an image give null in place of image
                $fcmMsg = array(
                    'title' => (isset($title) && !empty($title) ? (string)$title : ''),
                    'body' => (isset($message) && !empty($message) ? (string)$message : ''),
                    'image' => 'null',
                    'type' => (isset($type) && !empty($type) ? (string)$type : ''),
                    'type_id' => (isset($type_ids) && !empty($type_ids) ? (string)$type_ids : ''),
                );
            }

            if (isset($firebase_project_id) && isset($service_account_file) && !empty($firebase_project_id) && !empty($service_account_file)) {
                $fcmFields = send_notification('', $registrationIDs, $fcmMsg);
            }

            $this->response['error'] = false;
            $this->response['message'] = 'Notification Sended Successfully';
            $this->response['csrfName'] = $this->security->get_csrf_token_name();
            $this->response['csrfHash'] = $this->security->get_csrf_hash();
            echo json_encode($this->response);
            return;
        } else {
            redirect('admin/login', 'refresh');
        }
    }
}
