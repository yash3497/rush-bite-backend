<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    // Declare properties to avoid dynamic property creation
    protected $benchmark;
    protected $data = [];
    protected $response = [];
    public $ion_auth_model;
    public $ion_auth;
    public $db;
    public $email;
    public $session;
    public $form_validation; 
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model(['address_model', 'category_model', 'cart_model', 'faq_model']);

        // Initialize properties
        $this->data['is_logged_in'] = ($this->ion_auth->logged_in()) ? 1 : 0;
        $this->data['user'] = ($this->ion_auth->logged_in()) ? $this->ion_auth->user()->row() : array();
        $this->data['settings'] = get_settings('system_settings', true);
        $this->data['web_settings'] = get_settings('web_settings', true);
        $this->response['csrfName'] = $this->security->get_csrf_token_name();
        $this->response['csrfHash'] = $this->security->get_csrf_hash();
        $this->load->library('ion_auth');
        $this->load->model('ion_auth_model');
        $this->load->library('email');
        $this->load->library('session');
        $this->load->library('form_validation');  // Loading the form validation library
    }
  

    public function index()
    {
       redirect(base_url('admin/login'));
    }

    public function error_404()
    {
        $this->data['main_page'] = 'error_404';
        $this->data['title'] = 'Product cart | ' . $this->data['web_settings']['site_title'];
        $this->data['keywords'] = 'Product cart, ' . $this->data['web_settings']['meta_keywords'];
        $this->data['description'] = 'Product cart | ' . $this->data['web_settings']['meta_description'];
        $this->load->view('front-end/' . THEME . '/template', $this->data);
    }
}
