<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Home_model extends CI_Model
{

    public function count_new_orders()
    {
        $res = $this->db->select('count(id) as counter');
        $user_id = $this->session->userdata('user_id');
        if ($this->ion_auth->is_rider()) {
            $this->db->where('o.rider_id', $user_id);
        }
        $res = $this->db->get('`orders` o')->result_array();
        return $res[0]['counter'];
    }

    public function count_orders_by_status($status)
    {
        $res = $this->db->select('count(id) as counter');
        $this->db->where('active_status', $status);
        $res = $this->db->get('`orders` o')->result_array();
        return $res[0]['counter'];
    }

    public function count_new_users()
    {
        $res = $this->db->select('count(u.id) as counter')->join('users_groups ug', ' ug.`user_id` = u.`id` ')
            ->where('ug.group_id=2')
            ->get('`users u`')->result_array();
        return $res[0]['counter'];
    }

    public function count_riders()
    {
        $res = $this->db->select('count(u.id) as counter')->where('ug.group_id', '3')->join('users_groups ug', 'ug.user_id=u.id')
            ->get('`users` u')->result_array();
        return $res[0]['counter'];
    }
    public function count_partners()
    {
        $res = $this->db->select('count(u.id) as counter')->where('ug.group_id', '4')->join('users_groups ug', 'ug.user_id=u.id')
            ->get('`users` u')->result_array();
        return $res[0]['counter'];
    }

    public function count_products($partner_id = "")
    {
        $res = $this->db->select('count(id) as counter ');
        if (!empty($partner_id) && $partner_id != '') {
            $res->where('partner_id=' . $partner_id);
        }
        $count = $res->get('`products`')->result_array();
        return $count[0]['counter'];
    }
    public function count_tags($partner_id = "")
    {
        $res = $this->db->select('count(id) as counter ');
        if (!empty($partner_id) && $partner_id != '') {
            $res->where('partner_id=' . $partner_id);
        }
        $count = $res->get('`partner_tags`')->result_array();
        return $count[0]['counter'];
    }



    public function count_products_stock_low_status($partner_id = "")
    {
        $settings = get_settings('system_settings', true);
        $low_stock_limit = isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : 5;

        // Start building the query
        $this->db->select('COUNT(DISTINCT(p.id)) as `total`')
                ->from('products p')
                ->join('product_variants pv', 'pv.product_id = p.id', 'left'); // Use LEFT JOIN for products without variants

        // Where stock type is not null
        $this->db->where('p.stock_type IS NOT NULL');

        // Grouped conditions for low stock (either product or variant)
        $this->db->group_start()
                ->where('p.stock <=', $low_stock_limit)
                ->or_where('pv.stock <=', $low_stock_limit)
                ->group_end();

        // Grouped conditions for availability (either product or variant)
        $this->db->group_start()
                ->where('p.availability', '1')
                ->or_where('pv.availability', '1')
                ->group_end();

        // Branch filter, only applied if a branch_id is provided
        if (!empty($partner_id)) {
            $this->db->where('p.partner_id', $partner_id);
        }

        // Execute the query and return the result
        $product_count = $this->db->get()->result_array();

        // Check if result is found, otherwise return 0
        return isset($product_count[0]['total']) ? $product_count[0]['total'] : 0;
    }


    public function count_products_availability_status($partner_id = "")
    {
        $count_res = $this->db->select(' COUNT( distinct(p.id)) as `total` ')->join('product_variants', 'product_variants.product_id = p.id');
        $where = "p.stock_type is  NOT NULL";
        if (!empty($partner_id) && $partner_id != '') {
            $count_res->where('p.partner_id  =', $partner_id);
        }
        $count_res->where($where);
        $count_res->where('p.stock ', '0');
        $count_res->where('p.availability ', '0');
        $count_res->or_where('product_variants.stock ', '0');
        $count_res->where('product_variants.availability', '0');        
        $product_count = $count_res->get('products p')->result_array();
        return $product_count[0]['total'];
    }
    // public function total_earnings($type = "admin")
    // {
    //     $select = "";
    //     if($type == "admin"){
    //         $select = "SUM(admin_commission_amount) as total";;
    //     }
    //     if($type == "partner"){
    //         $select = "SUM(partner_commission_amount) as total ";
    //     }
    //     if($type == "overall"){
    //         $select = "SUM(final_total) as total ";
    //     }
    //     $count_res = $this->db->select($select);
    //     $where = "is_credited=1";
    //     $count_res->where($where);

    //     $product_count = $count_res->get('orders')->result_array();

    //     return $product_count[0]['total'];
    // }

    public function total_earnings($type = "admin")
    {
        $select = "";
        $promocode_discount_total = 0; // Default value in case of errors or missing data.

        if ($type == "admin") {
            $select = "SUM(admin_commission_amount) as total";

            // Calculate total promocode discounts of type 'default'
            $promocode_count_res = $this->db->select("SUM(promo_discount) as promocode_discount_total");
            $where = "promocode_type='default' AND is_credited=1"; // Corrected condition
            $promocode_count_res->where($where);
            $promocode_result = $promocode_count_res->get('orders')->result_array();

            // If results are valid, retrieve the discount total.
            if (!empty($promocode_result) && isset($promocode_result[0]['promocode_discount_total'])) {
                $promocode_discount_total = $promocode_result[0]['promocode_discount_total'];
            }
        }

        if ($type == "partner") {
            $select = "SUM(partner_commission_amount) as total";
        }

        if ($type == "overall") {
            $select = "SUM(final_total) as total";
        }

        // Fetch main total based on the type
        $count_res = $this->db->select($select);
        $where = "is_credited=1";
        $count_res->where($where);
        $product_count = $count_res->get('orders')->result_array();

        // Return the adjusted total
        return isset($product_count[0]['total']) ? $product_count[0]['total'] - $promocode_discount_total : 0;
    }

}
