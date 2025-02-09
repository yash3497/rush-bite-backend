<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_inventory extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'language', 'timezone_helper']);
        $this->load->model(['Sales_inventory_model', 'Order_model', 'Product_model']);
        $this->session->set_flashdata('authorize_flag', "");
    }

    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner()) {
            $this->data['main_page'] = TABLES . 'sales-inventory';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Sales Inventory Report Management |' . $settings['app_name'];
            $this->data['meta_description'] = 'eShop - Multivendor | Sales Inventory Report Management';
            $this->load->view('partner/template', $this->data);
        } else {
            redirect('partner/login', 'refresh');
        }
    }

    public function get_partner_sales_inventory_list()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_partner()) {
            return $this->Sales_inventory_model->get_partner_sales_inventory_list();
        } else {
            redirect('partner/login', 'refresh');
        }
    }
}
