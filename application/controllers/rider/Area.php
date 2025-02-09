<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Area extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model('Area_model');
    }

    public function get_cities()
    {
        $search = (isset($_GET['search'])) ? $_GET['search'] : null;
        $cities = $this->Area_model->get_cities("c.name", "ASC", $search);
        $this->response['data'] = $cities['data'];

        print_r(json_encode($this->response));
    }
}
