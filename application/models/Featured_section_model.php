<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Featured_section_model extends CI_Model
{
    function add_featured_section($data)
    {

        $data = escape_array($data);
        $featured_data = [
            'title' => $data['title'],
            // 'slug' => create_unique_slug($data['title'], 'products'),
            'short_description' => $data['short_description'],
            'product_type' => $data['product_type'],
            'categories' => (isset($data['categories']) && !empty($data['categories'])) ? implode(',', $data['categories']) : null,
            'product_ids' => (isset($data['product_ids']) && !empty($data['product_ids']) && strtolower(trim($data['product_type'])) == 'custom_foods') ? implode(',', $data['product_ids']) : null,
            'style' => 'default'
        ];

        if (isset($data['edit_featured_section'])) {
            if (strtolower(trim($data['product_type'])) != 'custom_foods') {
                $featured_data['product_ids'] = null;
            }
            $slug_exist = fetch_details(['id' => $data['edit_featured_section']], 'sections', 'slug');
            if (isset($slug_exist[0]['slug']) && !empty($slug_exist[0]['slug'])) {
                $featured_data['slug'] = $slug_exist[0]['slug'];
            }else{
                $featured_data['slug'] = create_unique_slug($data['title'], 'sections');
            }
            $this->db->set($featured_data)->where('id', $data['edit_featured_section'])->update('sections');
        } else {
            $featured_data['slug'] = create_unique_slug($data['title'], 'sections');
            $this->db->insert('sections', $featured_data);
        }
    }
    public function get_section_list()
    {
        $offset = 0;
        $limit = 10;
        $sort = 'u.id';
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
            $multipleWhere = ['id' => $search, 'title' => $search, 'short_description' => $search];
        }

        $count_res = $this->db->select(' COUNT(id) as `total` ');

        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $count_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $count_res->where($where);
        }

        $city_count = $count_res->get('sections')->result_array();

        foreach ($city_count as $row) {
            $total = $row['total'];
        }

        $search_res = $this->db->select(' * , DATE(date_added) as date_added');
        if (isset($multipleWhere) && !empty($multipleWhere)) {
            $search_res->or_like($multipleWhere);
        }
        if (isset($where) && !empty($where)) {
            $search_res->where($where);
        }

        $city_search_res = $search_res->order_by($sort, $order)->limit($limit, $offset)->get('sections')->result_array();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($city_search_res as $row) {
            $row = output_escaping($row);

            $operate = ' <a href="javascript:void(0)" class="edit_btn btn btn-primary btn-xs mr-1 mb-1" title="Edit" data-id="' . $row['id'] . '" data-url="admin/Featured_sections/"><i class="fa fa-pen"></i></a>';
            $operate .= ' <a  href="javascript:void(0)" class="btn btn-danger btn-xs mr-1 mb-1" title="Delete" data-id="' . $row['id'] . '" id="delete-featured-section" ><i class="fa fa-trash"></i></a>';
            $tempRow['id'] = $row['id'];
            $tempRow['title'] = $row['title'];
            $tempRow['short_description'] = $row['short_description'];
            $tempRow['style'] = ucfirst(str_replace('_', ' ', $row['style']));
            
            // category
            if (isset($row['categories']) && !empty($row['categories'])) {
            $this->db->select('id, name');
            $this->db->from('categories');
            
            // Construct the FIND_IN_SET part of the query
            $ids_str = $row['categories'];
            $this->db->where("FIND_IN_SET(id, '$ids_str')", null, false);
            
            $query = $this->db->get();
            $categories = $query->result_array();
            $categories_rows = '';
            
            for ($i = 0; $i < count($categories); $i++) {
                $categories_rows .= '<p> id: ' . $categories[$i]['id'] . ' , name: ' . $categories[$i]['name'] . '</p>';
            }
            
            $tempRow['categories'] = $categories_rows;
            }
            else{
                $tempRow['categories'] = $row['categories'];
            }
            // product
            if (isset($row['product_ids']) && !empty($row['product_ids'])) {
            $this->db->select('id, name');
            $this->db->from('products');
            
            // Construct the FIND_IN_SET part of the query
            $ids_str = $row['product_ids'];
            $this->db->where("FIND_IN_SET(id, '$ids_str')", null, false);
            
            $query = $this->db->get();
            $products = $query->result_array();
            $products_rows = '';
            
            for ($i = 0; $i < count($products); $i++) {
                $products_rows .= '<p> id: ' . $products[$i]['id'] . ' , name: ' . $products[$i]['name'] . '</p>';
            }
            
            $tempRow['product_ids'] = $products_rows;
            }
            else{
                $tempRow['product_ids'] = $row['product_ids'];
            }
          
            $tempRow['product_type'] = ucwords(str_replace('_', ' ', $row['product_type']));
            $tempRow['date_added'] = $row['date_added'];
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }
}
