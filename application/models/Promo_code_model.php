<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Promo_code_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
    }

    public function get_promo_code_list($offset = 0, $limit = 10, $sort = 'id', $order = 'ASC')
    {
        $multipleWhere = '';

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['p.`id`' => $search, 'p.`promo_code`' => $search, 'p.`message`' => $search, 'p.`start_date`' => $search, 'p.`end_date`' => $search, 'p.`discount`' => $search, 'p.`repeat_usage`' => $search, 'p.`max_discount_amount`' => $search];
        }

        $count_res = $this->db->select(' COUNT(p.id) as `total` ');
 
        if($this->ion_auth->is_partner()){
            $multipleWhere = ['partner_id' => $this->ion_auth->get_user_id()];
        }
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->or_where($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        $sc_count = $count_res->get('promo_codes p')->result_array();

        foreach ($sc_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' p.`id` as id , p.`promo_code`, p.`image` , p.`message` , p.`start_date` , p.`end_date`, p.`promocode_type`, p.`partner_id`, p.`discount` , p.`repeat_usage` ,p.`minimum_order_amount` ,p.`no_of_users` ,p.`discount_type` , p.`max_discount_amount`, p.`no_of_repeat_usage` , p.`status`');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->or_like($multipleWhere);
        }
       
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $sc_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('promo_codes p')->result_array();
       
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($sc_search_res as $row) {
            $row = output_escaping($row);
            if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                
                $operate = '<a href="javascript:void(0)" class="view_btn btn btn-primary btn-xs mr-1 mb-1"  title="view" data-id="' . $row['id'] . '" data-url="admin/promo_code" ><i class="fa fa-eye" ></i></a>';
                $operate .= '<a href="javascript:void(0)" class="edit_btn btn btn-success btn-xs mr-1 mb-1" title="Edit" data-id="' . $row['id'] . '" data-url="admin/promo_code"><i class="fa fa-pen"></i></a>';
                $operate .= '<a class="btn btn-danger btn-xs mr-1 mb-1" href="javascript:void(0)" id="delete-promo-code" title="Delete" data-id="' . $row['id'] . '" ><i class="fa fa-trash"></i></a>';
            }elseif ($this->ion_auth->logged_in() && $this->ion_auth->is_partner()) {
                $operate = '<a href="javascript:void(0)" class="view_btn btn btn-primary btn-xs mr-1 mb-1"  title="view" data-id="' . $row['id'] . '" data-url="partner/promo_code" ><i class="fa fa-eye" ></i></a>';
                if($row['partner_id'] !== "0"){
                    $operate .= '<a href="javascript:void(0)" class="edit_btn btn btn-success btn-xs mr-1 mb-1" title="Edit" data-id="' . $row['id'] . '" data-url="partner/promo_code"><i class="fa fa-pen"></i></a>';
                    $operate .= '<a class="btn btn-danger btn-xs mr-1 mb-1" href="javascript:void(0)" id="delete-partner-promo-code" title="Delete" data-id="' . $row['id'] . '" ><i class="fa fa-trash"></i></a>';
                }
            }

            $tempRow['id'] = $row['id'];
            $tempRow['promo_code'] = $row['promo_code'];
            $tempRow['message'] = $row['message'];
            $tempRow['start_date'] = $row['start_date'];
            $tempRow['end_date'] = $row['end_date'];
            $tempRow['discount'] = $row['discount'];
            $tempRow['promocode_type'] = $row['promocode_type'];
            if($row['partner_id'] !== "0"){
                $partner_name = fetch_details(['user_id' => $row['partner_id']] , 'partner_data' ,'partner_name')[0];
                $tempRow['partner'] = $partner_name['partner_name'];
            }else{

                $tempRow['partner'] = "-";
            }
            $tempRow['repeat_usage'] = ($row['repeat_usage'] == '1') ? 'Allowed' : 'Not Allowed';
            $tempRow['min_order_amt'] = $row['minimum_order_amount'];
            $tempRow['no_of_users'] = $row['no_of_users'];
            $tempRow['discount_type'] = $row['discount_type'];
            $tempRow['max_discount_amt'] = $row['max_discount_amount'];
            $row['image'] = (isset($row['image']) && !empty($row['image'])) ? base_url() . $row['image'] :  base_url() . NO_IMAGE;
            $tempRow['image'] = '<div class="mx-auto product-image"><a href=' . $row['image'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['image'] . ' class="img-fluid rounded"></a></div>';
            $tempRow['no_of_repeat_usage'] = $row['no_of_repeat_usage'];
            if ($row['status'] == '1') {
                $tempRow['status'] = '<span class="badge badge-success" >Active</span>';
            } else {
                $tempRow['status'] = '<span class="badge badge-danger" >Deactive</span>';
            }

            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

   

    public function get_promo_codes($limit = "", $offset = '', $sort = 'u.id', $order = 'DESC', $search = NULL, $partner_id = NULL,$from_partner_app = false)
{
    $multipleWhere = '';
    if (isset($search) and $search != '') {
        $multipleWhere = ['p.`id`' => $search, 'p.`promo_code`' => $search, 'p.`message`' => $search, 'p.`start_date`' => $search, 'p.`end_date`' => $search, 'p.`discount`' => $search, 'p.`repeat_usage`' => $search, 'p.`max_discount_amount`' => $search];
        
    }

    $count_res = $this->db->select(' COUNT(p.id) as `total` ');

    if (isset($multipleWhere) && !empty($multipleWhere)) {
        $count_res->group_Start();
        $count_res->or_where($multipleWhere);
        $count_res->group_end();

    }
    if($from_partner_app == true){

         $where = "(promocode_type = 'partner' AND FIND_IN_SET('$partner_id', partner_id))";

    }else{
         $where = "(CURDATE() between start_date AND end_date) and status=1 and (promocode_type = 'default' OR (promocode_type = 'partner' AND FIND_IN_SET('$partner_id', partner_id)))";


    }
    $count_res->where($where);

    $sc_count = $count_res->get('promo_codes p')->result_array();

    foreach ($sc_count as $row) {
        $total = $row['total'];
    }

    $search_res = $this->db->select(' p.`id` as id ,datediff(end_date, start_date ) as remaining_days, p.`promo_code`, p.`image` , p.`message` , p.`start_date` , p.`end_date`, p.`discount` , p.`repeat_usage` ,p.`minimum_order_amount` ,p.`no_of_users` ,p.`discount_type` , p.`max_discount_amount`, p.`no_of_repeat_usage` , p.`status`,p.`promocode_type`');
    if (isset($multipleWhere) && !empty($multipleWhere)) {
        $search_res->group_Start();
        $search_res->or_like($multipleWhere);
        $search_res->group_end();
    }
     if($from_partner_app == true){
         $where = "(promocode_type = 'partner' AND FIND_IN_SET('$partner_id', partner_id))";

     }else{
         $where = "(CURDATE() between start_date AND end_date) and status=1 and (promocode_type = 'default' OR (promocode_type = 'partner' AND FIND_IN_SET('$partner_id', partner_id)))";
     }
     
    $search_res->where($where);
    $sc_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('promo_codes p')->result_array();
    $bulkData = $rows = $tempRow = array();
    $bulkData['error'] = (empty($sc_search_res)) ? true : false;
    $bulkData['message'] = (empty($sc_search_res)) ? 'Promo code(s) does not exist' : 'Promo code(s) retrieved successfully';
    $bulkData['total'] = (empty($sc_search_res)) ? 0 : $total;

    foreach ($sc_search_res as $row) {
        $row = output_escaping($row);
        $tempRow['id'] = $row['id'];
        $tempRow['promo_code'] = $row['promo_code'];
        $tempRow['promocode_type'] = $row['promocode_type'];
        $tempRow['message'] = $row['message'];
        $tempRow['start_date'] = $row['start_date'];
        $tempRow['end_date'] = $row['end_date'];
        $tempRow['discount'] = $row['discount'];
        $tempRow['repeat_usage'] = ($row['repeat_usage'] == '1') ? 'Allowed' : 'Not Allowed';
        $tempRow['min_order_amt'] = $row['minimum_order_amount'];
        $tempRow['no_of_users'] = $row['no_of_users'];
        $tempRow['discount_type'] = $row['discount_type'];
        $tempRow['max_discount_amt'] = $row['max_discount_amount'];
        $tempRow['image'] = (isset($row['image']) && !empty($row['image'])) ? base_url() . $row['image'] :  base_url() . NO_IMAGE;
        $tempRow['relative_path'] = (isset($row['image']) && !empty($row['image'])) ? $row['image'] :  base_url() . NO_IMAGE;
        $tempRow['no_of_repeat_usage'] = $row['no_of_repeat_usage'];
        $tempRow['status'] = $row['status'];
        $tempRow['remaining_days'] =   $row['remaining_days'];
        $rows[] = $tempRow;
    }
    $bulkData['data'] = $rows;
    if (!empty($bulkData)) {
        return $bulkData;
    } else {
        return $bulkData = [];
    }
}


    public function add_promo_code_details($data)
    {

        /* 
            admin and partner should not have the same name promocode because when order placed with
            promocode then in place order function we are chekingg the type of promocode(admin/partner) and 
            according to that promocode amount will be deduct from admin wallet or partner wallet 
        */

        $data = escape_array($data);
        
        $promo_data = [
            'promo_code' => $data['promo_code'],
            'message' => $data['message'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'promocode_type' => isset($data['promocode_type']) && !empty($data['promocode_type']) && $data['promocode_type'] == 'partner' ? 'partner' : 'default',
            'partner_id' => isset($data['partner_id']) && !empty($data['partner_id']) && $data['promocode_type'] == 'partner'  ? $data['partner_id'] : "0",
            'no_of_users' => $data['no_of_users'], 
            'minimum_order_amount' => $data['minimum_order_amount'],
            'discount' => $data['discount'],
            'discount_type' => $data['discount_type'],
            'max_discount_amount' => $data['max_discount_amount'],
            'repeat_usage' => $data['repeat_usage'],
            'status' => $data['status'],
            'image' => (isset($data['image']) && !empty($data['image']) && $data['image'] != "") ? $data['image'] : ""
        ];
        if ($data['repeat_usage'] == '1') {
            $promo_data['no_of_repeat_usage'] = $data['no_of_repeat_usage'];
        }
       
        if (isset($data['edit_promo_code']) && !empty($data['edit_promo_code'])) {
            $this->db->set($promo_data)->where('id', $data['edit_promo_code'])->update('promo_codes');
        } else {
            $this->db->insert('promo_codes', $promo_data);
        }
    }
}
