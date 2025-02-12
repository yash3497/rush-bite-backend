<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Openstreetmap extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Setting_model');

    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = VIEW . 'openstreetmap';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'About Us | ' . $settings['app_name'];
            $this->data['meta_description'] = 'About Us | ' . $settings['app_name'];
            $this->data['about_us'] = get_settings('about_us');
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }


}
