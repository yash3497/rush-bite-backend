<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Table_booking_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
    }

    public function add_floore($data)
    {
        $data = escape_array($data);

        
        if (isset($data['status']) && !empty($data['status'])) {
            $attr_data = [
                'title' => $data['title'],
                'status' => $data['status'],
                'partner_id' => $data['partner_id']
            ];
        } else {
            $attr_data = [
                'title' => $data['title'],
                'partner_id' => $data['partner_id']
            ];
        }
        if (isset($data['edit_floore']) && !empty($data['edit_floore'])) {
            $this->db->set($attr_data)->where('id', $data['edit_floore'])->update('floore');
            return $data['edit_floore'];
        } else {
            $this->db->insert('floore', $attr_data);
            return $this->db->insert_id();
        }
    }

    public function get_floore_list(
        $partner_id = NULL,
        $offset = 0,
        $limit = 10,
        $sort = 'id',
        $order = 'DESC',
        $status = NULL
    ) {
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
            $multipleWhere = ['title' => $search];
        }
        if (isset($partner_id) and $partner_id != '') {
            $where = ['partner_id' => $partner_id];
        }

        $count_res = $this->db->select(' COUNT(f.id) as `total`');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        $attr_count = $count_res->get('floore f')->result_array();

        foreach ($attr_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select('f.id as floore_id,f.title,f.status');
        if (isset($partner_id) and $partner_id != '') {
            $where = ['partner_id' => $partner_id];
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $floore_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->group_by('f.id')->get('floore f')->result_array();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($floore_search_res as $row) {

            $row = output_escaping($row);
            $partner_id = $this->ion_auth->get_user_id();
            if ($this->ion_auth->is_partner()) {
                $operate = "<a href=" . base_url('partner/Table_booking?edit_id=' . $row['floore_id'] . '') . " data-id=" . $row['floore_id'] . " class='btn btn-success btn-xs mr-1 mb-1' title='Edit' ><i class='fa fa-pen'></i></a>";
            }
            if ($row['status'] == '1') {
                $tempRow['status'] = '<a class="badge badge-success text-white" >Available</a>';
                $operate .= '<a class="btn btn-warning btn-xs update_active_status mr-1 mb-1" data-table="floore" title="Deactivate" href="javascript:void(0)" data-id="' . $row['floore_id'] . '" data-status="' . $row['status'] . '" ><i class="fa fa-toggle-on"></i></a>';
            } else  if ($row['status'] == '0') {
                $tempRow['status'] = '<a class="badge badge-danger text-white" >Booked</a>';
                $operate .= '<a class="btn btn-secondary mr-1 mb-1 btn-xs update_active_status" data-table="floore" href="javascript:void(0)" title="Active" data-id="' . $row['floore_id'] . '" data-status="' . $row['status'] . '" ><i class="fa fa-toggle-off"></i></a>';
            }
            $operate .= ' <a href="javaScript:void(0)" id="delete-restro-floore" class="btn btn-danger btn-xs mr-1 mb-1" title="Delete" data-id="' . $row['floore_id'] . '"><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row['floore_id'];
            $tempRow['title'] = $row['title'];
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    //  add table

    public function add_table($data)
    {
        $data = escape_array($data);


        $attr_data = [
            'title' => $data['title'],
            'partner_id' => $data['partner_id'],
            'floore_id' => $data['floore_id']
        ];

        if (isset($data['edit_table']) && !empty($data['edit_table'])) {
            $this->db->set($attr_data)->where('id', $data['edit_table'])->update('tables');
            return $data['edit_table'];
        } else {
            $this->db->insert('tables', $attr_data);
            return $this->db->insert_id();
        }
    }

    public function get_table_list(
        $partner_id = NULL,
        $offset = 0,
        $limit = 10,
        $sort = 't.id',
        $order = 'DESC',
        $status = NULL
    ) {
        $multipleWhere = '';

        if (isset($_GET['offset']))
            $offset = $_GET['offset'];
        if (isset($_GET['limit']))
            $limit = $_GET['limit'];

        if (isset($_GET['sort']))
            if ($_GET['sort'] == 'id') {
                $sort = "t.id";
            } else {
                $sort = $_GET['sort'];
            }
        if (isset($_GET['order']))
            $order = $_GET['order'];

        if (isset($_GET['search']) and $_GET['search'] != '') {
            $search = $_GET['search'];
            $multipleWhere = ['title' => $search];
        }
        if (isset($partner_id) and $partner_id != '') {
            $where = ['partner_id' => $partner_id];
        }

        $count_res = $this->db->select(' COUNT(t.id) as `total`');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        $attr_count = $count_res->get('tables t')->result_array();

        foreach ($attr_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select('*,t.id as table_id,t.title,t.status,t.floore_id,f.title as floore_title')->join("floore f", "f.id=t.floore_id");
        if (isset($partner_id) and $partner_id != '') {
            $where = ['t.partner_id' => $partner_id];
        }

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $floore_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->group_by('t.id')->get('tables t')->result_array();


        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($floore_search_res as $row) {

            $row = output_escaping($row);
            $partner_id = $this->ion_auth->get_user_id();
            if ($this->ion_auth->is_partner()) {
                $operate = "<a href=" . base_url('partner/Table_booking/new_table?edit_id=' . $row['table_id'] . '') . " data-id=" . $row['table_id'] . " class='btn btn-success btn-xs mr-1 mb-1' title='Edit' ><i class='fa fa-pen'></i></a>";
            }
            if ($row['status'] == '1') {
                $tempRow['status'] = '<a class="badge badge-success text-white" >Available</a>';
                $operate .= '<a class="btn btn-warning btn-xs update_active_status mr-1 mb-1" data-table="tables" title="Deactivate" href="javascript:void(0)" data-id="' . $row['table_id'] . '" data-status="' . $row['status'] . '" ><i class="fa fa-toggle-on"></i></a>';
            } else  if ($row['status'] == '0') {
                $tempRow['status'] = '<a class="badge badge-danger text-white" >Booked</a>';
                $operate .= '<a class="btn btn-secondary mr-1 mb-1 btn-xs update_active_status" data-table="tables" href="javascript:void(0)" title="Active" data-id="' . $row['table_id'] . '" data-status="' . $row['status'] . '" ><i class="fa fa-toggle-off"></i></a>';
            }
            $operate .= ' <a href="javaScript:void(0)" id="delete-restro-table" class="btn btn-danger btn-xs mr-1 mb-1" title="Delete" data-id="' . $row['table_id'] . '"><i class="fa fa-trash"></i></a>';

            $tempRow['id'] = $row['table_id'];
            $tempRow['floore_id'] = $row['floore_id'];
            $tempRow['floore_name'] = $row['floore_title'];
            $tempRow['title'] = $row['title'];
            $tempRow['operate'] = $operate;

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }
}
