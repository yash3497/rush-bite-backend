<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron_job extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation', 'upload']);
        $this->load->helper(['url', 'language', 'file']);
        $this->load->model(['Partner_model', 'Order_model', 'Cart_model']);
    }

    public function settle_partner_commission()
    {
        return $this->Partner_model->settle_partner_commission();
    }
    public function settle_admin_commission()
    {
        return $this->Partner_model->settle_admin_commission();
    }

    public function draft_order_settel()
    {
        return $this->Order_model->delete_draft_orders();
    }

    public function cart_notification()
    {
        return $this->Cart_model->cart_notification();
    }
}
