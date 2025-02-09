<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_request extends CI_Controller {


	public function __construct(){
		parent::__construct();
		$this->load->database();
		$this->load->library(['ion_auth', 'form_validation','upload']);
		$this->load->helper(['url', 'language','file']);		
		$this->load->model('payment_request_model');		

        if (!has_permissions('read', 'payment_request')) {
            $this->session->set_flashdata('authorize_flag',PERMISSION_ERROR_MSG);
            redirect('admin/home','refresh');
        }

	}

	public function index(){
		if($this->ion_auth->logged_in() && $this->ion_auth->is_admin())
		{
			$this->data['main_page'] = TABLES.'payment-request';
			$settings=get_settings('system_settings',true);
			$this->data['title'] = 'Payment Request | '.$settings['app_name'];
			$this->data['meta_description'] = ' Payment Request  | '.$settings['app_name'];
			$this->load->view('admin/template',$this->data);
		}
		else{
			redirect('admin/login','refresh');
		}
    }

    public function update_payment_request(){
        if($this->ion_auth->logged_in() && $this->ion_auth->is_admin())
		{ 
            if ( print_msg(!has_permissions('update', 'payment_request'),PERMISSION_ERROR_MSG,'payment_request')) {
               return false;
            }
			$this->form_validation->set_rules('payment_request_id', 'id', 'trim|required|numeric|xss_clean');
			$this->form_validation->set_rules('status', 'Status', 'trim|required|numeric|xss_clean');
			$this->form_validation->set_rules('update_remarks', 'Remarks', 'trim|xss_clean');
			$this->form_validation->set_rules('requested_amount', 'Requested Amount', 'trim|xss_clean');
			
			 if(!$this->form_validation->run()){

	        	$this->response['error'] = true;				
				$this->response['csrfName'] = $this->security->get_csrf_token_name();
				$this->response['csrfHash'] = $this->security->get_csrf_hash();
				$this->response['messages'] = array(
                    'payment_request_id' => form_error('payment_request_id'),
                    'status' => form_error('status'),
                    'update_remarks' => form_error('update_remarks'),
                    'requested_amount' => form_error('requested_amount'),
                   
                );
				print_r(json_encode($this->response));	
	        } else {
				
				$payment_request_detail = fetch_details(['id' => $_POST['payment_request_id']], 'payment_requests');

				if ($payment_request_detail[0]['status'] == 0) {

					$this->payment_request_model->update_payment_request($_POST);
					$this->response['error'] = false;
					$this->response['csrfName'] = $this->security->get_csrf_token_name();
					$this->response['csrfHash'] = $this->security->get_csrf_hash();
					$this->response['message'] = 'Payment request updated successfully';
					print_r(json_encode($this->response));
				} elseif ($payment_request_detail[0]['status'] == 1 && $_POST['status'] == 2) {

					$this->response['error'] = true;
					$this->response['csrfName'] = $this->security->get_csrf_token_name();
					$this->response['csrfHash'] = $this->security->get_csrf_hash();
					$this->response['message'] = 'You cant reject the approved payment requests';
					print_r(json_encode($this->response));
				} elseif ($payment_request_detail[0]['status'] == 2 && $_POST['status'] == 1) {

					$this->response['error'] = true;
					$this->response['csrfName'] = $this->security->get_csrf_token_name();
					$this->response['csrfHash'] = $this->security->get_csrf_hash();
					$this->response['message'] = 'You cant approve the rejected payment requests';
					print_r(json_encode($this->response));
				} elseif ($payment_request_detail[0]['status'] == $_POST['status']) {

					$this->response['error'] = true;
					$this->response['csrfName'] = $this->security->get_csrf_token_name();
					$this->response['csrfHash'] = $this->security->get_csrf_hash();
					$this->response['message'] = $_POST['status'] == 1 ? 'The payment request has already been approved!' : 'The payment request has already been rejected!';
					print_r(json_encode($this->response));
				}
				else{
					$this->response['error'] = true;
					$this->response['csrfName'] = $this->security->get_csrf_token_name();
					$this->response['csrfHash'] = $this->security->get_csrf_hash();
					$this->response['message'] = 'Something went wrong!!';
					print_r(json_encode($this->response));
				}	
	        }
		}
		else{
			redirect('admin/login','refresh');
		}
    }


    public function view_payment_request_list(){
		if($this->ion_auth->logged_in() && $this->ion_auth->is_admin())
		{			
			return $this->payment_request_model->get_payment_request_list();
		} else {
			redirect('admin/login','refresh');
		}		
	}
}
?>