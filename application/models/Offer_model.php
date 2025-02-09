<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Offer_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
    }

    function add_offer($image_name)
    {
        $image_name = escape_array($image_name);
        $offer_data = [
            'type' => $image_name['offer_type'],
            'image' => $image_name['image'],
            'start_date' => $image_name['start_date'],
            'end_date' => $image_name['end_date'],
        ];
        if (isset($image_name['offer_type']) && $image_name['offer_type'] == 'categories' && isset($image_name['category_id']) && !empty($image_name['category_id'])) {
            $offer_data['type_id'] = $image_name['category_id'];
        }

        if (isset($image_name['offer_type']) && $image_name['offer_type'] == 'products' && isset($image_name['product_id']) && !empty($image_name['product_id'])) {
            $offer_data['type_id'] = $image_name['product_id'];
        }
        if (isset($image_name['banner'])) {
            $offer_data['banner'] = (isset($image_name['banner']) && !empty($image_name['banner'])) ? $image_name['banner'] : '';
        }
        if (isset($image_name['edit_offer'])) {
            if (empty($image_name['image'])) {
                unset($offer_data['image']);
            }
            $offer_data['banner'] = (isset($offer_data['banner'])) ? $offer_data['banner'] : '';
            $this->db->set($offer_data)->where('id', $image_name['edit_offer'])->update('offers');
        } else {
            $this->db->insert('offers', $offer_data);
        }
    }

    function get_offer_list()
    {

        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';
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
            $multipleWhere = ['`id`' => $search, '`type`' => $search];
        }

        $count_res = $this->db->select(' COUNT(id) as `total`');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        $offer_count = $count_res->get('offers')->result_array();

        foreach ($offer_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' * , DATE(date_added) as date_added');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $offer_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('offers')->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        foreach ($offer_search_res as $row) {
            $row = output_escaping($row);
            $operate = ' <a href="' . base_url('admin/offer?edit_id=' . $row['id']) . '" class="btn btn-success btn-xs mr-1 mb-1"  title="Edit" data-id="' . $row['id'] . '" data-url="admin/offer/"><i class="fa fa-pen"></i></a>';
            $operate .= ' <a href="javaScript:void(0)" id="delete-offer" class="btn btn-danger btn-xs mr-1 mb-1" title="Delete" data-id="' . $row['id'] . '"><i class="fa fa-trash"></i></a>';
            
            $imageWidth = '200px';
            $imageHeight = '100px';

            $bannerWidth = '200px';
            $bannerHeight = '100px';

            $tempRow['id'] = $row['id'];
            $tempRow['type'] = $row['type'];
            $tempRow['start_date'] = $row['start_date'];
            $tempRow['end_date'] = $row['end_date'];
            $tempRow['type_id'] = $row['type_id'];
            if($row['type'] == 'categories'){
                $category_name = fetch_details(['id' => $row['type_id']],'categories','name');
                $tempRow['name'] = isset($category_name[0]['name']) ? $category_name[0]['name'] : "";
            }else if($row['type'] == 'products'){
                $product_name = fetch_details(['id' => $row['type_id']],'products','name');
                $tempRow['name'] = isset($product_name[0]['name']) ? $product_name[0]['name'] : ""; 
            }else {
                
                $tempRow['name'] = "-";
            }
            if (empty($row['image']) || file_exists(FCPATH . $row['image']) == FALSE) {
                $row['image'] = base_url() . NO_IMAGE;
                $row['image_main'] = base_url() . NO_IMAGE;
            } else {
                $row['image_main'] = base_url($row['image']);
                $row['image'] = get_image_url($row['image'], 'thumb', 'sm');
            }
            if (empty($row['banner']) || file_exists(FCPATH . $row['banner']) == FALSE) {
                $row['banner'] = base_url() . NO_IMAGE;
                $row['banner'] = base_url() . NO_IMAGE;
            } else {
                $row['banner'] = get_image_url($row['banner'], 'thumb', 'sm');
            }
            $tempRow['image'] = "<div class='image-container'><a href='" . $row['image_main'] . "' data-toggle='lightbox' data-gallery='gallery'> <img src='" . $row['image'] . "' class='image-box-100' ></a></div>";
            $tempRow['banner'] = "<div class='image-container'><a href='" . $row['banner'] . "' data-toggle='lightbox' data-gallery='gallery'> <img src='" . $row['banner'] . "' class='image-box-100' ></a></div>";

            $tempRow['date_added'] = $row['date_added'];
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }
}
