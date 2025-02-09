<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'sms_helper', 'function_helper']);
        $this->load->model('Rider_model');
        $this->lang->load('auth');
    }

    public function index()
    {
        if (!$this->ion_auth->logged_in() && !$this->ion_auth->is_rider()) {
            $this->data['main_page'] = FORMS . 'login';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Rider Login Panel | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Rider Login Panel | ' . $settings['app_name'];
            $this->data['logo'] = get_settings('logo');
            $this->data['app_name'] = $settings['app_name'];
            $identity = $this->config->item('identity', 'ion_auth');
            if (empty($identity)) {
                $identity_column = 'text';
            } else {
                $identity_column = $identity;
            }
            $this->data['identity_column'] = $identity_column;
            $this->load->view('rider/login', $this->data);
        } else if ($this->ion_auth->logged_in() && $this->ion_auth->is_rider()) {
            redirect('rider/home', 'refresh');
        } else if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            redirect('admin/home', 'refresh');
        }
    }

    public function sign_up()
    {
        $this->data['main_page'] = FORMS . 'rider-registration';
        $settings = get_settings('system_settings', true);
        $this->data['title'] = 'Sign Up Rider | ' . $settings['app_name'];
        $this->data['meta_description'] = 'Sign Up Rider | ' . $settings['app_name'];
        $this->data['logo'] = get_settings('logo');
        if (isset($_SESSION['to_be_rider_name']) && !empty($_SESSION['to_be_rider_name']) && isset($_SESSION['to_be_rider_mobile']) && !empty($_SESSION['to_be_rider_mobile']) && isset($_SESSION['to_be_rider_id']) && !empty($_SESSION['to_be_rider_id'])) {
            $this->data['title'] = 'Rider Registration | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Rider Registration | ' . $settings['app_name'];
            $this->data['user_data'] = $_SESSION;
        } else {
            $this->data['user_data'] = [];
        }
        $this->load->view('rider/login', $this->data);
    }

    public function create_rider()
    {

        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean');
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]');
        $this->form_validation->set_rules('profile', 'Rider Profile', 'trim|xss_clean');
        $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');
        $this->form_validation->set_rules('serviceable_city[]', 'Serviceable city', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'trim|required|xss_clean');

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
                'mobile' => form_error('mobile'),
            );
            print_r(json_encode($this->response));
        } else {
            // profile image
            $serviceable_city = isset($_POST['serviceable_city']) && !empty($_POST['serviceable_city']) ? implode(",", $_POST['serviceable_city']) : "";


            $temp_array_logo = $profile_doc = array();
            $logo_files = $_FILES;
            $profile_error = "";
            $config = [
                'upload_path' =>  FCPATH . USER_IMG_PATH,
                'allowed_types' => 'jpg|png|jpeg|gif',
                'max_size' => 8000,
            ];
            if (isset($logo_files['profile']) && !empty($logo_files['profile']['name']) && isset($logo_files['profile']['name'])) {
                $other_img = $this->upload;
                $other_img->initialize($config);

                if (isset($_POST['edit_rider']) && !empty($_POST['edit_rider']) && isset($_POST['profile']) && !empty($_POST['profile'])) {
                    $old_logo = explode('/', $this->input->post('profile', true));
                    delete_images(USER_IMG_PATH, $old_logo[2]);
                }

                if (!empty($logo_files['profile']['name'])) {

                    $_FILES['temp_image']['name'] = $logo_files['profile']['name'];
                    $_FILES['temp_image']['type'] = $logo_files['profile']['type'];
                    $_FILES['temp_image']['tmp_name'] = $logo_files['profile']['tmp_name'];
                    $_FILES['temp_image']['error'] = $logo_files['profile']['error'];
                    $_FILES['temp_image']['size'] = $logo_files['profile']['size'];
                    if (!$other_img->do_upload('temp_image')) {
                        $profile_error = 'Images :' . $profile_error . ' ' . $other_img->display_errors();
                    } else {
                        $temp_array_logo = $other_img->data();
                        resize_review_images($temp_array_logo, FCPATH . USER_IMG_PATH);
                        $profile_doc  = USER_IMG_PATH . $temp_array_logo['file_name'];
                    }
                } else {
                    $_FILES['temp_image']['name'] = $logo_files['profile']['name'];
                    $_FILES['temp_image']['type'] = $logo_files['profile']['type'];
                    $_FILES['temp_image']['tmp_name'] = $logo_files['profile']['tmp_name'];
                    $_FILES['temp_image']['error'] = $logo_files['profile']['error'];
                    $_FILES['temp_image']['size'] = $logo_files['profile']['size'];
                    if (!$other_img->do_upload('temp_image')) {
                        $profile_error = $other_img->display_errors();
                    }
                }
                //Deleting Uploaded Images if any overall error occured
            }

            if ($profile_error != NULL) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] =  $profile_error;
                print_r(json_encode($this->response));
                return;
            }
            /* process commission params */

            if (isset($_POST['edit_rider'])) {

                if (!edit_unique($this->input->post('email', true), 'users.email.' . $this->input->post('edit_rider', true) . '') || !edit_unique($this->input->post('mobile', true), 'users.mobile.' . $this->input->post('edit_rider', true) . '')) {
                    $response["error"]   = true;
                    $response["message"] = "Email or mobile already exists !";
                    $response['csrfName'] = $this->security->get_csrf_token_name();
                    $response['csrfHash'] = $this->security->get_csrf_hash();
                    $response["data"] = array();
                    echo json_encode($response);
                    return false;
                }
                $_POST['active'] = $this->input->post("active", true);
                $_POST['rider_cancel_order'] = isset($_POST['rider_cancel_order']) && $_POST['rider_cancel_order'] == 'on' ? 1 : 0;
                $image = USER_IMG_PATH . $_FILES['profile']['name'];

                $this->Rider_model->update_rider($_POST, $image);
            } else {
                if (!$this->form_validation->is_unique($_POST['mobile'], 'users.mobile') || !$this->form_validation->is_unique($_POST['email'], 'users.email')) {
                    $response["error"]   = true;
                    $response["message"] = "Email or mobile already exists !";
                    $response['csrfName'] = $this->security->get_csrf_token_name();
                    $response['csrfHash'] = $this->security->get_csrf_hash();
                    $response["data"] = array();
                    echo json_encode($response);
                    return false;
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
                        'image' => (!empty($profile_doc)) ? $profile_doc : $this->input->post('profile', true),
                        'rider_cancel_order' => isset($_POST['rider_cancel_order']) && $_POST['rider_cancel_order'] == 'on' ? 1 : 0,
                    ];
                    $this->ion_auth->register($identity, $password, $email, $additional_data, ['3']);
                    update_details(['active' => 2], [$identity_column => $identity], 'users');
                } else {
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
            $this->response['location'] = base_url('rider/login');
            $this->response['message'] = 'Rider Registerd Successfully , Please wait for approval from admin!';
            print_r(json_encode($this->response));
        }
    }

    public function verify_account()
    {
        $identity_column = $this->config->item('identity', 'ion_auth');
        $identity = $this->input->post('identity', true);
        $this->form_validation->set_rules('identity', 'Mobile', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        if ($this->form_validation->run()) {
            $res = $this->db->select('id,mobile,username')->where($identity_column, $identity)->get('users')->result_array();
            if (!empty($res)) {
                if ($this->ion_auth_model->in_group('rider', $res[0]['id'])) {
                    $response['error'] = false;
                    $response['csrfName'] = $this->security->get_csrf_token_name();
                    $response['csrfHash'] = $this->security->get_csrf_hash();
                    $response['message'] = "This user is already Rider please do login";
                    $response['data'] = array();
                    $response['redirect'] = 1;
                    echo json_encode($response);
                } else {
                    // already user
                    $this->session->set_flashdata('to_be_rider_name', $res[0]['username']);
                    $this->session->set_flashdata('to_be_rider_mobile', $res[0]['mobile']);
                    $this->session->set_flashdata('to_be_rider_id', $res[0]['id']);
                    $response['error'] = false;
                    $response['csrfName'] = $this->security->get_csrf_token_name();
                    $response['csrfHash'] = $this->security->get_csrf_hash();
                    $response['message'] = "Already user";
                    $response['data'] = array();
                    $response['redirect'] = 3;
                    echo json_encode($response);
                }
            } else {
                // no user
                $response['error'] = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = "Redirect to new registration";
                $response['data'] = array();
                $response['redirect'] = 2;
                echo json_encode($response);
            }
        } else {
            $response['error'] = true;
            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            $response['message'] = validation_errors();
            $response['data'] = array();
            $response['redirect'] = 0;
            echo json_encode($response);
        }
    }


    public function update_user()
    {

        $identity_column = $this->config->item('identity', 'ion_auth');
        $identity = $this->session->userdata('identity');
        $user = $this->ion_auth->user()->row();
        if ($identity_column == 'email') {
            $this->form_validation->set_rules('email', 'Email', 'required|xss_clean|trim|valid_email|edit_unique[users.email.' . $user->id . ']');
        } else {
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|xss_clean|trim|numeric|edit_unique[users.mobile.' . $user->id . ']');
        }
        $this->form_validation->set_rules('username', 'Username', 'required|xss_clean|trim');

        if (!empty($_POST['old']) || !empty($_POST['new']) || !empty($_POST['new_confirm'])) {
            $this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
            $this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', $this->lang->line('change_password_validation_new_password_confirm_label'), 'required');
        }


        $tables = $this->config->item('tables', 'ion_auth');
        if (!$this->form_validation->run()) {
            if (validation_errors()) {
                $response['error'] = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = array(
                    'email' => form_error('email'),
                    'mobile' => form_error('mobile'),
                    'username' => form_error('username'),
                    'old' => form_error('old'),
                    'new' => form_error('new'),
                    'new_confirm' => form_error('new_confirm'),
                );
                echo json_encode($response);
                return false;
                exit();
            }
            if ($this->session->flashdata('message')) {
                $response['error'] = false;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = $this->session->flashdata('message');
                echo json_encode($response);
                return false;
                exit();
            }
        } else {

            if (!empty($_POST['old']) || !empty($_POST['new']) || !empty($_POST['new_confirm'])) {
                if (!$this->ion_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'))) {
                    $response['error'] = true;
                    $response['csrfName'] = $this->security->get_csrf_token_name();
                    $response['csrfHash'] = $this->security->get_csrf_hash();
                    $response['message'] = $this->ion_auth->errors();
                    echo json_encode($response);
                    return;
                    exit();
                }
            }
            $set = ['username' => $this->input->post('username'), 'email' => $this->input->post('email')];
            $set = escape_array($set);
            $this->db->set($set)->where($identity_column, $identity)->update($tables['login_users']);
            $response['error'] = false;
            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            $response['message'] = 'Profile Update Succesfully';
            echo json_encode($response);
            return;
        }
    }
    public function auth()
    {
        $identity_column = $this->config->item('identity', 'ion_auth');
        $identity = $this->input->post('identity', true);
        $this->form_validation->set_rules('identity', 'Email', 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $res = $this->db->select('id')->where($identity_column, $identity)->get('users')->result_array();
        if ($this->form_validation->run()) {
            if (!empty($res)) {
                if ($this->ion_auth_model->in_group('rider', $res[0]['id'])) {
                    $remember = (bool) $this->input->post('remember');
                    if ($this->ion_auth->login($this->input->post('identity', true), $this->input->post('password', true), $remember)) {
                        //if the login is successful
                        $response['error'] = false;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response['message'] = $this->ion_auth->messages();
                        echo json_encode($response);
                    } else {
                        // if the login was un-successful
                        $response['error'] = true;
                        $response['csrfName'] = $this->security->get_csrf_token_name();
                        $response['csrfHash'] = $this->security->get_csrf_hash();
                        $response['message'] = $this->ion_auth->errors();
                        echo json_encode($response);
                    }
                } else {
                    $response['error'] = true;
                    $response['csrfName'] = $this->security->get_csrf_token_name();
                    $response['csrfHash'] = $this->security->get_csrf_hash();
                    $response['message'] = ucfirst($identity_column) . ' field is not correct';
                    echo json_encode($response);
                }
            } else {
                $response['error'] = true;
                $response['csrfName'] = $this->security->get_csrf_token_name();
                $response['csrfHash'] = $this->security->get_csrf_hash();
                $response['message'] = '' . ucfirst($identity_column) . ' field is not correct';
                echo json_encode($response);
            }
        } else {
            $response['error'] = true;
            $response['csrfName'] = $this->security->get_csrf_token_name();
            $response['csrfHash'] = $this->security->get_csrf_hash();
            $response['message'] = validation_errors();
            echo json_encode($response);
        }
    }

    public function forgot_password()
    {
        $this->data['main_page'] = FORMS . 'forgot-password';
        $this->data['title'] = 'Forget Password | Rider Panel';
        $this->data['meta_description'] = 'Ekart';
        $this->data['logo'] = get_settings('logo');
        $this->load->view('rider/login', $this->data);
    }
}
