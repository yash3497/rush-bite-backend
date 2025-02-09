<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Waiter_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
    }

    function get_waiter_list()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
        $order = 'DESC';
        $multipleWhere = '';
        $where = ['u.active' => 1];

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "u.id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['u.`id`' => $search, 'u.`username`' => $search, 'u.`username`' => $search, 'u.`email`' => $search, 'u.`mobile`' => $search, 'u.`address`' => $search, 'u.`balance`' => $search];
        }
        if (isset($_GET['status_filter']) and $_GET['status_filter'] != '') {
            $where['pd.status'] = $_GET['status_filter'];
        }

        $count_res = $this->db->select(' COUNT(u.id) as `total` ')->join('users_groups ug', ' ug.user_id = u.id ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->group_start();
            $count_res->or_like($multipleWhere);
            $count_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '5';
            $count_res->where($where);
        }

        $offer_count = $count_res->get('users u')->result_array();
        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select('u.*,wd.*')->join('users_groups ug', ' ug.user_id = u.id ')->join('waiter_data wd', ' wd.waiter_id = u.id ');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->group_start();
            $search_res->or_like($multipleWhere);
            $search_res->group_end();
        }
        if (isset($where) && !empty($where)) {
            $where['ug.group_id'] = '5';
            $search_res->where($where);
        }

        $waiter_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('users u')->result_array();
        

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($waiter_search_res as $row) {

            $row = output_escaping($row);
            if ($this->ion_auth->is_partner()) {
                $operate = "<a href=" . base_url('waiter/waiter?edit_id=' . $row['id'] . '') . " data-id=" . $row['id'] . " class='btn btn-success btn-xs mr-1 mb-1' title='Edit' ><i class='fa fa-pen'></i></a>";
            }
            if ($row['status'] == '1') {

                $tempRow['status'] = '<a class="badge badge-success text-white" >Activate</a>';
                $operate .= '<a class="btn btn-warning btn-xs update_active_status mr-1 mb-1" data-table="waiter_data" title="Deactivate" href="javascript:void(0)" data-id="' . $row['id'] . '" data-status="' . $row['status'] . '" ><i class="fa fa-toggle-on"></i></a>';
            } else  if ($row['status'] == '0') {
                $tempRow['status'] = '<a class="badge badge-danger text-white" >Deactivate</a>';
                $operate .= '<a class="btn btn-secondary mr-1 mb-1 btn-xs update_active_status" data-table="waiter_data" href="javascript:void(0)" title="Active" data-id="' . $row['id'] . '" data-status="' . $row['status'] . '" ><i class="fa fa-toggle-off"></i></a>';
            }
            $operate .= ' <a href="javaScript:void(0)" id="delete-restro-waiter" class="btn btn-danger btn-xs mr-1 mb-1" title="Delete" data-id="' . $row['waiter_id'] . '"><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row['id'];
            $tempRow['name'] = $row['username'];
            if (isset($row['email']) && !empty($row['email']) && $row['email'] != "" && $row['email'] != " ") {
                $tempRow['email'] = (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($row['email']) - 3) . substr($row['email'], -3) : $row['email'];
            } else {
                $tempRow['email'] = "";
            }
            $tempRow['mobile'] = (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) ? str_repeat("X", strlen($row['mobile']) - 3) . substr($row['mobile'], -3) :  $row['mobile'];
            $tempRow['address'] = $row['address'];

            $row['profile'] = base_url() . $row['profile'];
            $tempRow['profile'] = '<div class="mx-auto product-image"><a href=' . $row['profile'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['profile'] . ' class="img-fluid rounded"></a></div>';

            $row['national_identity_card'] = base_url() . $row['national_identity_card'];
            $tempRow['national_identity_card'] = '<div class="mx-auto product-image"><a href=' . $row['national_identity_card'] . ' data-toggle="lightbox" data-gallery="gallery"><img src=' . $row['national_identity_card'] . ' class="img-fluid rounded"></a></div>';

            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function add_waiter($data, $profile = [])
    {
        $data = escape_array($data);

        $profile = (!empty($profile)) ? escape_array($profile) : [];
        $tempRow = $rows = array();

        if (!empty($profile)) {

            $waiter_profile = [
                'username' => $profile['name'],
                'email' => $profile['email'],
                'mobile' => $profile['mobile'],
                'address' => $data['address'],
            ];
        }
        if (isset($data['waiter_id'])) {


            $waiters_data = fetch_details(['id' => $data['waiter_id']], 'waiter_data', '*');

            $waiter_data = [
                'national_identity_card' => isset($data['national_identity_card']) && !empty($data['national_identity_card']) ? $data['national_identity_card'] : $waiters_data[0]['national_identity_card'],
                'address' => $data['address'],
                'profile' => isset($data['profile'])  && !empty($data['profile']) ? $data['profile'] : $waiters_data[0]['profile'],
            ];


            if ($this->db->set($waiter_profile)->where('id', $waiters_data[0]['waiter_id'])->update('users')) {
                $this->db->set($waiter_data)->where('waiter_id', $waiters_data[0]['waiter_id'])->update('waiter_data');
                return true;
            } else {
                return false;
            }
        } else {
            $waiter_data = [
                'waiter_id' => $data['user_id'],
                'partner_id' => $data['partner_id'],
                'national_identity_card' => $data['national_identity_card'],
                'address' => $data['address'],
                'profile' => $data['profile'],
            ];
            $this->db->insert('waiter_data', $waiter_data);
            $insert_id = $this->db->insert_id();
            if (!empty($insert_id)) {
                return  $insert_id;
            } else {
                return false;
            }
        }
    }
}
