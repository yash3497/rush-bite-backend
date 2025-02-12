<?php
defined('BASEPATH') or exit('No direct script access allowed');


/*
---------------------------------------------------------------------------
Defined Methods:-
---------------------------------------------------------------------------
1. login
2  get_orders
3. update_order_status
4. get_categories
5. get_products
6. get_customers
7. get_transactions
8. get_statistics
9. forgot_password
10. delete_order
11. verify_user
12. get_settings
13. update_fcm
14. get_cities
15. get_taxes
16. send_withdrawal_request
17. get_withdrawal_request
18. add_attributes
19. edit_attributes
20. get_attributes
21. get_attribute_values
22. add_products
23. get_media
24. get_partner_details
25. update_partner
26. delete_product
27. update_products
28. get_riders
29. reset_password
30. get_tags
31. add_tags
32. delete_tag
33. upload_media
34. get_product_add_ons
35. update_add_ons
36. delete_add_on
37. update_product_status
38. update_attribute_status
39. add_floore
40. delete_floore
---------------------------------------------------------------------------
*/
class Api extends CI_Controller
{
    /**
     *   @var array $excluded_routes is an array of uri strings which we want to exclude from jwt verification.
     */
    protected $excluded_routes =
    [
        "partner/app/v1/api/index",
        "partner/app/v1/api",
        "partner/app/v1/api/login",
        "partner/app/v1/api/resend_otp",
        "partner/app/v1/api/verify_otp",
        "partner/app/v1/api/forgot_password",
        "partner/app/v1/api/verify_user",
        "partner/app/v1/api/get_settings",
        "partner/app/v1/api/get_cities",
        "partner/app/v1/api/update_partner",
        "partner/app/v1/api/upload_media",
        "partner/app/v1/api/manage_promocode",
        "partner/app/v1/api/get_promo_codes",
        "partner/app/v1/api/send_withdrawal_request",
        // "partner/app/v1/api/delete_partner_account",

    ];

    private  $user_details = [];
    private  $allowed_settings = ["general_settings", "terms_conditions", "privacy_policy", "about_us", 'payment_gateways_settings'];
    private  $user_data = [
        'id', 'username', 'mobile', 'email', 'fcm_id', 'image', 'latitude', 'longitude', 'friends_code', 'referral_code',
        'city', 'serviceable_city', 'country_code', 'cash_received', 'commission', 'commission_method', 'active', 'no_of_ratings', 'rating', 'balance'
    ];

    public function __construct()
    {

        parent::__construct();
        header("Content-Type: application/json");
        header("Expires: 0");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $this->load->library(['upload', 'jwt', 'ion_auth', 'form_validation']);
        $this->load->model(['Order_model', 'category_model', 'transaction_model', 'Home_model', 'customer_model', 'ticket_model', 'Rider_model', 'Area_model', 'Attribute_model', 'Product_model', 'media_model', 'Partner_model', 'Tag_model', 'Promo_code_model']);
        $this->load->helper(['sms_helper']);
        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
        $this->lang->load('auth');
        $response = $temp = $bulkdata = array();
        $this->identity_column = $this->config->item('identity', 'ion_auth');
        // initialize db tables data
        $this->tables = $this->config->item('tables', 'ion_auth');

        $current_uri =  uri_string();
        if (!in_array($current_uri, $this->excluded_routes)) {
            $token = verify_app_request();
            if ($token['error']) {
                header('Content-Type: application/json');
                http_response_code($token['status']);
                print_r(json_encode($token));
                die();
            }
            $this->user_details = $token['data'];
        }
    }


    public function index()
    {
        $this->load->helper('file');
        $this->output->set_content_type(get_mime_by_extension(base_url('partner-api-doc.txt')));
        $this->output->set_output(file_get_contents(base_url('partner-api-doc.txt')));
    }

    public function login()
    {
        /* Parameters to be passed
            mobile: 9874565478
            password: 12345678
            fcm_id: FCM_ID //{ optional }
            device_type: android/ios
        */


        $identity_column = $this->config->item('identity', 'ion_auth');
        if ($identity_column == 'mobile') {
            $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        } elseif ($identity_column == 'email') {
            $this->form_validation->set_rules('email', 'Email', 'trim|required|xss_clean|valid_email');
        } else {
            $this->form_validation->set_rules('identity', 'Identity', 'trim|required|xss_clean');
        }
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('fcm_id', 'FCM ID', 'trim|xss_clean');
        $this->form_validation->set_rules('device_type', 'Device Type', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        $login = $this->ion_auth->login($this->input->post('mobile'), $this->input->post('password'), false);
        if ($login) {
            $data = fetch_details(['mobile' => $this->input->post('mobile', true)], 'users', "id");
            $restro_filter['id'] = $data[0]['id'];
            $restro_filter['ignore_status'] = true;

            $restro_data = fetch_partners($restro_filter);

            if (!empty($restro_data['data'])) {
                $status = $restro_data['data'][0]['status'];
                unset($restro_data['data'][0]['partner_cook_time']);
            } else {
                $status = "";
            }

            if ($this->ion_auth->in_group('partner', $data[0]['id'])) {
                if (isset($_POST['fcm_id']) && $_POST['fcm_id'] != '') {
                    update_details(['fcm_id' => $_POST['fcm_id']], ['mobile' => $_POST['mobile']], 'users');
                }
                if (isset($_POST['device_type']) && !empty($_POST['device_type'])) {
                    update_details(['platform' => $_POST['device_type']], ['mobile' => $_POST['mobile']], 'users');
                }
                $messages = array("0" => "Your acount is deactivated", "1" => "Logged in successfully", "2" => "Your account is not yet approved.", "7" => "Your account has been removed by the admin. Contact admin for more information.");

                /** set user jwt token if login successfull */
                if (isset($status) && $status != "" && ($status == 1 || $status == 0) && isset($restro_data) && !empty($restro_data)) {
                    $token = generate_tokens($this->input->post('mobile'));
                    update_details(['apikey' => $token], ['mobile' => $this->input->post('mobile')], "users");
                }

                //if the login is successful
                $response['error'] = (isset($status) && $status != "" && ($status == 1 || $status == 0) && isset($restro_data) && !empty($restro_data)) ? false : true;
                $response['token'] = (isset($status) && $status != "" && ($status == 1 || $status == 0) && isset($restro_data) && !empty($restro_data)) ?  $token : "";
                $response['message'] =  (isset($messages[$status]) && !empty($messages[$status])) ? $messages[$status] : "Something went wrong.";
                $response['data'] = (isset($status) && $status != "" && ($status == 1 || $status == 0) && isset($restro_data) && !empty($restro_data)) ?  $restro_data['data'][0] : [];
                echo json_encode($response);
                return false;
            } else {
                $response['error'] = true;
                $response['message'] = 'Incorrect Login.';
                echo json_encode($response);
                return false;
            }
        } else {
            // if the login was un-successful
            $response['error'] = true;
            $response['message'] = strip_tags($this->ion_auth->errors());
            echo json_encode($response);
            return false;
        }
    }

    public function get_orders()
    {
        /* 2.get_orders
            partner_id:174 
            id:101 { optional }
            user_id:101 { optional }
            start_date : 2020-09-07 or 2020/09/07 { optional }
            end_date : 2021-03-15 or 2021/03/15 { optional }
            search:keyword      // optional
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort: id / date_added // { default - id } optional
            order:DESC/ASC      // { default - DESC } optional
            active_status: pending  {pending,confirmed,preparing,out_for_delivery,delivered,cancelled}     // optional
        */
        if (!verify_tokens()) {
            return false;
        }

        $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
        $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
        $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'o.id';
        $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';
        $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : '';

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('active_status', 'status', 'trim|xss_clean');
        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $partner_id = $this->input->post('partner_id', true);
            $id = (isset($_POST['id']) && !empty($_POST['id'])) ? $_POST['id'] : false;
            $user_id = (isset($_POST['user_id']) && !empty($_POST['user_id'])) ? $_POST['user_id'] : false;
            $start_date = (isset($_POST['start_date']) && !empty($_POST['start_date'])) ? $_POST['start_date'] : false;
            $end_date = (isset($_POST['end_date']) && !empty($_POST['end_date'])) ? $_POST['end_date'] : false;
            $multiple_status =   (isset($_POST['active_status']) && !empty($_POST['active_status'])) ? explode(',', $_POST['active_status']) : false;
            $download_invoice =   (isset($_POST['download_invoice']) && !empty($_POST['download_invoice'])) ? $_POST['download_invoice'] : 1;
            $download_thermal_invoice =   (isset($_POST['download_thermal_invoice']) && !empty($_POST['download_thermal_invoice'])) ? $_POST['download_thermal_invoice'] : 1;
            $city_id =   (isset($_POST['city_id']) && !empty($_POST['city_id'])) ? $_POST['city_id'] : null;
            $order_details = fetch_orders($id, $user_id, $multiple_status, false, $limit, $offset, $sort, $order, $download_invoice, $download_thermal_invoice, $start_date, $end_date, $search, $city_id, null, $partner_id);
            if (!empty($order_details['order_data'])) {
                $this->response['error'] = false;
                $this->response['message'] = 'Data retrieved successfully';
                $this->response['total'] = $order_details['total'];
                $this->response['pending'] = strval(orders_count("pending", $partner_id));
                $this->response['confirmed'] = strval(orders_count("confirmed", $partner_id));
                $this->response['preparing'] = strval(orders_count("preparing", $partner_id));
                $this->response['out_for_delivery'] = strval(orders_count("out_for_delivery", $partner_id));
                $this->response['delivered'] = strval(orders_count("delivered", $partner_id));
                $this->response['cancelled'] = strval(orders_count("cancelled", $partner_id));
                $this->response['ready_for_pickup'] = strval(orders_count("ready_for_pickup", $partner_id));
                $this->response['awaiting'] = strval(orders_count("awaiting", $partner_id));
                $this->response['data'] = $order_details['order_data'];
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Data Does Not Exists';
                $this->response['total'] = "0";
                $this->response['pending'] = "0";
                $this->response['confirmed'] = "0";
                $this->response['preparing'] = "0";
                $this->response['out_for_delivery'] = "0";
                $this->response['delivered'] = "0";
                $this->response['cancelled'] = "0";
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    //3. update_order_status 
    /* to update the status of order */
    public function update_order_status()
    {
        /* 
            partner_id:12
            order_id: 137
            deliver_by:rider_id        {optional}{required when its out for delivery and delivered status}
            status: confirmed                 {pending|confirmed|preparing|out_for_delivery|delivered|cancelled}  
            reason: text              //{required when order status is cancelled}
            owner_note: message      // {pass this param if order is self pickup}
            self_pickup_time:date & time   //{pass this param if order is self pickup}{2022-06-25 13:25:00}
        */

        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('order_id', 'Order Id', 'numeric|trim|required|xss_clean');
        $this->form_validation->set_rules('partner_id', 'partner Id', 'numeric|trim|required|xss_clean');
        $pickup = fetch_details(['id' => $_POST['order_id']], 'orders', 'is_self_pick_up');
        if (isset($pickup) && !empty($pickup)) {
            if ($pickup[0]['is_self_pick_up'] == "1") {
                $this->form_validation->set_rules('owner_note', 'owner_note', 'trim|required|xss_clean');
                $this->form_validation->set_rules('self_pickup_time', 'self_pickup_time', 'trim|required|xss_clean');
            }
        }
         if (isset($pickup) && !empty($pickup)) {
            if ($pickup[0]['is_self_pick_up'] == "1"){
            }else{

                if ((isset($_POST['deliver_by']) && !empty($_POST['deliver_by'])) || $_POST['status'] == 'out_for_delivery' || $_POST['status'] == 'delivered') {
                    $this->form_validation->set_rules('deliver_by', 'Deliver By', 'numeric|trim|xss_clean|required');
                }
            }
        }
        $this->form_validation->set_rules('status', 'Status', 'trim|required|xss_clean|in_list[pending,confirmed,preparing,out_for_delivery,delivered,cancelled,ready_for_pickup]');
        if (isset($_POST['status']) && !empty($_POST['status']) && $_POST['status'] == 'cancelled') {
            $this->form_validation->set_rules('reason', 'reason', 'trim|required|xss_clean');
        }

        
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
        } else {
            $msg = '';
            $order_id = $this->input->post('order_id', true);
            $partner_id = $this->input->post('partner_id', true);
            $settings = get_settings('system_settings', true);
            $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';
            $deliver_by = (isset($_POST['deliver_by']) && !empty($_POST['deliver_by'])) ? $this->input->post('deliver_by', true) : "0";
            $val = $this->input->post('status', true);
            $reason = (isset($_POST['reason']) && !empty($_POST['reason'])) ? $this->input->post('reason', true) : "";
            $field = "status";
            $owner_note = (isset($_POST['owner_note']) && !empty($_POST['owner_note'])) ? $this->input->post('owner_note', true) : "";
            $self_pickup_time = (isset($_POST['self_pickup_time']) && !empty($_POST['self_pickup_time'])) ? $this->input->post('self_pickup_time', true) : "";

            if (isset($_POST['deliver_by']) && !empty($_POST['deliver_by'])) {
                if (!get_partner_permission($partner_id, "assign_rider")) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'You are not allowed to update Rider on order.Contact Admin for permission.';
                    $this->response['data'] = array();
                    print_r(json_encode($this->response));
                    return false;
                }
            }

            if (isset($_POST['deliver_by']) && !empty($_POST['deliver_by'])) {
               
            }

            if (isset($deliver_by) && !empty($deliver_by) && isset($order_id) && !empty($order_id)) {
                if ($val == "pending") {
                    $this->response['error'] = true;
                    $this->response['message'] = "First confirm the order by restaurant then you can assign rider for this order.";
                    $this->response['data'] = array();
                    print_r(json_encode($this->response));
                    return false;
                }
                $result = update_rider($deliver_by, $order_id, $val);
                if ($result['error']) {
                    $this->response['error'] = true;
                    $this->response['message'] = $result['message'];
                    $this->response['data'] = array();
                    print_r(json_encode($this->response));
                    return false;
                } else {
                    $msg  = $result['message'];
                }
            }
            $res = validate_order_status($order_id, $val, 'orders', $partner_id);
            if ($res['error']) {
                $this->response['error'] = true;
                $this->response['message'] = $msg . $res['message'];
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return false;
            }

            $priority_status = array();
            if ($val == 'ready_for_pickup') {
                $priority_status = [
                    'pending' => 0,
                    'confirmed' => 1,
                    'preparing' => 2,
                    'ready_for_pickup' => 3,
                    'delivered' => 4,
                    'cancelled' => 5,
                ];
            } else {
                $priority_status = [
                    'pending' => 0,
                    'confirmed' => 1,
                    'preparing' => 2,
                    'out_for_delivery' => 3,
                    'delivered' => 4,
                    'cancelled' => 5,
                ];
            }

            $error = TRUE;
            $message = '';

            $where_id = "id = " . $order_id . " and (active_status != 'cancelled' ) ";

            if (isset($order_id) && isset($field) && isset($val)) {
                if ($field == 'status') {
                    $current_orders_status = fetch_details($where_id, 'orders', 'user_id,active_status');
                    $user_id = $current_orders_status[0]['user_id'];
                    $current_orders_status = $current_orders_status[0]['active_status'];

                    if ($priority_status[$val] > $priority_status[$current_orders_status]) {
                        $set = [
                            $field => $val,
                            "reason" => $reason,
                            "owner_note" => $owner_note,
                            "self_pickup_time" => $self_pickup_time,
                            "cancel_by" => $this->session->userdata('user_id')
                        ];

                        /* Update Active Status of Order Table */
                        if ($this->Order_model->update_order($set, $where_id, true)) {
                            if ($this->Order_model->update_order(['active_status' => $val], $where_id)) {
                                $error = false;
                            }
                        }

                        if ($error == false) {
                            /* Send notification */
                            if ($val  == 'pending') {
                                $type = ['type' => "customer_order_pending"];
                            } elseif ($val == 'confirmed') {
                                $type = ['type' => "customer_order_confirm"];
                            } elseif ($val == 'preparing') {
                                $type = ['type' => "customer_order_preparing"];
                            } elseif ($val == 'delivered') {
                                $type = ['type' => "customer_order_delivered"];
                            } elseif ($val == 'cancelled') {
                                $type = ['type' => "customer_order_cancelled"];
                            } elseif ($val == 'out_for_delivery') {
                                $type = ['type' => "customer_order_out_for_delivery"];
                            }

                            $custom_notification = fetch_details($type, 'custom_notifications', '*');

                            $hashtag_order_id = '< order_item_id >';
                            $hashtag_application_name = '< application_name >';
                            $string = json_encode($custom_notification[0]['message'], JSON_UNESCAPED_UNICODE);
                            $hashtag = html_entity_decode($string);
                            $data = str_replace(array($hashtag_order_id, $hashtag_application_name), array($order_id, $app_name), $hashtag);
                            $message = output_escaping(trim($data, '"'));

                            $title = (!empty($custom_notification)) ? $custom_notification[0]['title'] : 'Order status updated';
                            $body = (!empty($custom_notification)) ? $message : 'Order status updated to ' . $val . ' for your order ID #' . $order_id . ' please take note of it! Thank you for ordering with us.';
                            send_notifications($user_id, "user", $title, $body, "order", $order_id);

                            /* Process refund when order cancel */
                            process_refund($order_id, $val, 'orders');
                            if (trim($val) == 'cancelled') {
                                $data = fetch_details(['order_id' => $order_id], 'order_items', 'product_variant_id,quantity');
                                $product_variant_ids = $qtns = [];
                                foreach ($data as $d) {
                                    array_push($product_variant_ids, $d['product_variant_id']);
                                    array_push($qtns, $d['quantity']);
                                }
                                update_stock($product_variant_ids, $qtns, 'plus');
                            }

                            /* Process refer and earn bonus */
                            $response = process_referral_bonus($user_id, $order_id, $val);
                            $message = 'Status Updated Successfully';
                        }
                    }
                }
                if ($error == true) {
                    $message = $msg . ' Status Updation Failed';
                }
            }
            $response['error'] = $error;
            $response['message'] = $message;
            print_r(json_encode($response));
        }
    }

    // 4.get_categories
    public function get_categories()
    {
        /*
            id:15               // optional
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:               id / name
                                // { default -row_id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // { optional }
        */

        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('id', 'Category Id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');
        $this->form_validation->set_rules('search', 'search', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }
        $limit = (isset($_POST['limit'])  && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : "";
        $offset = (isset($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : "";
        $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'row_order';
        $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'ASC';
        $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : null;

        $this->response['message'] = "Cateogry(s) retrieved successfully!";
        $id = (!empty($_POST['id']) && isset($_POST['id'])) ? $_POST['id'] : '';
        $cat_res = $this->category_model->get_categories($id, $limit, $offset, $sort, $order, "", "", 1, $search, "",1);

        $this->response['error'] = (empty($cat_res)) ? true : false;
        $this->response['total'] = !empty($cat_res) ? $cat_res[0]['total'] : 0;
        $this->response['message'] = (empty($cat_res)) ? 'Category does not exist' : 'Category retrieved successfully';
        $this->response['data'] = $cat_res;

        print_r(json_encode($this->response));
    }

    // 5.get_products
    public function get_products()
    {
        /*
        partner_id:175
        id:101              // optional
        category_id:29      // optional
        user_id:15          // optional
        search:keyword      // optional   // search by product name and highlights and tags
        tags:multiword tag1, tag2, another tag      // optional {search by restro and product tags}
        highlights:multiword tag1, tag2, another tag      // optional
        attribute_value_ids : 34,23,12 // { Use only for filteration } optional
        limit:25            // { default - 25 } optional
        offset:0            // { default - 0 } optional
        sort:p.id / p.date_added / pv.price
                            { default - p.id } optional
        order:DESC/ASC      // { default - DESC } optional
        top_rated_product: 1 // { default - 0 } optional
        discount: 5             // optional
        min_price:10000          // optional
        max_price:50000          // optional
        product_ids: 19,20             // optional
        product_variant_ids: 44,45,40             // optional
        vegetarian:1|2             //{optional -> 1 - veg | 2 - non-veg}
        filter_by:p.id|pd.user_id       // {p.id = product list | pd.user_id = partner list}            
                             { default - pd.user_id } optional
        flag:low/sold      // optional
        show_only_active_products:false { default - true } optional <- used for partner app to get active/deactive both the products

        */

        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('partner_id', 'partner id', 'required|trim|numeric|xss_clean');
        $this->form_validation->set_rules('id', 'Product ID', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('vegetarian', 'vegetarian', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
        $this->form_validation->set_rules('category_id', 'Category id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('attribute_value_ids', 'Attr Ids', 'trim|xss_clean');
        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean|alpha');
        $this->form_validation->set_rules('top_rated_foods', ' Top Rated Foods ', 'trim|xss_clean|numeric');
        $this->form_validation->set_rules('min_price', ' Min Price ', 'trim|xss_clean|numeric|less_than_equal_to[' . $this->input->post('max_price') . ']');
        $this->form_validation->set_rules('max_price', ' Max Price ', 'trim|xss_clean|numeric|greater_than_equal_to[' . $this->input->post('min_price') . ']');
        $this->form_validation->set_rules('discount', ' Discount ', 'trim|xss_clean|numeric');
        $this->form_validation->set_rules('filter_by', ' filter_by ', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $limit = (isset($_POST['limit'])) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset'])) ? $this->input->post('offset', true) : 0;
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'ASC';
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true)  : 'p.row_order';
            $partner_id = (isset($_POST['partner_id']) && !empty(trim($_POST['partner_id']))) ?  $this->input->post('partner_id', true) : NULL;
            $filters['search'] =  (isset($_POST['search'])) ? $this->input->post('search', true)  : null;
            $filters['tags'] =  (isset($_POST['tags'])) ? $this->input->post('tags', true)  : "";
            $filters['highlights'] =  (isset($_POST['highlights'])) ? $this->input->post('highlights', true)  : "";
            $filters['attribute_value_ids'] = (isset($_POST['attribute_value_ids'])) ? $this->input->post('attribute_value_ids', true)  : null;
            $filters['is_similar_products'] = (isset($_POST['is_similar_products'])) ? $this->input->post('is_similar_products', true)  : null;
            $filters['vegetarian'] = (isset($_POST['vegetarian'])) ? $this->input->post("vegetarian", true) : null;
            $filters['discount'] = (isset($_POST['discount'])) ? $this->input->post('discount', true) : 0;
            $filters['product_type'] = (isset($_POST['top_rated_foods']) && $_POST['top_rated_foods'] == 1) ? 'top_rated_foods_including_all_foods' : null;
            $filters['min_price'] = (isset($_POST['min_price']) && !empty($_POST['min_price'])) ? $this->input->post("min_price", true) : 0;
            $filters['max_price'] = (isset($_POST['max_price']) && !empty($_POST['max_price'])) ? $this->input->post("max_price", true) : 0;
            $filter_by = (isset($_POST['filter_by']) && !empty($_POST['filter_by'])) ? $this->input->post("filter_by", true) : 'p.id';
            $filters['flag'] =  (isset($_POST['flag']) && !empty($_POST['flag'])) ? $this->input->post("flag", true) : "";
            $filters['show_only_active_products'] = (isset($_POST['show_only_active_products'])) ? $this->input->post('show_only_active_products', true) : false;

            $category_id = (isset($_POST['category_id'])) ?  $this->input->post('category_id', true)  : null;
            $product_id = (isset($_POST['id'])) ? $this->input->post('id', true)  : null;
            $product_ids = (isset($_POST['product_ids'])) ?  $this->input->post('product_ids', true) : null;
            $product_variant_ids = (isset($_POST['product_variant_ids']) && !empty($_POST['product_variant_ids'])) ? $this->input->post("product_variant_ids", true) : null;
            if ($product_ids != null) {
                $product_id = explode(",", $product_ids);
            }
            if ($product_variant_ids != null) {
                $filters['product_variant_ids'] = explode(",", $product_variant_ids);
            }
            $user_id = (isset($_POST['user_id'])) ? $this->input->post('user_id', true)  : null;

            $products = fetch_product($user_id, (isset($filters)) ? $filters : null, $product_id, $category_id, $limit, $offset, $sort, $order, null, null, $partner_id, $filter_by);

            $final_total = "0";
            if (isset($filters['discount']) && !empty($filters['discount'])) {
                $final_total = (isset($products['product'][0]['total']) && !empty($products['product'][0]['total'])) ? $products['product'][0]['total'] : "";
            } else {
                $final_total = (isset($products['total'])) ? strval($products['total']) : '';
            }

            if (!empty($products['product'])) {
                $this->response['error'] = false;
                $this->response['message'] = "Products retrieved successfully !";
                $this->response['min_price'] = (isset($products['min_price']) && !empty($products['min_price'])) ? strval($products['min_price']) : 0;
                $this->response['max_price'] = (isset($products['max_price']) && !empty($products['max_price'])) ? strval($products['max_price']) : 0;
                $this->response['search'] = (isset($_POST['search'])) ? $this->input->post("search", true) : "";
                $this->response['filters'] = (isset($products['filters']) && !empty($products['filters'])) ? $products['filters'] : [];
                $this->response['product_tags'] = (isset($products['product_tags']) && !empty($products['product_tags'])) ? $products['product_tags'] : [];
                $this->response['partner_tags'] = (isset($products['partner_tags']) && !empty($products['partner_tags'])) ? $products['partner_tags'] : [];
                $this->response['total'] = $final_total;
                $this->response['offset'] = (isset($_POST['offset']) && !empty($_POST['offset'])) ? $this->input->post("offset", true) : '0';
                $this->response['data'] = $products['product'];
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Products Not Found !";
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    // 7.get_transactions
    public function get_transactions()
    {
        /*
            user_id:73             
            id: 1001                // { optional}
            type : credit / debit - for wallet // { optional }
            search : Search keyword // { optional }
            limit:25                // { default - 25 } optional
            offset:0                // { default - 0 } optional
            sort: id / date_created // { default - id } optional
            order:DESC/ASC          // { default - DESC } optional
        */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('user_id', 'User ID', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('type', 'Type', 'trim|xss_clean');
        $this->form_validation->set_rules('search', 'Search keyword', 'trim|xss_clean');
        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id']) && !empty(trim($_POST['user_id']))) ? $this->input->post('user_id', true) : "";
            $id = (isset($_POST['id']) && is_numeric($_POST['id']) && !empty(trim($_POST['id']))) ? $this->input->post('id', true) : "";
            $type = (isset($_POST['type']) && !empty(trim($_POST['type']))) ? $this->input->post('type', true) : "";
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $_POST['order'] : 'DESC';
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $_POST['sort'] : 'id';
            $res = $this->transaction_model->get_transactions($id, $user_id, 'wallet', $type, $search, $offset, $limit, $sort, $order);
            $this->response['error'] = !empty($res['data']) ? false : true;
            $this->response['message'] = !empty($res['data']) ? 'Transactions Retrieved Successfully' : 'Transactions does not exists';
            $this->response['total'] = !empty($res['data']) ? $res['total'] : 0;
            $this->response['data'] = !empty($res['data']) ? $res['data'] : [];
        }

        print_r(json_encode($this->response));
    }

    //8. get_statistics
    public function get_statistics()
    {
        /* 
            partner_id:174
        */

        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('partner_id', 'partner ID', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $response['error'] = true;
            $response['message'] = strip_tags(validation_errors());
            $response['data'] = array();
            print_r(json_encode($response));
            return false;
        } else {

            $currency_symbol = get_settings('currency');
            $bulkData = $rows =  $tempRow =  $tempRow1 =  $tempRow2 = array();
            $bulkData['error'] = false;
            $bulkData['message'] = 'Data retrieved successfully';
            $bulkData['currency_symbol'] = !empty($currency_symbol) ? $currency_symbol : '';
            $user_id = $this->input->post('partner_id', true);
            $res = $this->db->select('c.name as name,count(c.id) as counter')->where(['p.status' => '1', 'c.status' => '1', 'p.partner_id' => $user_id])->join('products p', 'p.category_id=c.id')->group_by('c.id')->get('categories c')->result_array();
            foreach ($res as $row) {
                $tempRow['cat_name'][] = $row['name'];
                $tempRow['counter'][] = $row['counter'];
            }

            $rows[] = $tempRow;
            $bulkData['category_wise_food_count'] = $tempRow;

            // overall sale
            $overall_sale = $this->db->select("SUM(sub_total) as overall_sale")->where('partner_id = ' . $user_id)->get('`order_items`')->result_array();
            $overall_sale = !empty($overall_sale[0]['overall_sale']) ? intval($overall_sale[0]['overall_sale']) : 0;
            $tempRow1['overall_sale'] = $overall_sale;

            // daily earnings
            $day_res = $this->db->select("DAY(date_added) as date, SUM(sub_total) as total_sale")
                ->where('date_added >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)')->where(['partner_id' => $user_id])
                ->group_by('day(date_added)')->get('`order_items`')->result_array();
            $day_wise_sales['total_sale'] = array_map('intval', array_column($day_res, 'total_sale'));
            $day_wise_sales['day'] = array_column($day_res, 'date');
            $tempRow1['daily_earnings'] = $day_wise_sales;

            // weekly earnings
            $d = strtotime("today");
            $start_week = strtotime("last sunday midnight", $d);
            $end_week = strtotime("next saturday", $d);
            $start = date("Y-m-d", $start_week);
            $end = date("Y-m-d", $end_week);
            $week_res = $this->db->select("DATE_FORMAT(date_added, '%d-%b') as date, SUM(sub_total) as total_sale")
                ->where("date(date_added) >='$start' and date(date_added) <= '$end' ")->where(['partner_id' => $user_id])
                ->group_by('day(date_added)')->get('`order_items`')->result_array();

            $week_wise_sales['total_sale'] = array_map('intval', array_column($week_res, 'total_sale'));
            $week_wise_sales['week'] = array_column($week_res, 'date');
            $tempRow1['weekly_earnings'] = $week_wise_sales;

            // monthly earnings
            $month_res = $this->db->select('SUM(sub_total) AS total_sale,DATE_FORMAT(date_added,"%b") AS month_name ')->where(['partner_id' => $user_id])
                ->group_by('year(CURDATE()),MONTH(date_added)')
                ->order_by('year(CURDATE()),MONTH(date_added)')
                ->get('`order_items`')->result_array();

            $month_wise_sales['total_sale'] = array_map('intval', array_column($month_res, 'total_sale'));
            $month_wise_sales['month_name'] = array_column($month_res, 'month_name');
            $tempRow1['monthly_earnings'] = $month_wise_sales;
            $rows1[] = $tempRow1;
            $bulkData['earnings'] = $rows1;

            // counts
            $res = $this->transaction_model->get_transactions("", $user_id, 'wallet');
            $tempRow2['transaction_counter'] = !empty($res['data']) ? $res['total'] : 0;
            $tempRow2['order_counter'] = strval(orders_count("", $user_id));
            $tempRow2['pending'] = strval(orders_count("pending", $user_id));
            $tempRow2['confirmed'] = strval(orders_count("confirmed", $user_id));
            $tempRow2['preparing'] = strval(orders_count("preparing", $user_id));
            $tempRow2['out_for_delivery'] = strval(orders_count("out_for_delivery", $user_id));
            $tempRow2['delivered'] = strval(orders_count("delivered", $user_id));
            $tempRow2['cancelled'] = strval(orders_count("cancelled", $user_id));
            $tempRow2['count_products_low_status'] = strval($this->Home_model->count_products_stock_low_status($user_id));
            $tempRow2['count_products_sold_out_status'] = strval($this->Home_model->count_products_availability_status($user_id));
            $tempRow2['product_counter'] = $this->Home_model->count_products($user_id);
            $tempRow2['tag_counter'] = $this->Home_model->count_tags($user_id);
            $tempRow2['user_counter'] = (get_partner_permission($user_id, 'customer_privacy')) ? $this->Home_model->count_new_users() : "0";
            $tempRow2['permissions'] = get_partner_permission($user_id);
            $rows2[] = $tempRow2;
            $bulkData['counts'] = $rows2;
            print_r(json_encode($bulkData));
        }
    }

    //9. forgot_password
    public function forgot_password()
    {
        /* Parameters to be passed
            mobile_no:7894561235            
            new: pass@123
        */


        $this->form_validation->set_rules('mobile_no', 'Mobile No', 'trim|numeric|required|xss_clean|max_length[16]');
        $this->form_validation->set_rules('new', 'New Password', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        $identity_column = $this->config->item('identity', 'ion_auth');
        $res = fetch_details(['mobile' => $_POST['mobile_no']], 'users');
        if (!empty($res)) {
            $identity = ($identity_column  == 'email') ? $res[0]['email'] : $res[0]['mobile'];
            if (!$this->ion_auth->reset_password($identity, $_POST['new'])) {
                $response['error'] = true;
                $response['message'] = strip_tags($this->ion_auth->messages());;
                $response['data'] = array();
                echo json_encode($response);
                return false;
            } else {
                $response['error'] = false;
                $response['message'] = 'Reset Password Successfully';
                $response['data'] = array();
                echo json_encode($response);
                return false;
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'User does not exists !';
            $response['data'] = array();
            echo json_encode($response);
            return false;
        }
    }

    //10. delete_order
    public function delete_order()
    {
        /*
            order_id:1
        */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('order_id', 'Order ID', 'trim|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $order_id = $_POST['order_id'];
            delete_details(['id' => $order_id], 'orders');
            delete_details(['order_id' => $order_id], 'order_items');

            $this->response['error'] = false;
            $this->response['message'] = 'Order deleted successfully';
            $this->response['data'] = array();
        }
        print_r(json_encode($this->response));
    }

    //12. verify_user

        public function verify_user()
    {
        /* Parameters to be passed
            mobile: 9874565478
            email: test@gmail.com // { optional }
            is_forgot_password: 1
        */

        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return;
        } else {
            if (isset($_POST['is_forgot_password'])  && ($_POST['is_forgot_password'] == 1) && !is_exist(['mobile' => $_POST['mobile']], 'users')) {
                $this->response['error'] = true;
                $this->response['message'] = 'Mobile is not register yet !';
                $this->response['data'] = array();
                print_r(json_encode($this->response));
                return;
            } else {
                $mobile = $this->input->post('mobile');
                $auth_settings = get_settings('authentication_settings', true);
                if ($auth_settings['authentication_method'] == "sms") {
                    $otps = fetch_details(['mobile' => $mobile],'otps');


                    $query = $this->db->select(' * ')->where('id', $otps[0]['id'])->get('otps')->result_array();
  
                    $otp = random_int(100000, 999999);
                    $data = set_user_otp($mobile, $otp);
                    $this->response['error'] = false;
                    $this->response['message'] = 'Ready to sent OTP request from sms!';
                    $this->response['data'] = array();
                    print_r(json_encode($this->response));
                    return;
                }
            }
            if (isset($_POST['mobile']) && is_exist(['mobile' => $_POST['mobile']], 'users')) {
                $user_id = fetch_details(['mobile' => $_POST['mobile']],'users','id');

                //Check if this mobile no. is registered as a partner or not.
                if (!$this->ion_auth->in_group('partner', $user_id[0]['id'])) {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Mobile number / email could not be found!';
                    print_r(json_encode($this->response));
                    return;
                } else {
                    $this->response['error'] = false;
                    $this->response['message'] = 'Mobile number is registered. ';
                    print_r(json_encode($this->response));
                    return;
                }
            }
            if (isset($_POST['email']) && is_exist(['email' => $_POST['email']], 'users')) {
                $this->response['error'] = false;
                $this->response['message'] = 'Email is registered.';
                print_r(json_encode($this->response));
                return;
            }

            $this->response['error'] = true;
            $this->response['message'] = 'Mobile number / email could not be found!';
            print_r(json_encode($this->response));
            return;
        }
    }

     //verify_otp
    public function verify_otp()
    {
        /* 
        otp: 123456
        phone number: 9876543210
        */

        $mobile = $this->input->post('mobile');
        $auth_settings = get_settings('authentication_settings', true);
        if ($auth_settings['authentication_method'] == "sms") {
            $otps = fetch_details(['mobile' => $mobile],'otps');
            $time = $otps[0]['created_at'];
            $time_expire = checkOTPExpiration($time);
            if ($time_expire['error'] == 1) {
                $response['error'] = true;
                $response['message'] = $time_expire['message'];
                echo json_encode($response);
                return false;
            }
            if (($otps[0]['otp'] != $_POST['otp'])) {
                $response['error'] = true;
                $response['message'] = "OTP not valid , check again ";
                echo json_encode($response);
                return false;
            } else {
                update_details(['varified' => 1], ['mobile' => $mobile], 'otps');
            }
        }
        $this->response['error'] = false;
        $this->response['message'] = 'Otp Verified Successfully';
        $this->response['data'] = array();
       
        print_r(json_encode($this->response));
       
    }

    //resend_otp
    public function resend_otp()
    {
        /*
        mobile:9876543210
        */


        $mobile = $this->input->post('mobile');
        $auth_settings = get_settings('authentication_settings', true);
        if ($auth_settings['authentication_method'] == "sms") {
            $otps = fetch_details(['mobile' => $mobile],'otps');


            $query = $this->db->select(' * ')->where('id', $otps[0]['id'])->get('otps')->result_array();

            $otp = random_int(100000, 999999);
            $data = set_user_otp($mobile, $otp);
            $this->response['error'] = false;
            $this->response['message'] = 'Ready to sent OTP request from sms!';
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        }
        // }
    }

    // 13.get_settings
    public function get_settings()
    {
        /*      
            user_id:  15 { optional }
        */

        $type = 'all';
        $this->form_validation->set_rules('type', 'Setting Type', 'trim|xss_clean');


        if (!$this->form_validation->run()) {

            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
        } else {
            $general_settings = array();

            $settings = [
                'logo' => 0,
                'partner_privacy_policy' => 0,
                'partner_terms_conditions' => 0,
                'fcm_server_key' => 0,
                'contact_us' => 0,
                'about_us' => 0,
                'currency' => 0,
                'user_data' => 0,
                'system_settings' => 1,
                'authentication_settings' => 0,
            ];
            if (!empty($settings)) {
                foreach ($settings as $type => $isjson) {
                    $general_settings[$type] = [];
                    $settings_res = get_settings($type, $isjson);
                    
                    if ($type == 'logo') {
                        $settings_res = base_url() . $settings_res;
                    }

                    if ($type == 'user_data') {
                        if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
                            $cart_total_response = get_cart_total($_POST['user_id'], false, 0);
                            $settings_res = fetch_users($_POST['user_id']);
                            $settings_res['cart_total_items'] = (isset($cart_total_response[0]['cart_count']) && $cart_total_response[0]['cart_count'] > 0) ? $cart_total_response[0]['cart_count'] : '0';
                            $settings_res = $settings_res;
                        } else {
                            $settings_res = "";
                        }
                    }
                    if ($type == 'system_settings') {
                        unset($settings_res['google_map_api_key']);
                        unset($settings_res['google_map_javascript_api_key']);
                    }
                    array_push($general_settings[$type], $settings_res);
                }

                $general_settings['privacy_policy'] = $general_settings['partner_privacy_policy'];
                unset($general_settings['partner_privacy_policy']);
                $general_settings['terms_conditions'] = $general_settings['partner_terms_conditions'];
                unset($general_settings['partner_terms_conditions']);
                $authentication_settings = json_decode($general_settings['authentication_settings'][0],true);
                unset($general_settings['authentication_settings']);

                $this->response['error'] = false;
                $this->response['message'] = 'Settings retrieved successfully';
                $this->response['authentication_mode'] = ($authentication_settings['authentication_method'] == 'sms') ? 1 : 0;
                $this->response['data'] = $general_settings;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Settings Not Found';
                $this->response['data'] = array();
            }
            print_r(json_encode($this->response));
        }
    }

    // 14. update_fcm
    public function update_fcm()
    {

        /* Parameters to be passed
             user_id:12
             fcm_id: FCM_ID
             device_type: android/ios
         */

        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('fcm_id', 'FCM Id', 'trim|required|xss_clean');
        $this->form_validation->set_rules('device_type', 'Device Type', 'trim|xss_clean');


        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        $user_res = update_details(['fcm_id' => $this->input->post('fcm_id', true)], ['id' => $this->input->post('user_id', true), 'platform' => $_POST['device_type']], 'users');

        if ($user_res) {
            $response['error'] = false;
            $response['message'] = 'Updated Successfully';
            $response['data'] = array();
            echo json_encode($response);
            return false;
        } else {
            $response['error'] = true;
            $response['message'] = 'Updation Failed !';
            $response['data'] = array();
            echo json_encode($response);
            return false;
        }
    }

    //15.get_cities
    public function get_cities()
    {
        /*
            sort:               // { c.name / c.id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // {optional} 
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
       */

        $this->form_validation->set_rules('sort', 'sort', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');
        $this->form_validation->set_rules('search', 'search', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'c.name';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'ASC';
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;

            $result = $this->Area_model->get_cities($sort, $order, $search, $limit, $offset);
            print_r(json_encode($result));
        }
    }

    //16. get_taxes
    public function get_taxes()
    {
        if (!verify_tokens()) {
            return false;
        }

        $types = fetch_details(['status' => 1], 'taxes');
        if (!empty($types)) {
            for ($i = 0; $i < count($types); $i++) {
                $types[$i] = output_escaping($types[$i]);
            }
        }
        $this->response['error'] = false;
        $this->response['message'] = 'Taxes fetched successfully';
        $this->response['data'] = $types;
        print_r(json_encode($this->response));
    }

    //17. send_withdrawal_request
    public function send_withdrawal_request()
    {
        /* 
            user_id:174
            payment_address: 12343535
            amount: 56
        */

        // if (!verify_tokens()) {
        //     return false;
        // }
        $this->form_validation->set_rules('user_id', 'User Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('payment_address', 'Payment Address', 'trim|required|xss_clean');
        $this->form_validation->set_rules('amount', 'Amount', 'trim|required|xss_clean|numeric|greater_than[0]');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
        } else {
            $user_id = $this->input->post('user_id', true);
            $payment_address = $this->input->post('payment_address', true);
            $amount = $this->input->post('amount', true);
            $userData = fetch_details(['id' => $_POST['user_id']], 'users', 'balance');

            if (!empty($userData)) {
                if ($_POST['amount'] <= $userData[0]['balance']) {
                    $data = [
                        'user_id' => $user_id,
                        'payment_address' => $payment_address,
                        'payment_type' => 'partner',
                        'amount_requested' => $amount,
                    ];

                    if (insert_details($data, 'payment_requests')) {
                        $this->Partner_model->update_balance($amount, $user_id, 'deduct');

                        $userData = fetch_details(['id' => $_POST['user_id']], 'users', 'balance');
                        $this->response['error'] = false;
                        $this->response['message'] = 'Withdrawal Request Sent Successfully';
                        $this->response['data'] = number_format($userData[0]['balance'], 2);
                    } else {
                        $this->response['error'] = true;
                        $this->response['message'] = 'Cannot sent Withdrawal Request.Please Try again later.';
                        $this->response['data'] = array();
                    }
                } else {
                    $this->response['error'] = true;
                    $this->response['message'] = 'You don\'t have enough balance to sent the withdraw request.';
                    $this->response['data'] = array();
                }

                print_r(json_encode($this->response));
            }
        }
    }

    //18. get_withdrawal_request
    public function get_withdrawal_request()
    {
        /* 
            user_id:15  
            limit:10  {optional}
            offset:10  {optional}
            sort:id              // { optional } 
            order:DESC/ASC      // { default - DESC } optional
        */

        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('user_id', 'User Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('limit', 'Limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'Offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('sort', 'sort', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
        } else {
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'id';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';

            $userData = fetch_details(['user_id' => $this->input->post('user_id', true)], 'payment_requests', '*', $limit, $offset, $sort, $order);
            $userData = array_map(function ($value) {
                return $value === NULL ? "" : $value;
            }, $userData);

            $this->response['error'] = false;
            $this->response['message'] = 'Withdrawal Request Retrieved Successfully';
            $this->response['total'] = strval(count($userData));
            $this->response['data'] = $userData;
            print_r(json_encode($this->response));
        }
    }

    //19. add_attributes
    public function add_attributes()
    {
        /*
            name:color 
            attribute_values:[{"value":"value1"},{"value":"value2"},{"value":"value3"}]       //{JSON ARRAY- index(value) must be same}
       */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('name', 'Attribute Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('attribute_values', 'Attribute Values', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response["data"] = array();
            print_r(json_encode($this->response));
            return false;
        } else {

            $name = (isset($_POST['name']) && !empty($_POST['name'])) ? $this->input->post("name", true) : "";
            $attribute_values = (isset($_POST['attribute_values']) && !empty($_POST['attribute_values'])) ? $this->input->post("attribute_values", true) : "";

            $data = array(
                'name' => $name,
                'attribute_values' => $attribute_values
            );

            if (is_exist(['name' => $name], 'attributes')) {
                $response["error"]   = true;
                $response["message"] = "This Attribute Already Exist.";
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }
            $this->Attribute_model->add_attributes($data);
            $this->response['error'] = false;
            $this->response['message'] = 'Attribute Added Successfully';
            $this->response["data"] = array();
            print_r(json_encode($this->response));
        }
    }

    //20. edit_attributes
    public function edit_attributes()
    {
        /*
            edit_attribute_id:1
            attribute_value_ids:1,2,3,0         // {provide zero if any new value added in edited attribute}
            name:color 
            value_name:red,blue,green,new_value   // {provide new attribute value if new added in edited attribute}
       */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('edit_attribute_id', 'Attribute ID', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('attribute_value_ids', 'Attribute Value IDs', 'trim|required|xss_clean');
        $this->form_validation->set_rules('name', 'Attribute Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('value_name', 'Value Name', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {

            $edit_attribute_id = $this->input->post("edit_attribute_id", true);
            $attribute_value_ids = $this->input->post("attribute_value_ids", true);
            $value_name = $this->input->post("value_name", true);
            $name = $this->input->post("name", true);

            $data = array(
                'edit_attribute_id' => $edit_attribute_id,
                'value_id' => explode(",", $attribute_value_ids),
                'value_name' => explode(",", $value_name),
                'name' => $name,
            );

            if (is_exist(['name' => $name], 'attributes', $edit_attribute_id)) {
                $response["error"]   = true;
                $response["message"] = "This Attribute Already Exist.";
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }
            if ($this->Attribute_model->add_attributes($data)) {
                $response["error"]   = true;
                $response["message"] = "This combination already exist ! Please provide a new combination";
                $response["data"] = array();
                echo json_encode($response);
                return false;
            } else {
                $this->response['error'] = false;
                $this->response['message'] = "Attribute Updated Successfully";
                $this->response["data"] = array();
                print_r(json_encode($this->response));
                return false;
            }
        }
    }

    //21. get_attributes
    public function get_attributes()
    {
        /*
            sort: name              // { name / id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // {optional} 
            limit:10  {optional}
            offset:10  {optional}
       */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('sort', 'sort', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('search', 'search', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'Limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'Offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'name';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'ASC';
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";
            $limit = ($this->input->post('limit', true)) ? $this->input->post('limit', true) : NULL;
            $offset = ($this->input->post('offset', true)) ? $this->input->post('offset', true) : NULL;
            $result = $this->Attribute_model->get_attributes($sort, $order, $search, $offset, $limit, true);
            print_r(json_encode($result));
        }
    }

    //22. get_attribute_values
    public function get_attribute_values()
    {
        /*
            attribute_id:1  // {optional}
            sort:a.name               // { a.name / a.id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // {optional} 
            limit:10  {optional}
            offset:10  {optional}
       */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('sort', 'sort', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('search', 'search', 'trim|xss_clean');
        $this->form_validation->set_rules('attribute_id', 'attribute id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('limit', 'Limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'Offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'a.name';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'ASC';
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";
            $limit = ($this->input->post('limit', true)) ? $this->input->post('limit', true) : NULL;
            $offset = ($this->input->post('offset', true)) ? $this->input->post('offset', true) : NULL;
            $attribute_id = (isset($_POST['attribute_id']) && !empty(trim($_POST['attribute_id']))) ? $this->input->post('attribute_id', true) : "";
            $result = $this->Attribute_model->get_attribute_value($sort, $order, $search, $attribute_id, $offset, $limit, true);
            print_r(json_encode($result));
        }
    }

    // 23. add_products
    public function add_products()
    {
        /*
            pro_input_name: product name
            partner_id:1255
            product_category_id:99
            short_description: description
            product_add_ons:  [{"title":"add_on1","description":"descritpion","price":"40","calories":"123","status":1},{"title":"add_on2","description":"description2","price":"43","calories":"1234","status":1}]
            tags:1,2,3                               //{pass Tag Ids comma saprated}
            is_prices_inclusive_tax:0                //{1: inclusive | 0: exclusive}
            cod_allowed:1                            //{ 1:allowed | 0:not-allowed }{default:1}
            available_time : 1                       //{ 1:need-time | 0:no-time needed }{default:0}
            product_start_time : 16:00:00
            product_end_time : 17:00:00
            is_cancelable:1                          //{optional}{1:cancelable | 0:not-cancelable}{default:0}
            cancelable_till:pending                  //{pending,confirmed,preparing,out_for_delivery}{required if "is_cancelable" is 1}
            pro_input_image:file  
            indicator:1                              //{ 0 - none | 1 - veg | 2 - non-veg }
            highlights:new,fresh                     //{optional}
            calories:123                             //{optional}
            total_allowed_quantity:100               //{optional}
            minimum_order_quantity:12
            attribute_values:1,2,3,4,5               //{comma saprated attributes values ids if set}
            --------------------------------------------------------------------------------
            till above same params
            --------------------------------------------------------------------------------
            --------------------------------------------------------------------------------
            common param for simple and variable product
            --------------------------------------------------------------------------------          
            product_type:simple_product | variable_product  
            variant_stock_level_type:product_level
            
            if(product_type == variable_product):
                variants_ids:3 5,4 5,1 2
                variant_price:100,200
                variant_special_price:90,190
                variant_images:files              //{optional}

                total_stock_variant_type:100     //{if (variant_stock_level_type == product_level)}
                variant_status:1                 //{if (variant_stock_level_type == product_level)}

            if(product_type == simple_product):
                simple_product_stock_status:null|0|1   {1=in stock | 0=out stock}
                simple_price:100
                simple_special_price:90
                product_total_stock:100             {optional}
                variant_stock_status: 0             {optional}//{0 =>'Simple_Product_Stock_Active' 1 => "Product_Level"	}
                variant_status:1
       */
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return false;
            exit();
        }
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('pro_input_name', 'Product Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('indicator', 'Product Indicator', 'trim|required|xss_clean');
        $this->form_validation->set_rules('product_category_id', 'Product Category', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('short_description', 'Short Description', 'trim|required|xss_clean');
        $this->form_validation->set_rules('pro_input_image', 'Product Image', 'trim|xss_clean', array('required' => 'Image is required'));
        $this->form_validation->set_rules('tags', 'Food Tags', 'trim|xss_clean'); // tag ids->1,2,3
        $this->form_validation->set_rules('attribute_values', 'Attribute Values', 'trim|xss_clean'); // tag ids->1,2,3
        $this->form_validation->set_rules('product_type', 'Product type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('total_allowed_quantity', 'Total Allowed Quantity', 'trim|xss_clean');
        $this->form_validation->set_rules('calories', 'calories', 'trim|xss_clean|numeric');
        $this->form_validation->set_rules('minimum_order_quantity', 'Minimum Order Quantity', 'trim|xss_clean');
        $this->form_validation->set_rules('product_type', 'Product Type', 'trim|required|xss_clean|in_list[simple_product,variable_product]');
        $this->form_validation->set_rules('variant_stock_level_type', 'Product Lavel', 'trim|xss_clean|in_list[product_level]');

        $_POST['variant_price'] = (isset($_POST['variant_price']) && !empty($_POST['variant_price'])) ?  explode(",", $this->input->post('variant_price', true)) : NULL;
        $_POST['variant_special_price'] = (isset($_POST['variant_special_price']) && !empty($_POST['variant_special_price'])) ?  explode(",", $this->input->post('variant_special_price', true)) : NULL;
        $_POST['variants_ids'] = (isset($_POST['variants_ids']) && !empty($_POST['variants_ids'])) ?  explode(",", $this->input->post('variants_ids', true)) : NULL;
        $_POST['variant_total_stock'] = (isset($_POST['variant_total_stock']) && !empty($_POST['variant_total_stock'])) ?  explode(",", $this->input->post('variant_total_stock', true)) : NULL;
        $_POST['variant_level_stock_status'] = (isset($_POST['variant_level_stock_status']) && !empty($_POST['variant_level_stock_status'])) ?  explode(",", $this->input->post('variant_level_stock_status', true)) : NULL;

        if (isset($_POST['is_cancelable']) && $_POST['is_cancelable'] == '1') {
            $this->form_validation->set_rules('cancelable_till', 'Till which status', 'trim|required|xss_clean|in_list[pending,confirmed,preparing,out_for_delivery]');
        }

        if (isset($_POST['cod_allowed'])) {
            $this->form_validation->set_rules('cod_allowed', 'COD allowed', 'trim|xss_clean');
        }
        if (isset($_POST['is_spicy'])) {
            $this->form_validation->set_rules('is_spicy', 'Is Spicy', 'trim|xss_clean');
        }
        if (isset($_POST['is_prices_inclusive_tax'])) {
            $this->form_validation->set_rules('is_prices_inclusive_tax', 'Tax included in prices', 'trim|xss_clean');
        }

        // If product type is simple			
        if (isset($_POST['product_type']) && $_POST['product_type'] == 'simple_product') {
            $this->form_validation->set_rules('simple_price', 'Price', 'trim|required|numeric|greater_than_equal_to[' . $this->input->post('simple_special_price') . ']|xss_clean');
            $this->form_validation->set_rules('simple_special_price', 'Special Price', 'trim|numeric|less_than_equal_to[' . $this->input->post('simple_price') . ']|xss_clean');

            if (isset($_POST['simple_product_stock_status']) && in_array($_POST['simple_product_stock_status'], array('0', '1'))) {
                $this->form_validation->set_rules('product_total_stock', 'Total Stock', 'trim|required|numeric|xss_clean');
                $this->form_validation->set_rules('simple_product_stock_status', 'Stock Status', 'trim|required|numeric|xss_clean');
            }
        } elseif (isset($_POST['product_type']) && $_POST['product_type'] == 'variable_product') { //If product type is variant	
            if (isset($_POST['variant_stock_status']) && $_POST['variant_stock_status'] == '0') {
                if ($_POST['variant_stock_level_type'] == "product_level") {
                    $this->form_validation->set_rules('total_stock_variant_type', 'Total Stock', 'trim|required|xss_clean');
                    $this->form_validation->set_rules('variant_stock_status', 'Stock Status', 'trim|required|xss_clean');
                    if (isset($_POST['variant_price']) && isset($_POST['variant_special_price'])) {
                        foreach ($_POST['variant_price'] as $key => $value) {
                            $this->form_validation->set_rules('variant_price[' . $key . ']', 'Price', 'trim|required|numeric|xss_clean|greater_than_equal_to[' . $this->input->post('variant_special_price[' . $key . ']') . ']');
                            $this->form_validation->set_rules('variant_special_price[' . $key . ']', 'Special Price', 'trim|numeric|xss_clean|less_than_equal_to[' . $this->input->post('variant_price[' . $key . ']') . ']');
                        }
                    } else {
                        $this->form_validation->set_rules('variant_price', 'Price', 'trim|required|numeric|xss_clean|greater_than_equal_to[' . $this->input->post('variant_special_price') . ']');
                        $this->form_validation->set_rules('variant_special_price', 'Special Price', 'trim|numeric|xss_clean|less_than_equal_to[' . $this->input->post('variant_price') . ']');
                    }
                }
            } else {
                if (isset($_POST['variant_price']) && isset($_POST['variant_special_price'])) {
                    foreach ($_POST['variant_price'] as $key => $value) {
                        $this->form_validation->set_rules('variant_price[' . $key . ']', 'Price', 'trim|required|numeric|xss_clean|greater_than_equal_to[' . $this->input->post('variant_special_price[' . $key . ']') . ']');
                        $this->form_validation->set_rules('variant_special_price[' . $key . ']', 'Special Price', 'trim|numeric|xss_clean|less_than_equal_to[' . $this->input->post('variant_price[' . $key . ']') . ']');
                    }
                } else {
                    $this->form_validation->set_rules('variant_price', 'Price', 'trim|required|numeric|xss_clean|greater_than_equal_to[' . $this->input->post('variant_special_price') . ']');
                    $this->form_validation->set_rules('variant_special_price', 'Special Price', 'trim|numeric|xss_clean|less_than_equal_to[' . $this->input->post('variant_price') . ']');
                }
            }
        }

     
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
        } else {
            if (isset($_POST['product_add_ons']) && $_POST['product_add_ons'] != '') {
                $_POST['product_add_ons'] = json_decode($_POST['product_add_ons'], 1);
            }
            if (isset($_POST['tags']) && $_POST['tags'] != '') {
                $_POST['tags'] = explode(",", $_POST['tags']);
            }
            $this->Product_model->add_product($_POST);
            $this->response['error'] = false;
            $this->response['message'] = 'Product Added Successfully';
            print_r(json_encode($this->response));
        }
    }
    // 24. get_media
    public function get_media()
    {
        /* 
            partner_id:1255            // {optional}
            limit:25            // { default - 25 } optional
            offset:0            // { default - 0 } optional
            sort:               // { id } optional
            order:DESC/ASC      // { default - DESC } optional
            search:value        // {optional} 
            type:image          // {documents,spreadsheet,archive,video,audio,image}
        */

        if (!verify_tokens()) {
            return false;
        }

        $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
        $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
        $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'id';
        $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';
        $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : '';
        $type = (isset($_POST['type']) && !empty(trim($_POST['type']))) ? $this->input->post('type', true) : '';
        $partner_id = (isset($_POST['partner_id']) && !empty(trim($_POST['partner_id']))) ? $this->input->post('partner_id', true) : '';

        $this->form_validation->set_rules('partner_id', 'partner id', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $this->media_model->get_media($limit, $offset, $sort, $order, $search, $type, $partner_id);
        }
    }

    // 25. get_partner_details
    public function get_partner_details()
    {
        /* Parameters to be passed
            id:28
        */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('id', 'Id', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }
        $working_time = [];
        $id = $this->input->post('id', true);
        $restro_filter['id'] = $id;
        $restro_filter['ignore_status'] = true;
        $restro_data = fetch_partners($restro_filter);
        if (!empty($restro_data['data'])) {
            unset($restro_data['data'][0]['partner_cook_time']);
            $working_time = fetch_details(["partner_id" => $id], "partner_timings");
            $restro_data['data'][0]['partner_working_time'] = $working_time;
        }
        $response['error'] = $restro_data['error'];
        $response['message'] = $restro_data['message'];
        $response['data'] = $restro_data['data'];
        print_r(json_encode($response));
        return false;
    }

    // 26. update_partner
    public function update_partner()
    {
        /*
            id:34                                 {partner_id}(pass when update profile)
            fcm_id:FCM Id
            device_type:android/ios

        restro details:
            partner_name:asd   
            type:1                                {1:veg | 2:non-Veg | 3:Both}
            profile:file                          // {pass if want to change}
            status:1                              {1:active | 0: deactive} (when register -> pass status:2 ( Not-Approved))
            city_id:1  
            cooking_time:20                       {in minutes}   
            working_time:[{"day":"Sunday","opening_time":"11:02:00","closing_time":"22:04:00","is_open":1},{"day":"Tuesday","opening_time":"19:20","closing_time":"18:21","is_open":1}]
            address: restro address
            address_proof:file                    // {pass if want to change}
            latitude:123464
            longitude:234535
            gallary:multiple images from media    {optional}  
            description:asd                       {optional}
            restro_tags:1,2,3                     {optional}  {tag_ids comma saprated}

            delivery_orders:1|0     
            restro_profile:1|0

        restro owner details
            name:asd
            mobile:123456789
            email:asd@gmail.com
            password:password               // {pass if restro register}
            old:12345                       //{if want to change password}
            new:345234                      //{if want to change password}
            national_identity_card:file     // {pass if want to change}
            tax_name:GST
            tax_number:GSTIN4565
            account_number:sdfv             {optional}
            account_name:asd                {optional}
            bank_code:ASD                   {optional}
            bank_name:sdf                   {optional}
            pan_number:ad                   {optional}       

        */
        if (isset($_POST['id'])) {
           
        }
        $identity_column = $this->config->item('identity', 'ion_auth');
        $identity = $this->session->userdata('identity');
        if ($identity_column == 'email') {
            $this->form_validation->set_rules('email', 'Email', 'required|xss_clean|trim|valid_email');
        } else {
            $this->form_validation->set_rules('mobile', 'Mobile', 'required|xss_clean|trim|numeric');
        }
        // validate owner details
        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', 'Mail', 'trim|required|xss_clean|valid_email');
        $this->form_validation->set_rules('mobile', 'Mobile', 'trim|required|xss_clean|min_length[5]');
        if (!isset($_POST['id'])) {
            $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
            $this->form_validation->set_rules('fcm_id', 'FCM ID', 'trim|xss_clean');
            $this->form_validation->set_rules('device_type', 'Device Type', 'trim|xss_clean');
        }
        if (!empty($_POST['old']) || !empty($_POST['new'])) {
            $this->form_validation->set_rules('old', $this->lang->line('change_password_validation_old_password_label'), 'required');
            $this->form_validation->set_rules('new', $this->lang->line('change_password_validation_new_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']');
        }
        $this->form_validation->set_rules('working_time', 'Working Days', 'trim|xss_clean');
        $this->form_validation->set_rules('cooking_time', 'cooking_time', 'trim|required|xss_clean|numeric');
        $this->form_validation->set_rules('restro_tags', 'Restro Tags', 'trim|xss_clean');

        // validate restro details
        $this->form_validation->set_rules('partner_name', 'partner Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('description', 'Description', 'trim|required|xss_clean');
        $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');
        $this->form_validation->set_rules('latitude', 'Latitude', 'trim|xss_clean');
        $this->form_validation->set_rules('longitude', 'Longitude', 'trim|xss_clean');
        $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('tax_name', 'Tax Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('tax_number', 'Tax Number', 'trim|required|xss_clean');
        $this->form_validation->set_rules('self_pickup', 'Self Pickup', 'trim|xss_clean');
        $this->form_validation->set_rules('delivery_orders', 'Delivery Orders', 'trim|xss_clean');

        // bank details
        $this->form_validation->set_rules('account_number', 'Account Number', 'trim|xss_clean');
        $this->form_validation->set_rules('account_name', 'Account Name', 'trim|xss_clean');
        $this->form_validation->set_rules('bank_code', 'Bank Code', 'trim|xss_clean');
        $this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|xss_clean');
        $this->form_validation->set_rules('pan_number', 'Pan Number', 'trim|xss_clean');

        // licence details
        $this->form_validation->set_rules('licence_name', 'Licence Name', 'trim|xss_clean');
        $this->form_validation->set_rules('licence_code', 'Licence Code', 'trim|xss_clean');
        $this->form_validation->set_rules('licence_proof', 'Licence Proof', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
             if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $this->response['error'] = true;
                $this->response['message'] = DEMO_VERSION_MSG;
                echo json_encode($this->response);
                return false;
                exit();
            }
            $id = $this->input->post('id', true);
            $partner_data_id = fetch_details(['user_id' => $id], 'partner_data', 'id,address_proof,national_identity_card,profile,licence_proof,commission');

            if ((empty($_POST['delivery_orders']) || $_POST['delivery_orders'] == "") && (empty($_POST['self_pickup']) || $_POST['self_pickup'] == "")) {
                $this->response['error'] = true;
                $this->response['message'] = "Both order receive type should not be disable.";
                print_r(json_encode($this->response));
                return;
                exit();
            }
            if (!file_exists(FCPATH . RESTRO_DOCUMENTS_PATH)) {
                mkdir(FCPATH . RESTRO_DOCUMENTS_PATH, 0777);
            }

            //process licence proof

            $temp_licence_proof = $licence_doc = array();
            $licence_files = $_FILES;
            $licence_error = "";
            $config = [
                'upload_path' =>  FCPATH . RESTRO_DOCUMENTS_PATH,
                'allowed_types' => 'jpg|png|jpeg|gif',
                'max_size' => 8000,
            ];
            if (isset($licence_files['licence_proof']) && !empty($licence_files['licence_proof']['name']) && isset($licence_files['licence_proof']['name'])) {
                $other_image_cnt = count($_FILES['licence_proof']['name']);
                $other_img = $this->upload;
                $other_img->initialize($config);

                for ($i = 0; $i < $other_image_cnt; $i++) {
                    if (!empty($licence_files['licence_proof']['name'][$i])) {
                        $_FILES['temp_image']['name'] = $licence_files['licence_proof']['name'][$i];
                        $_FILES['temp_image']['type'] = $licence_files['licence_proof']['type'][$i];
                        $_FILES['temp_image']['tmp_name'] = $licence_files['licence_proof']['tmp_name'][$i];
                        $_FILES['temp_image']['error'] = $licence_files['licence_proof']['error'][$i];
                        $_FILES['temp_image']['size'] = $licence_files['licence_proof']['size'][$i];
                        if (!$other_img->do_upload('temp_image')) {
                            $licence_error = 'Images :' . $licence_error . ' ' . $other_img->display_errors();
                        } else {
                            $temp_licence_proof = $other_img->data();
                            resize_review_images($temp_licence_proof, FCPATH . RESTRO_DOCUMENTS_PATH);
                            $licence_doc[$i]  = RESTRO_DOCUMENTS_PATH . $temp_licence_proof['file_name'];
                        }
                    } else {
                        $_FILES['temp_image']['name'] = $licence_files['licence_proof']['name'][$i];
                        $_FILES['temp_image']['type'] = $licence_files['licence_proof']['type'][$i];
                        $_FILES['temp_image']['tmp_name'] = $licence_files['licence_proof']['tmp_name'][$i];
                        $_FILES['temp_image']['error'] = $licence_files['licence_proof']['error'][$i];
                        $_FILES['temp_image']['size'] = $licence_files['licence_proof']['size'][$i];
                        if (!$other_img->do_upload('temp_image')) {
                            $licence_error = $other_img->display_errors();
                        }
                    }
                }
                //Deleting Uploaded Images if any overall error occured
                if ($licence_error != NULL || !$this->form_validation->run()) {
                    if (isset($licence_doc) && !empty($licence_doc || !$this->form_validation->run())) {
                        foreach ($licence_doc as $key => $val) {
                            unlink(FCPATH . RESTRO_DOCUMENTS_PATH . $licence_doc[$key]);
                        }
                    }
                }
            }

            if ($licence_error != NULL) {
                $this->response['error'] = true;
                $this->response['csrfName'] = $this->security->get_csrf_token_name();
                $this->response['csrfHash'] = $this->security->get_csrf_hash();
                $this->response['message'] =  $licence_error;
                print_r(json_encode($this->response));
                return;
            }

            //process store logo
            $temp_array_logo = $profile_doc = array();
            $logo_files = $_FILES;
            $profile_error = "";
            $config = [
                'upload_path' =>  FCPATH . RESTRO_DOCUMENTS_PATH,
                'allowed_types' => 'jpg|png|jpeg|gif',
                'max_size' => 8000,
            ];
            if (isset($logo_files['profile']) && !empty($logo_files['profile']['name']) && isset($logo_files['profile']['name'])) {
                $other_img = $this->upload;
                $other_img->initialize($config);

                if (isset($_POST['id']) && !empty($_POST['id']) && isset($partner_data_id[0]['profile']) && !empty($partner_data_id[0]['profile'])) {
                    $old_logo = explode('/', $partner_data_id[0]['profile']);
                    delete_images(RESTRO_DOCUMENTS_PATH, $old_logo[2]);
                }

                if (!empty($logo_files['profile']['name'])) {

                    $_FILES['temp_image']['name'] = $logo_files['profile']['name'];
                    $_FILES['temp_image']['type'] = $logo_files['profile']['type'];
                    $_FILES['temp_image']['tmp_name'] = $logo_files['profile']['tmp_name'];
                    $_FILES['temp_image']['error'] = $logo_files['profile']['error'];
                    $_FILES['temp_image']['size'] = $logo_files['profile']['size'];
                    if (!$other_img->do_upload('temp_image')) {
                        $profile_error = 'Images :' . $profile_error . ' ' . $other_img->display_errors();
                    } else {
                        $temp_array_logo = $other_img->data();
                        resize_review_images($temp_array_logo, FCPATH . RESTRO_DOCUMENTS_PATH);
                        $profile_doc  = RESTRO_DOCUMENTS_PATH . $temp_array_logo['file_name'];
                    }
                } else {
                    $_FILES['temp_image']['name'] = $logo_files['profile']['name'];
                    $_FILES['temp_image']['type'] = $logo_files['profile']['type'];
                    $_FILES['temp_image']['tmp_name'] = $logo_files['profile']['tmp_name'];
                    $_FILES['temp_image']['error'] = $logo_files['profile']['error'];
                    $_FILES['temp_image']['size'] = $logo_files['profile']['size'];
                    if (!$other_img->do_upload('temp_image')) {
                        $profile_error = $other_img->display_errors();
                    }
                }
                //Deleting Uploaded Images if any overall error occured
                if ($profile_error != NULL || !$this->form_validation->run()) {
                    if (isset($profile_doc) && !empty($profile_doc || !$this->form_validation->run())) {
                        foreach ($profile_doc as $key => $val) {
                            unlink(FCPATH . RESTRO_DOCUMENTS_PATH . $profile_doc[$key]);
                        }
                    }
                }
            }

            if ($profile_error != NULL) {
                $this->response['error'] = true;
                $this->response['message'] =  $profile_error;
                print_r(json_encode($this->response));
                return;
            }

            //process national_identity_card
            $temp_array_id_card = $id_card_doc = array();
            $id_card_files = $_FILES;
            $id_card_error = "";
            $config = [
                'upload_path' =>  FCPATH . RESTRO_DOCUMENTS_PATH,
                'allowed_types' => 'jpg|png|jpeg|gif',
                'max_size' => 8000,
            ];
            if (isset($id_card_files['national_identity_card']) &&  !empty($id_card_files['national_identity_card']['name']) && isset($id_card_files['national_identity_card']['name'])) {
                $other_img = $this->upload;
                $other_img->initialize($config);

                if (isset($_POST['id']) && !empty($_POST['id']) && isset($partner_data_id[0]['national_identity_card']) && !empty($partner_data_id[0]['national_identity_card'])) {
                    $old_logo = explode('/', $partner_data_id[0]['national_identity_card']);
                    delete_images(RESTRO_DOCUMENTS_PATH, $old_logo[2]);
                }

                if (!empty($id_card_files['national_identity_card']['name'])) {

                    $_FILES['temp_image']['name'] = $id_card_files['national_identity_card']['name'];
                    $_FILES['temp_image']['type'] = $id_card_files['national_identity_card']['type'];
                    $_FILES['temp_image']['tmp_name'] = $id_card_files['national_identity_card']['tmp_name'];
                    $_FILES['temp_image']['error'] = $id_card_files['national_identity_card']['error'];
                    $_FILES['temp_image']['size'] = $id_card_files['national_identity_card']['size'];
                    if (!$other_img->do_upload('temp_image')) {
                        $id_card_error = 'Images :' . $id_card_error . ' ' . $other_img->display_errors();
                    } else {
                        $temp_array_id_card = $other_img->data();
                        resize_review_images($temp_array_id_card, FCPATH . RESTRO_DOCUMENTS_PATH);
                        $id_card_doc  = RESTRO_DOCUMENTS_PATH . $temp_array_id_card['file_name'];
                    }
                } else {
                    $_FILES['temp_image']['name'] = $id_card_files['national_identity_card']['name'];
                    $_FILES['temp_image']['type'] = $id_card_files['national_identity_card']['type'];
                    $_FILES['temp_image']['tmp_name'] = $id_card_files['national_identity_card']['tmp_name'];
                    $_FILES['temp_image']['error'] = $id_card_files['national_identity_card']['error'];
                    $_FILES['temp_image']['size'] = $id_card_files['national_identity_card']['size'];
                    if (!$other_img->do_upload('temp_image')) {
                        $id_card_error = $other_img->display_errors();
                    }
                }
                //Deleting Uploaded Images if any overall error occured
                if ($id_card_error != NULL || !$this->form_validation->run()) {
                    if (isset($id_card_doc) && !empty($id_card_doc || !$this->form_validation->run())) {
                        foreach ($id_card_doc as $key => $val) {
                            unlink(FCPATH . RESTRO_DOCUMENTS_PATH . $id_card_doc[$key]);
                        }
                    }
                }
            }

            if ($id_card_error != NULL) {
                $this->response['error'] = true;
                $this->response['message'] =  $id_card_error;
                print_r(json_encode($this->response));
                return;
            }

            //process address_proof
            $temp_array_proof = $proof_doc = array();
            $proof_files = $_FILES;
            $proof_error = "";
            $config = [
                'upload_path' =>  FCPATH . RESTRO_DOCUMENTS_PATH,
                'allowed_types' => 'jpg|png|jpeg|gif',
                'max_size' => 8000,
            ];
            if (isset($proof_files['address_proof']) && !empty($proof_files['address_proof']['name']) && isset($proof_files['address_proof']['name'])) {
                $other_img = $this->upload;
                $other_img->initialize($config);

                if (isset($_POST['id']) && !empty($_POST['id']) && isset($partner_data_id[0]['address_proof']) && !empty($partner_data_id[0]['address_proof'])) {
                    $old_logo = explode('/', $partner_data_id[0]['address_proof']);
                    delete_images(RESTRO_DOCUMENTS_PATH, $old_logo[2]);
                }

                if (!empty($proof_files['address_proof']['name'])) {

                    $_FILES['temp_image']['name'] = $proof_files['address_proof']['name'];
                    $_FILES['temp_image']['type'] = $proof_files['address_proof']['type'];
                    $_FILES['temp_image']['tmp_name'] = $proof_files['address_proof']['tmp_name'];
                    $_FILES['temp_image']['error'] = $proof_files['address_proof']['error'];
                    $_FILES['temp_image']['size'] = $proof_files['address_proof']['size'];
                    if (!$other_img->do_upload('temp_image')) {
                        $proof_error = 'Images :' . $proof_error . ' ' . $other_img->display_errors();
                    } else {
                        $temp_array_proof = $other_img->data();
                        resize_review_images($temp_array_proof, FCPATH . RESTRO_DOCUMENTS_PATH);
                        $proof_doc  = RESTRO_DOCUMENTS_PATH . $temp_array_proof['file_name'];
                    }
                } else {
                    $_FILES['temp_image']['name'] = $proof_files['address_proof']['name'];
                    $_FILES['temp_image']['type'] = $proof_files['address_proof']['type'];
                    $_FILES['temp_image']['tmp_name'] = $proof_files['address_proof']['tmp_name'];
                    $_FILES['temp_image']['error'] = $proof_files['address_proof']['error'];
                    $_FILES['temp_image']['size'] = $proof_files['address_proof']['size'];
                    if (!$other_img->do_upload('temp_image')) {
                        $proof_error = $other_img->display_errors();
                    }
                }
                //Deleting Uploaded Images if any overall error occured
                if ($proof_error != NULL || !$this->form_validation->run()) {
                    if (isset($proof_doc) && !empty($proof_doc || !$this->form_validation->run())) {
                        foreach ($proof_doc as $key => $val) {
                            unlink(FCPATH . RESTRO_DOCUMENTS_PATH . $proof_doc[$key]);
                        }
                    }
                }
            }

            if ($proof_error != NULL) {
                $this->response['error'] = true;
                $this->response['message'] =  $proof_error;
                print_r(json_encode($this->response));
                return;
            }

            // process working hours for restro
            $work_time = $gallary = [];
            if (isset($_POST['working_time']) && !empty($_POST['working_time'])) {
                $working_time = $this->input->post('working_time', true);
                $work_time = json_decode($working_time, true);
            }
            if (isset($_POST['gallary']) && !empty($_POST['gallary'])) {
                $gallary = explode(",", $this->input->post('gallary', true));
            }

            if (isset($_POST['id'])) {
             
                // process permissions of partners
                $permmissions = $permits = array();
                $permits = get_partner_permission($this->input->post('id', true));
                $permmissions['customer_privacy'] = (isset($permits->customer_privacy)) ? 1 : 0;
                $permmissions['view_order_otp'] = (isset($permits->view_order_otp)) ? 1 : 0;
                $permmissions['assign_rider'] = (isset($permits->assign_rider)) ? 1 : 0;
                $permmissions['is_email_setting_on'] = (isset($permits->is_email_setting_on)) ? 1 : 0;
                $permmissions['delivery_orders'] = (isset($_POST['delivery_orders']) && !empty($_POST['delivery_orders'])) ? 1 : 0;
                $permmissions['self_pickup'] = (isset($_POST['self_pickup']) && !empty($_POST['self_pickup'])) ? 1 : 0;
                if(isset($_POST['id']) && !empty($_POST['id'])){
                    $global_restaurant_time = fetch_details(['user_id' => $this->input->post('id', true)], 'partner_data', 'global_restaurant_time');
                }
                // print_r($global_restaurant_time);
                // die;
                $restro_data = array(
                    'user_id' => $this->input->post('id', true),
                    'global_restaurant_time' => (isset($global_restaurant_time[0]['global_restaurant_time'])) && $global_restaurant_time[0]['global_restaurant_time'] == 1  ? "on" : "0",
                    'edit_restro_data_id' => $partner_data_id[0]['id'],
                    'address_proof' => (!empty($proof_doc)) ? $proof_doc : $partner_data_id[0]['address_proof'],
                    'national_identity_card' => (!empty($id_card_doc)) ? $id_card_doc : $partner_data_id[0]['national_identity_card'],
                    'profile' => (!empty($profile_doc)) ? $profile_doc : $partner_data_id[0]['profile'],
                    'global_commission' => (isset($partner_data_id[0]['commission'])) ? $partner_data_id[0]['commission'] : 0,
                    'partner_name' => $this->input->post('partner_name', true),
                    'description' => $this->input->post('description', true),
                    'address' => $this->input->post('address', true),
                    'type' => $this->input->post('type', true),
                    'tax_name' => $this->input->post('tax_name', true),
                    'tax_number' => $this->input->post('tax_number', true),
                    'account_number' => $this->input->post('account_number', true),
                    'account_name' => $this->input->post('account_name', true),
                    'bank_code' => $this->input->post('bank_code', true),
                    'bank_name' => $this->input->post('bank_name', true),
                    'bank_code' => $this->input->post('bank_code', true),
                    'licence_name' => $this->input->post('licence_name', true),
                    'licence_code' => $this->input->post('licence_code', true),
                    'licence_proof' => (!empty($licence_doc)) ?  $licence_doc : [],
                    'cooking_time' => $this->input->post('cooking_time', true),
                    'bank_name' => $this->input->post('bank_name', true),
                    'pan_number' => $this->input->post('pan_number', true),
                    'gallery' => (isset($gallary) && !empty($gallary)) ? $gallary : NULL,
                    'status' => $this->input->post('status', true),
                    'permissions' => $permmissions,
                    'slug' => create_unique_slug($this->input->post('partner_name', true), 'partner_data')
                );
                

                if (!empty($_POST['old']) || !empty($_POST['new'])) {
                    $identity = ($identity_column == 'mobile') ? 'mobile' : 'email';
                    $res = fetch_details(['id' => $id], 'users', $identity);
                    if (!empty($res)) {
                        if (!$this->ion_auth->change_password($res[0][$identity], $this->input->post('old'), $this->input->post('new'))) {

                            // if the login was un-successful
                            $response['error'] = true;
                            $response['message'] = strip_tags($this->ion_auth->errors());
                            echo json_encode($response);
                            return;
                        } else {
                            $restro_filter['id'] = $id;
                            $restro_filter['ignore_status'] = true;
                            $restro_data = fetch_partners($restro_filter);
                            unset($restro_data['data'][0]['partner_cook_time']);

                            $response['error'] = false;
                            $response['message'] = 'Password Update Succesfully';
                            $response['data'] = $restro_data['data'];
                            echo json_encode($response);
                            return;
                        }
                    } else {
                        $response['error'] = true;
                        $response['message'] = 'User not exists';
                        echo json_encode($response);
                        return;
                    }
                }
                $profile = array(
                    'name' => $this->input->post('name', true),
                    'email' => $this->input->post('email', true),
                    'mobile' => $this->input->post('mobile', true),
                    'password' => $this->input->post('password', true),
                    'latitude' => $this->input->post('latitude', true),
                    'longitude' => $this->input->post('longitude', true),
                    'city' => $this->input->post('city_id', true)
                );

                // process updated tags
                $tags = array();
                if (isset($_POST['restro_tags']) && !empty($_POST['restro_tags'])) {
                    $restro_tags = explode(',', $this->input->post('restro_tags', true));
                    foreach ($restro_tags as $row) {
                        $tempRow['partner_id'] = $this->input->post('id', true);
                        $tempRow['tag_id'] = $row;
                        $tags[] = $tempRow;
                    }
                }

                if ($this->Partner_model->add_partner($restro_data, $profile, $work_time, $tags)) {
                    $id = $this->input->post('id', true);
                    $restro_filter['id'] = $id;
                    $restro_filter['ignore_status'] = true;
                    $restro_data = fetch_partners($restro_filter);
                    unset($restro_data['data'][0]['partner_cook_time']);

                    $response['error'] = false;
                    $response['message'] = 'Partner Update Successfully';
                    $response['data'] = $restro_data['data'];
                    echo json_encode($response);
                    return false;
                } else {
                    $this->response['error'] = true;
                    $this->response['message'] = "Somehting went wrong.Please try again later.";
                    $this->response['data'] = array();
                    print_r(json_encode($this->response));
                    return false;
                }
            } else {

                if (!$this->form_validation->is_unique($_POST['mobile'], 'users.mobile') || !$this->form_validation->is_unique($_POST['email'], 'users.email')) {
                    $response["error"]   = true;
                    $response["message"] = "Email or mobile already exists !";
                    $response["data"] = array();
                    echo json_encode($response);
                    return false;
                }

                $identity_column = $this->config->item('identity', 'ion_auth');
                $email = strtolower($this->input->post('email'));
                $mobile = $this->input->post('mobile');
                $identity = ($identity_column == 'mobile') ? $mobile : $email;
                $password = $this->input->post('password');

                $additional_data = [
                    'username' => $this->input->post('name', true),
                    'fcm_id' => $this->input->post('fcm_id', true),
                    'platform' => $this->input->post('device_type', true),
                    'latitude' => $this->input->post('latitude', true),
                    'longitude' => $this->input->post('longitude', true),
                    'city' => $this->input->post('city_id', true)
                ];
                // process tags if any
                $tags = array();
                if (isset($_POST['restro_tags']) && !empty($_POST['restro_tags'])) {
                    $restro_tags = explode(',', $this->input->post('restro_tags', true));
                    $tags = array_map(function ($value) {
                        $tmp_tag["partner_id"] =  $this->input->post('id', true);
                        $tmp_tag["tag_id"] = $value;
                        return $tmp_tag;
                    }, $restro_tags);
                }

                $tags = array();
                $this->ion_auth->register($identity, $password, $email, $additional_data, ['4']);
                if (update_details(['active' => 1], [$identity_column => $identity], 'users')) {
                    $user_id = fetch_details(['mobile' => $mobile], 'users', 'id');
                    if (isset($_POST['gallary']) && !empty($_POST['gallary'])) {
                        $gallary = explode(",", $this->input->post('gallary', true));
                    }

                    $data = array(
                        'user_id' => $user_id[0]['id'],
                        'address_proof' => (!empty($proof_doc)) ? $proof_doc : null,
                        'national_identity_card' => (!empty($id_card_doc)) ? $id_card_doc : null,
                        'profile' => (!empty($profile_doc)) ? $profile_doc : null,
                        'global_commission' => (isset($partner_data_id[0]['commission'])) ? $partner_data_id[0]['commission'] : 0,
                        'partner_name' => $this->input->post('partner_name', true),
                        'description' => $this->input->post('description', true),
                        'address' => $this->input->post('address', true),
                        'type' => $this->input->post('type', true),
                        'tax_name' => $this->input->post('tax_name', true),
                        'tax_number' => $this->input->post('tax_number', true),
                        'account_number' => $this->input->post('account_number', true),
                        'account_name' => $this->input->post('account_name', true),
                        'bank_code' => $this->input->post('bank_code', true),
                        'bank_name' => $this->input->post('bank_name', true),
                        'licence_name' => $this->input->post('licence_name', true),
                        'licence_code' => $this->input->post('licence_code', true),
                        'licence_proof' => (!empty($licence_doc)) ?  $licence_doc : [],
                        'pan_number' => $this->input->post('pan_number', true),
                        'cooking_time' => $this->input->post('cooking_time', true),
                        'gallery' => (isset($_POST['gallery']) && !empty($_POST['gallery'])) ? $this->input->post('gallery', true) : NULL,
                        'status' => 2,
                        'permissions' => 'restro_profile',
                        'slug' => create_unique_slug($this->input->post('partner_name', true), 'partner_data')
                    );
                    $insert_id = $this->Partner_model->add_partner($data, [], $work_time, $tags);
                    if (!empty($insert_id)) {
                        $this->response['error'] = false;
                        $this->response['message'] = 'Partner Registered Successfully';
                        print_r(json_encode($this->response));
                    } else {
                        $this->response['error'] = true;
                        $this->response['message'] = "partner data was not Registered";
                        print_r(json_encode($this->response));
                    }
                } else {
                    $this->response['error'] = true;
                    $message = (isset($_POST['id'])) ? 'partner not Updated' : 'partner not Registered.';
                    $this->response['message'] = $message;
                    print_r(json_encode($this->response));
                }
            }
        }
    }

        public function update_restaurant_time(){
        
         if (!verify_tokens()) {
            return false;
        }
        
        $this->form_validation->set_rules('partner_id', 'Partner', 'required|trim|xss_clean');
        $this->form_validation->set_rules('global_restaurant_time', 'Global Restaurant Time', 'required|trim|xss_clean');
        
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            
            
            if (update_details(['global_restaurant_time' => $_POST['global_restaurant_time']], ['user_id' => $_POST['partner_id']], "partner_data")) {
                    $response['error'] = false;
                    $response['message'] = "Restaurant time updated successfully.";
                    print_r(json_encode($response));
                    return false;
                } else {
                    $response['error'] = true;
                    $response['message'] = "Something went wrong.Please try again later.";
                    print_r(json_encode($response));
                    return false;
                }
        }
    }

    // 27. delete_product
    public function delete_product()
    {
        /* Parameters to be passed
            product_id:28
        */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('product_id', 'Product Id', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return false;
        }
        $id = $this->input->post('product_id', true);
         if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $this->response['error'] = true;
                $this->response['message'] = DEMO_VERSION_MSG;
                echo json_encode($this->response);
                return false;
                exit();
            }
        if (delete_details(['product_id' => $id], 'product_variants')) {
            delete_details(['product_id' => $id], 'product_add_ons');
            delete_details(['product_id' => $id], 'product_rating');
            delete_details(['product_id' => $id], 'product_tags');
            delete_details(['product_id' => $id], 'product_attributes');
            delete_details(['id' => $id], 'products');
            $response['error'] = false;
            $response['message'] = 'Deleted Succesfully';
        } else {
            $response['error'] = true;
            $response['message'] = 'Something Went Wrong';
        }
        print_r(json_encode($response));
    }

    // 28. update_products
    public function update_products()
    {
        /*
            edit_product_id:74
            edit_variant_id:104,105
            variants_ids: new created with new attributes added
            pro_input_name: product name
            partner_id:1255
            product_category_id:99
            short_description: description
            product_add_ons:  [{"title":"add_on1","description":"descritpion","price":"40","calories":"123","status":1},{"title":"add_on2","description":"description2","price":"43","calories":"1234","status":1}]
            tags:1,2,3                               //{pass Tag Ids comma saprated}
            is_prices_inclusive_tax:0                //{1: inclusive | 0: exclusive}
            cod_allowed:1                            //{ 1:allowed | 0:not-allowed }{default:1}
            available_time : 1                       //{ 1:need-time | 0:no-time needed }{default:0}
            product_start_time : 16:00:00
            product_end_time : 17:00:00
            is_cancelable:1                          //{optional}{1:cancelable | 0:not-cancelable}{default:0}
            cancelable_till:pending                  //{pending,confirmed,preparing,out_for_delivery}{required if "is_cancelable" is 1}
            pro_input_image:text type(relative_path url - from get_media api)  
            indicator:1                              //{ 0 - none | 1 - veg | 2 - non-veg }
            highlights:new,fresh                     //{optional}
            calories:123                             //{optional}
            total_allowed_quantity:100               //{optional}
            minimum_order_quantity:12
            attribute_values:1,2,3,4,5               //{comma saprated attributes values ids if set}
            --------------------------------------------------------------------------------
            till above same params
            --------------------------------------------------------------------------------
            --------------------------------------------------------------------------------
            common param for simple and variable product
            --------------------------------------------------------------------------------          
            product_type:simple_product | variable_product  
            variant_stock_level_type:product_level
            
            if(product_type == variable_product):
                variants_ids:3 5,4 5,1 2
                variant_price:100,200
                variant_special_price:90,190
                variant_images:files              //{optional}

                total_stock_variant_type:100     //{if (variant_stock_level_type == product_level)}
                variant_status:1                 //{if (variant_stock_level_type == product_level)}

            if(product_type == simple_product):
                simple_product_stock_status:null|0|1   {1=in stock | 0=out stock}
                simple_price:100
                simple_special_price:90
                product_total_stock:100             {optional}
                variant_stock_status: 0             {optional}//{0 =>'Simple_Product_Stock_Active' 1 => "Product_Level"	}
                variant_status:1
       */
        if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
            $this->response['error'] = true;
            $this->response['message'] = DEMO_VERSION_MSG;
            echo json_encode($this->response);
            return false;
            exit();
        }
        if (!verify_tokens()) {
            return false;
        }


        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('edit_product_id', 'edit_product_id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('indicator', 'Product Indicator', 'trim|required|xss_clean');
        $this->form_validation->set_rules('edit_variant_id', 'edit_variant_id', 'trim|xss_clean');
        $this->form_validation->set_rules('pro_input_name', 'Product Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('product_category_id', 'Product Category', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('short_description', 'Short Description', 'trim|required|xss_clean');
        $this->form_validation->set_rules('pro_input_image', 'Product Image', 'trim|xss_clean', array('required' => 'Image is required'));
        $this->form_validation->set_rules('tags', 'Food Tags', 'trim|xss_clean');
        $this->form_validation->set_rules('attribute_values', 'Attribute Values', 'trim|xss_clean');
        $this->form_validation->set_rules('product_type', 'Product type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('total_allowed_quantity', 'Total Allowed Quantity', 'trim|xss_clean');
        $this->form_validation->set_rules('calories', 'calories', 'trim|xss_clean|numeric');
        $this->form_validation->set_rules('minimum_order_quantity', 'Minimum Order Quantity', 'trim|xss_clean');
        $this->form_validation->set_rules('product_type', 'Product Type', 'trim|required|xss_clean|in_list[simple_product,variable_product]');
        $this->form_validation->set_rules('variant_stock_level_type', 'Product Lavel', 'trim|required|xss_clean|in_list[product_level]');

        $_POST['variant_price'] = (isset($_POST['variant_price']) && !empty($_POST['variant_price'])) ?  explode(",", $this->input->post('variant_price', true)) : NULL;
        $_POST['variant_special_price'] = (isset($_POST['variant_special_price']) && !empty($_POST['variant_special_price'])) ?  explode(",", $this->input->post('variant_special_price', true)) : NULL;
        $_POST['variants_ids'] = (isset($_POST['variants_ids']) && !empty($_POST['variants_ids'])) ?  explode(",", $this->input->post('variants_ids', true)) : NULL;
        $_POST['variant_total_stock'] = (isset($_POST['variant_total_stock']) && !empty($_POST['variant_total_stock'])) ?  explode(",", $this->input->post('variant_total_stock', true)) : NULL;
        $_POST['variant_level_stock_status'] = (isset($_POST['variant_level_stock_status']) && !empty($_POST['variant_level_stock_status'])) ?  explode(",", $this->input->post('variant_level_stock_status', true)) : NULL;
        $_POST['edit_variant_id'] = (isset($_POST['edit_variant_id']) && !empty($_POST['edit_variant_id'])) ? explode(",", $this->input->post('edit_variant_id', true)) : [];

        if (isset($_POST['is_cancelable']) && $_POST['is_cancelable'] == '1') {
            $this->form_validation->set_rules('cancelable_till', 'Till which status', 'trim|required|xss_clean|in_list[pending,confirmed,preparing,out_for_delivery]');
        }
        if (isset($_POST['cod_allowed'])) {
            $this->form_validation->set_rules('cod_allowed', 'COD allowed', 'trim|xss_clean');
        }
        if (isset($_POST['is_spicy'])) {
            $this->form_validation->set_rules('is_spicy', 'Is Spicy', 'trim|xss_clean');
        }
        if (isset($_POST['is_prices_inclusive_tax'])) {
            $this->form_validation->set_rules('is_prices_inclusive_tax', 'Tax included in prices', 'trim|xss_clean');
        }

        // If product type is simple			
        if (isset($_POST['product_type']) && $_POST['product_type'] == 'simple_product') {
            $this->form_validation->set_rules('simple_price', 'Price', 'trim|required|numeric|greater_than_equal_to[' . $this->input->post('simple_special_price') . ']|xss_clean');
            $this->form_validation->set_rules('simple_special_price', 'Special Price', 'trim|numeric|less_than_equal_to[' . $this->input->post('simple_price') . ']|xss_clean');

            if (isset($_POST['simple_product_stock_status']) && in_array($_POST['simple_product_stock_status'], array('0', '1'))) {
                $this->form_validation->set_rules('product_total_stock', 'Total Stock', 'trim|required|numeric|xss_clean');
                $this->form_validation->set_rules('simple_product_stock_status', 'Stock Status', 'trim|required|numeric|xss_clean');
            }
        } elseif (isset($_POST['product_type']) && $_POST['product_type'] == 'variable_product') { //If product type is variant	
            if (isset($_POST['variant_stock_status']) && $_POST['variant_stock_status'] == '0') {
                if ($_POST['variant_stock_level_type'] == "product_level") {
                    $this->form_validation->set_rules('total_stock_variant_type', 'Total Stock', 'trim|required|xss_clean');
                    $this->form_validation->set_rules('variant_stock_status', 'Stock Status', 'trim|required|xss_clean');
                    if (isset($_POST['variant_price']) && isset($_POST['variant_special_price'])) {
                        foreach ($_POST['variant_price'] as $key => $value) {
                            $this->form_validation->set_rules('variant_price[' . $key . ']', 'Price', 'trim|required|numeric|xss_clean|greater_than_equal_to[' . $this->input->post('variant_special_price[' . $key . ']') . ']');
                            $this->form_validation->set_rules('variant_special_price[' . $key . ']', 'Special Price', 'trim|numeric|xss_clean|less_than_equal_to[' . $this->input->post('variant_price[' . $key . ']') . ']');
                        }
                    } else {
                        $this->form_validation->set_rules('variant_price', 'Price', 'trim|required|numeric|xss_clean|greater_than_equal_to[' . $this->input->post('variant_special_price') . ']');
                        $this->form_validation->set_rules('variant_special_price', 'Special Price', 'trim|numeric|xss_clean|less_than_equal_to[' . $this->input->post('variant_price') . ']');
                    }
                }
            } else {
                if (isset($_POST['variant_price']) && isset($_POST['variant_special_price'])) {
                    foreach ($_POST['variant_price'] as $key => $value) {
                        $this->form_validation->set_rules('variant_price[' . $key . ']', 'Price', 'trim|required|numeric|xss_clean|greater_than_equal_to[' . $this->input->post('variant_special_price[' . $key . ']') . ']');
                        $this->form_validation->set_rules('variant_special_price[' . $key . ']', 'Special Price', 'trim|numeric|xss_clean|less_than_equal_to[' . $this->input->post('variant_price[' . $key . ']') . ']');
                    }
                } else {
                    $this->form_validation->set_rules('variant_price', 'Price', 'trim|required|numeric|xss_clean|greater_than_equal_to[' . $this->input->post('variant_special_price') . ']');
                    $this->form_validation->set_rules('variant_special_price', 'Special Price', 'trim|numeric|xss_clean|less_than_equal_to[' . $this->input->post('variant_price') . ']');
                }
            }
        }

        $varient_eidt_id  = $_POST['edit_variant_id'];
        if (!$this->form_validation->run()) {
            $response['error'] = true;
            $response['message'] = strip_tags(validation_errors());
            $response['data'] = array();
            echo json_encode($response);
        } else {
            if (isset($_POST['tags']) && $_POST['tags'] != '') {
                $_POST['tags'] = explode(",", $_POST['tags']);
            }
            $_POST['edit_variant_id'] = $varient_eidt_id;
            $this->Product_model->add_product($_POST);
            $this->response['error'] = false;
            $this->response['message'] = 'Product Updated Successfully';
            $this->response['data'] = array();

            print_r(json_encode($this->response));
        }
    }

    // 29. get_riders
    public function get_riders()
    {
        /*
            partner_id:1255
            id: 1001                // { optional}
            search : Search keyword // { optional }
            limit:25                // { default - 25 } optional
            offset:0                // { default - 0 } optional
            sort: id/username/email/mobile/area_name/city_name/date_created // { default - id } optional
            order:DESC/ASC          // { default - DESC } optional
        */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('id', 'ID', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('partner_id', 'partner ID', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('search', 'Search keyword', 'trim|xss_clean');
        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            echo json_encode($this->response);
            return;
        } else {
            if (get_partner_permission($this->input->post('partner_id', true), 'assign_rider') == FALSE) {
                $this->response['error'] = true;
                $this->response['message'] = "You do not have permission to assign the Rider to orders.";
                $this->response['data'] = array();
                echo json_encode($this->response);
                return;
            }

            $id = (isset($_POST['id']) && is_numeric($_POST['id']) && !empty(trim($_POST['id']))) ? $this->input->post('id', true) : "";
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 25;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $_POST['order'] : 'DESC';
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $_POST['sort'] : 'id';
            $this->Rider_model->get_riders($id, $search, $offset, $limit, $sort, $order,$partner_login = true);
        }
    }

    public function reset_password()
    {
        /* Parameters to be passed
            mobile_no:7894561235            
            new: pass@123
        */

        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('mobile_no', 'Mobile No', 'trim|numeric|required|xss_clean|max_length[16]');
        $this->form_validation->set_rules('new', 'New Password', 'trim|required|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        }

        $identity_column = $this->config->item('identity', 'ion_auth');
        $res = fetch_details(['mobile' => $this->input->post("mobile_no", true)], 'users', "mobile,id");
        if (!empty($res)) {
            if ($this->ion_auth->in_group('partner', $res[0]['id'])) {
                $identity = ($identity_column  == 'email') ? $res[0]['email'] : $res[0]['mobile'];
                if (!$this->ion_auth->reset_password($identity, $this->input->post("new", true))) {
                    $response['error'] = true;
                    $response['message'] = strip_tags($this->ion_auth->messages());
                    $response['data'] = array();
                    echo json_encode($response);
                    return false;
                } else {
                    $response['error'] = false;
                    $response['message'] = 'Reset Password Successfully';
                    $response['data'] = array();
                    echo json_encode($response);
                    return false;
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'You can not reset password of higher authority!';
                $response['data'] = array();
                echo json_encode($response);
                return false;
            }
        } else {
            $response['error'] = true;
            $response['message'] = 'User does not exists !';
            $response['data'] = array();
            echo json_encode($response);
            return false;
        }
    }

    // get_tags
    public function get_tags()
    {
        /*
            partner_id:1  // {optional}
            sort:a.name               // { a.name / a.id } optional
            order:DESC/ASC      // { default - ASC } optional
            search:value        // {optional} 
            limit:10  {optional}
            offset:10  {optional}
       */

        $this->form_validation->set_rules('sort', 'sort', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('search', 'search', 'trim|xss_clean');
        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('limit', 'Limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'Offset', 'trim|numeric|xss_clean');

        if (!verify_tokens()) {
            return false;
        }
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 't.id';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";
            $limit = ($this->input->post('limit', true)) ? $this->input->post('limit', true) : 25;
            $offset = ($this->input->post('offset', true)) ? $this->input->post('offset', true) : 0;
            $partner_id = (isset($_POST['partner_id']) && !empty(trim($_POST['partner_id']))) ? $this->input->post('partner_id', true) : "";
            $result = $this->Tag_model->get_tags($search, $limit, $offset, $sort, $order, $partner_id);
            print_r(json_encode($result));
        }
    }

    public function add_tags()
    {
        /*
            partner_id:1 
            title:tag1
            tag_id:tag_id  {optional} {pass when update tag}
       */
        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('tag_id', 'Tag Id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('title', 'Title', 'trim|required|xss_clean');

        if (!verify_tokens()) {
            return false;
        }
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response["data"] = array();
            print_r(json_encode($this->response));
            return false;
        } else {
            $data = array();
            $partner_id = $this->input->post("partner_id", true);
            $title = $this->input->post("title", true);
            $data['edit_tag'] = (isset($_POST['tag_id']) && !empty($_POST['tag_id'])) ? $this->input->post("tag_id", true) : "";

            if (is_exist(['title' => $this->input->post('title', true)], 'tags')) {
                $response["error"]   = true;
                $response["message"] = "This Tag is Already Exist.";
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }
            $data['title'] = $title;

            $tag_id = $this->Tag_model->add_tags($data);
            if (!empty($tag_id)) {
                if (!isset($_POST['tag_id']) && empty($_POST['tag_id'])) {
                    $data = array(
                        'partner_id' => $partner_id,
                        'tag_id' => $tag_id,
                    );
                    insert_details($data, "partner_tags");
                }
                $this->response['error'] = false;
                $msg = (isset($_POST['tag_id']) && !empty($_POST['tag_id'])) ? 'Tag Updated Successfully' : "Tag Added Successfully";
                $this->response['message'] = $msg;
                $this->response["data"] = array();
                print_r(json_encode($this->response));
                return false;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Tag does not Added';
                $this->response["data"] = array();
                print_r(json_encode($this->response));
                return false;
            }
        }
    }

    //10. delete_tag
    public function delete_tag()
    {
        /*
            partner_id:1
            tag_id:1
         */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('partner_id', 'partner ID', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('tag_id', 'Tag ID', 'trim|numeric|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $partner_id = $this->input->post("partner_id", true);
            $tag_id = $this->input->post("tag_id", true);

            if (!is_exist(['tag_id' => $tag_id], 'partner_tags')) {
                $response["error"]   = true;
                $response["message"] = "This Tag is Already Removed.";
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }

            if (delete_details(['tag_id' => $tag_id, 'partner_id' => $partner_id], 'partner_tags')) {
                $this->response['error'] = false;
                $this->response['message'] = 'Tag Removed form your partner';
                $this->response['data'] = array();
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Something went wrong. Try again Later';
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    public function upload_media()
    {
        /* 
            upload_media
                partner_id:1
                documents[]:FILES
        */
        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|numeric|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return;
        } else {
            $partner_id = $this->input->post("partner_id", true);
            if (empty($_FILES['documents']['name'][0])) {
                $this->response['error'] = true;
                $this->response['message'] = "Upload at least one media file !";
                print_r(json_encode($this->response));
                return;
            }
            $year = date('Y');
            $target_path = FCPATH . MEDIA_PATH . $year . '/';
            $sub_directory = MEDIA_PATH . $year . '/';

            if (!file_exists($target_path)) {
                mkdir($target_path, 0777, true);
            }

            $temp_array = $media_ids = $other_images_new_name = array();
            $files = $_FILES;
            $other_image_info_error = "";
            $allowed_media_types = implode('|', allowed_media_types());
            $config['upload_path'] = $target_path;
            $config['allowed_types'] = $allowed_media_types;
            $other_image_cnt = count($_FILES['documents']['name']);
            $other_img = $this->upload;
            $other_img->initialize($config);
            for ($i = 0; $i < $other_image_cnt; $i++) {
                if (!empty($_FILES['documents']['name'][$i])) {
                    $_FILES['temp_image']['name'] = $files['documents']['name'][$i];
                    $_FILES['temp_image']['type'] = $files['documents']['type'][$i];
                    $_FILES['temp_image']['tmp_name'] = $files['documents']['tmp_name'][$i];
                    $_FILES['temp_image']['error'] = $files['documents']['error'][$i];
                    $_FILES['temp_image']['size'] = $files['documents']['size'][$i];
                    if (!$other_img->do_upload('temp_image')) {
                        $other_image_info_error = $other_image_info_error . ' ' . $other_img->display_errors();
                    } else {
                        $temp_array = $other_img->data();
                        $temp_array['sub_directory'] = $sub_directory;
                        $temp_array['partner_id'] = $partner_id;
                        $media_ids[] = $media_id = $this->media_model->set_media($temp_array); /* set media in database */
                        resize_image($temp_array,  $target_path, $media_id);
                        $other_images_new_name[$i] = $temp_array['file_name'];
                    }
                } else {

                    $_FILES['temp_image']['name'] = $files['documents']['name'][$i];
                    $_FILES['temp_image']['type'] = $files['documents']['type'][$i];
                    $_FILES['temp_image']['tmp_name'] = $files['documents']['tmp_name'][$i];
                    $_FILES['temp_image']['error'] = $files['documents']['error'][$i];
                    $_FILES['temp_image']['size'] = $files['documents']['size'][$i];
                    if (!$other_img->do_upload('temp_image')) {
                        $other_image_info_error = $other_img->display_errors();
                    }
                }
            }
            // Deleting Uploaded Images if any overall error occured
            if ($other_image_info_error != NULL) {
                if (isset($other_images_new_name) && !empty($other_images_new_name)) {
                    foreach ($other_images_new_name as $key => $val) {
                        unlink($target_path . $other_images_new_name[$key]);
                    }
                }
            }

            if (empty($_FILES) || $other_image_info_error != NULL) {
                $this->response['error'] = true;
                $this->response['message'] = (empty($_FILES)) ? "Files not Uploaded Successfully..!" :  $other_image_info_error;
                print_r(json_encode($this->response));
            } else {
                $this->response['error'] = false;
                $this->response['message'] = "Files Uploaded Successfully..!";
                print_r(json_encode($this->response));
            }
        }
    }

    // 29. get_product_add_ons
    public function get_product_add_ons()
    {
        /*
            product_id:10
        */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            echo json_encode($this->response);
            return;
        } else {
            $data = $this->Product_model->get_product_add_ons($this->input->post('product_id', true), true);
          return $data;
        }
    }

    public function update_add_ons()
    {
        /*
            add_on_id: 36                //{optional} {pass when need to update}
            title: add_on1
            product_id: 29
            description: descritpion
            price: 40.00
            calories: 123.00
            status:1 | 0                 //{1:active | 0:deactivate}
         */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('title', 'title', 'trim|required|xss_clean');
        $this->form_validation->set_rules('description', 'description', 'trim|required|xss_clean');
        $this->form_validation->set_rules('price', 'price', 'trim|required|xss_clean');
        $this->form_validation->set_rules('calories', 'calories', 'trim|required|xss_clean');
        $this->form_validation->set_rules('product_id', 'product_id', 'trim|required|xss_clean');
        $this->form_validation->set_rules('status', 'status', 'trim|required|xss_clean');
        $this->form_validation->set_rules('add_on_id', 'add_on_id', 'trim|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
        } else {
            $data = array(
                'title' => $this->input->post('title', true),
                'product_id' => $this->input->post('product_id', true),
                'description' => $this->input->post('description', true),
                'price' => $this->input->post('price', true),
                'calories' => $this->input->post('calories', true),
                'status' => $this->input->post('status', true),
            );
            if (isset($_POST['add_on_id']) && !empty($_POST['add_on_id'])) {
                // update add_ons
                if (update_details($data, ['id' => $this->input->post('add_on_id', true)], 'product_add_ons') == TRUE) {
                    $this->response['error'] = false;
                    $this->response['message'] = "Add On details Update Successfuly.";
                } else {
                    $this->response['error'] = true;
                    $this->response['message'] = "Not Updated. Try again later.";
                }
            } else {
                if (!is_exist(['title' => $this->input->post('title', true), 'product_id' => $this->input->post('product_id', true)], 'product_add_ons', null)) {
                    // add new add_ons
                    if (insert_details($data, 'product_add_ons')) {
                        $this->response['error'] = false;
                        $this->response['message'] = "Tracking details Insert Successfuly.";
                    } else {
                        $this->response['error'] = true;
                        $this->response['message'] = "Not Inserted. Try again later.";
                    }
                } else {
                    $this->response['error'] = true;
                    $this->response['message'] = "Already have this add on.";
                }
            }
            print_r(json_encode($this->response));
        }
    }

    public function delete_add_on()
    {
        /*
            add_on_id: 1
         */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('add_on_id', 'add_on_id', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            if (delete_details(['id' => $this->input->post('add_on_id', true)], 'product_add_ons')) {
                $response['error'] = false;
                $response['message'] = 'Deleted Succesfully';
            } else {
                $response['error'] = true;
                $response['message'] = 'Something Went Wrong';
            }
            print_r(json_encode($response));
            return false;
        }
    }

    public function get_product_tags()
    {
        /*
            product_id:10
        */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            echo json_encode($this->response);
            return;
        } else {
            $data = fetch_details(["pt.product_id" => $this->input->post('product_id', true)], "product_tags pt", "pt.*,t.title", null, null, null, "DESC", "", '', "tags t", "t.id=pt.tag_id");
            if (!empty($data)) {
                $this->response['error'] = false;
                $this->response['message'] = "Data Retrived Successfully";
                $this->response['data'] = $data;
                echo json_encode($this->response);
                return;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Data not found.";
                $this->response['data'] = array();
                echo json_encode($this->response);
                return;
            }
        }
    }
    public function update_product_status()
    {
        /*
            product_id:10
            status:1     {1: active | 0: de-active}
        */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('product_id', 'Product ID', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('status', 'Status', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            echo json_encode($this->response);
            return;
        } else {
             if (defined('ALLOW_MODIFICATION') && ALLOW_MODIFICATION == 0) {
                $this->response['error'] = true;
                $this->response['message'] = DEMO_VERSION_MSG;
                echo json_encode($this->response);
                return false;
                exit();
            }
            $status = $this->input->post("status", true);
            $product_id = $this->input->post("product_id", true);
            if (update_details(['status' => $status], ['id' => $product_id], "products")) {
                $this->response['error'] = false;
                $this->response['message'] = "Status Updated Successfully";
                $this->response['data'] = [];
                echo json_encode($this->response);
                return;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Status not Updated.";
                $this->response['data'] = array();
                echo json_encode($this->response);
                return;
            }
        }
    }
    public function update_attribute_status()
    {
        /*
            type: attributes | attribute_values
            type_id: 1 
            status: 1       {1: active | 0: de-active}
        */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('type_id', 'Type Id', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('status', 'Status', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            echo json_encode($this->response);
            return;
        } else {
            $status = $this->input->post("status", true);
            $type = $this->input->post("type", true);
            $type_id = $this->input->post("type_id", true);
            if (is_exist(["id" => $type_id], $type)) {
                if (update_details(['status' => $status], ['id' => $type_id], $type)) {
                    $this->response['error'] = false;
                    $this->response['message'] = "Status Updated Successfully";
                    $this->response['data'] = [];
                    echo json_encode($this->response);
                    return;
                } else {
                    $this->response['error'] = true;
                    $this->response['message'] = "Status not Updated.";
                    $this->response['data'] = array();
                    echo json_encode($this->response);
                    return;
                }
            } else {
                $this->response['error'] = true;
                $this->response['message'] = "Id you provided was not exist in database.";
                $this->response['data'] = array();
                echo json_encode($this->response);
                return;
            }
        }
    }

    public function manage_promocode(){
        /** Add/Update promocode
         * 
         * promocode_id:7 {only required while updateing the promocode}
         * partner_id:290{required}
         * promo_code:test promocode{required}
         * message:test{required}
         * start_date:2024-06-20 {required}
         * end_date:2024-06-25 {required}
         * no_of_users:5 {required}
         * minimum_order_amount:500 {required}
         * discount:20{required}
         * discount_type: percentage/amount {here need to select from these 2 options} {required}
         * max_discount_amount: 100{required}
         * repeat_usage:0/1 {required} {0: not-allowed, 1: allowed} {required}
         * no_of_repeat_usage:20 {only required when repeat_usage is 1(allowed)}
         * status:0/1 {0: deactive, 1: active}{required}
         * image:text type(relative_path url - from get_media api)
         * 
         **/
            
            $this->form_validation->set_rules('promocode_id', 'Promo Code Id ', 'trim|numeric|xss_clean');
            $this->form_validation->set_rules('partner_id', 'Partner Id ', 'trim|numeric|required|xss_clean');
            $this->form_validation->set_rules('promo_code', 'Promo Code ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('message', 'Message ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('start_date', 'Start date ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('end_date', 'End date ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('no_of_users', 'No of Users ', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('minimum_order_amount', 'Minimum Order Amount ', 'trim|numeric|required|xss_clean');
            $this->form_validation->set_rules('discount', 'Discount ', 'trim|required|numeric|xss_clean');
            $this->form_validation->set_rules('discount_type', 'Discount Type ', 'trim|required|xss_clean');
            $this->form_validation->set_rules('max_discount_amount', 'Maximum Discount Amount ', 'trim|numeric|required|xss_clean');
            $this->form_validation->set_rules('repeat_usage', 'Repeat Usage ', 'trim|required|xss_clean');
            if ($_POST['repeat_usage'] == '1') {
                $this->form_validation->set_rules('no_of_repeat_usage', 'No. of Repeat Usage ', 'trim|required|numeric|xss_clean');
            }
            $this->form_validation->set_rules('status', 'Status ', 'trim|required|xss_clean');

            if (!$this->form_validation->run()) {

                $this->response['error'] = true;
                $this->response['message'] = validation_errors();
                print_r(json_encode($this->response));
            } else {
                if (isset($_POST['promocode_id']) && !empty($_POST['promocode_id'])) {
                               
                    $promocode_detail = fetch_details(['id' => $_POST['promocode_id']],'promo_codes','*');

                    if($promocode_detail[0]['promocode_type'] == 'partner' && $_POST['partner_id'] == $promocode_detail[0]['partner_id']){
                           
                    }else{
                        $response["error"]   = true;
                        $response["message"] = "Oops! You are not authorized to update this promocode..!";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }

                    if (is_exist(['promo_code' => $_POST['promo_code']], 'promo_codes', $_POST['promocode_id'])) {
                        $response["error"]   = true;
                        $response["message"] = "Promo Code Already Exists !";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }

                } else {
                    if (is_exist(['promo_code' => $_POST['promo_code']], 'promo_codes')) {
                        $response["error"]   = true;
                        $response["message"] = "Promo Code Already Exists !";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }
                $minimum_order_amount = $_POST['minimum_order_amount'];
                $discount = $_POST['discount'];

                if ($_POST['discount_type'] == 'percentage') {
                    if ($discount <= 0 || $discount > 100) {
                        $response["error"]   = true;
                        $response["message"] = "Discount percentage cannot exceed 100 !";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                } elseif ($_POST['discount_type'] == 'amount') {
                    if ($discount >= $minimum_order_amount) {
                        $response["error"]   = true;
                        $response["message"] = "Discount can not be grater then or equal to the Minimum order amount !";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
                }
                $startDate = $_POST["start_date"];
                $endDate = $_POST["end_date"];


                $startDateTime = new DateTime($startDate);
                $endDateTime = new DateTime($endDate);

                if ($endDateTime <= $startDateTime) {

                    $this->response['error'] = true;
                    $this->response['message'] = 'The start date must be earlier than the end date.';
                    print_r(json_encode($this->response));
                } else {
                    $_POST['promocode_type'] = 'partner';
                    $_POST['edit_promo_code'] = $_POST['promocode_id'];
                    $this->Promo_code_model->add_promo_code_details($_POST);
                    $this->response['error'] = false;
                    $message = (isset($_POST['promocode_id'])) ? 'Promo code Updated Successfully' : 'Promo code Added Successfully';
                    $this->response['message'] = $message;
                    print_r(json_encode($this->response));
                }

            }
    }

    public function get_promo_codes()
    {
        /*
      get_promo_codes
             search : Search keyword // { optional }
             limit:25                // { default - 25 } optional
             offset:0                // { default - 0 } optional
             sort: id | date_created | last_updated                // { default - id } optional
             order:DESC/ASC          // { default - DESC } optional

             prtner_id : 290 {required}
         */
       

        $this->form_validation->set_rules('search', 'Search keyword', 'trim|xss_clean');
        $this->form_validation->set_rules('sort', 'sort', 'trim|xss_clean');
        $this->form_validation->set_rules('limit', 'limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'offset', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|xss_clean');
        $this->form_validation->set_rules('partner_id', 'Partner Id', 'trim|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        } else {
            $search = (isset($_POST['search']) && !empty(trim($_POST['search']))) ? $this->input->post('search', true) : "";
            $limit = (isset($_POST['limit']) && is_numeric($_POST['limit']) && !empty(trim($_POST['limit']))) ? $this->input->post('limit', true) : 10;
            $offset = (isset($_POST['offset']) && is_numeric($_POST['offset']) && !empty(trim($_POST['offset']))) ? $this->input->post('offset', true) : 0;
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $_POST['order'] : 'DESC';
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $_POST['sort'] : 'id';
            $partner_id = (isset($_POST['partner_id'])) && !empty($_POST['partner_id']) ? $_POST['partner_id'] : "";
            $from_partner_app = true;
            $promo_code = $this->Promo_code_model->get_promo_codes($limit, $offset, $sort, $order, $search,$partner_id,$from_partner_app);
            print_r(json_encode($promo_code));
            return false;
        }
    }

    public function delete_promocode(){
         
        /**
         * partner_id : 173 {required}
         * promocode_id : 47 {required}
         * 
         */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('partner_id', 'Partner Id', 'trim|required|xss_clean');
        $this->form_validation->set_rules('promocode_id', 'Promocode Id', 'trim|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
            print_r(json_encode($this->response));
            return;
        } else {
                    $promocode_detail = fetch_details(['id' => $_POST['promocode_id']],'promo_codes','*');
                    if(empty($promocode_detail)){
                                $response["error"]   = true;
                                $response["message"] = "This promocode dose not exist..!";
                                $response["data"] = array();
                                echo json_encode($response);
                                return false;
                    }
                    
                   
                    if($promocode_detail[0]['promocode_type'] == 'partner' && $_POST['partner_id'] == $promocode_detail[0]['partner_id']){
                           if(delete_details(['id' => $_POST['promocode_id']] , 'promo_codes')){
                            $response["error"]   = false;
                            $response["message"] = "Promocode deleted successfully..!";
                            $response["data"] = array();
                            echo json_encode($response);
                            return false;
                           }else{
                             $response["error"]   = true;
                             $response["message"] = "Something went wrong..!";
                             $response["data"] = array();
                             echo json_encode($response);
                             return false;
                           }

                    }else{
                        $response["error"]   = true;
                        $response["message"] = "Oops! You are not authorized to update this promocode..!";
                        $response["data"] = array();
                        echo json_encode($response);
                        return false;
                    }
        }
    }

    public function delete_partner_account()
    {
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|required|numeric|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response["data"] = array();
            print_r(json_encode($this->response));
            return false;
        } else {
            $partner_id = $this->input->post("partner_id", true);

            // Step 1: Get all product IDs associated with this partner
            $this->db->select('id');
            $this->db->where('partner_id', $partner_id);
            $product_query = $this->db->get('products');
            $product_ids = $product_query->result_array();

            // If there are products, extract their IDs
            $product_id_list = array_column($product_ids, 'id');

            // Start a database transaction
            $this->db->trans_start();

            // Delete product variants first (due to foreign key constraints)
            if (!empty($product_id_list)) {
                $this->db->where_in('product_id', $product_id_list);
                $this->db->delete('product_varient');
            }

            // Delete products of the partner
            $this->db->where('partner_id', $partner_id);
            $this->db->delete('products');

            // Delete partner data
            $this->db->where('user_id', $partner_id);
            $this->db->delete('partner_data');

            // Delete user
            $this->db->where('id', $partner_id);
            $this->db->delete('users');

            // Complete the transaction
            $this->db->trans_complete();

            // Check if the transaction was successful
            if ($this->db->trans_status() === FALSE) {
                $this->response['error'] = true;
                $this->response['message'] = "Something went wrong during account deletion.";
                $this->response["data"] = array();
                print_r(json_encode($this->response));
                return false;
            } else {
                $this->response['error'] = false;
                $this->response['message'] = "Partner account deleted successfully.";
                $this->response["data"] = array();
                print_r(json_encode($this->response));
                return true;
            }
        }
    }

    /** Table booking APIs */

    /** 1. add/update floor */


    public function add_floore()
    {
        /*
            partner_id:1 
            title:floore1
            status:1/0
            floore_id:floore_id  {optional} {pass when update floore}
       */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('floore_id', 'Floore Id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('title', 'Title', 'trim|required|xss_clean');
        $this->form_validation->set_rules('status', 'Status', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response["data"] = array();
            print_r(json_encode($this->response));
            return false;
        } else {
            $data = array();
            $partner_id = $this->input->post("partner_id", true);
            $title = $this->input->post("title", true);
            $status = $this->input->post("staus", true);

            $data['edit_floore'] = (isset($_POST['floore_id']) && !empty($_POST['floore_id'])) ? $this->input->post("floore_id", true) : "";
            if (empty($data['edit_floore'])) {
                if (is_exist(['title' => $this->input->post('title', true)], 'floore')) {
                    $response["error"]   = true;
                    $response["message"] = "This Floore is Already Exist.";
                    $response["data"] = array();
                    echo json_encode($response);
                    return false;
                }
            }
            $data['title'] = $title;
            $data['partner_id'] = $partner_id;
            $data['status'] = $status;

            $floore_id = $this->Table_booking_model->add_floore($data);
            if (!empty($floore_id)) {
                $data =  fetch_details(['id' => $floore_id], 'floore', "*");


                $this->response['error'] = false;
                $msg = (isset($_POST['floore_id']) && !empty($_POST['floore_id'])) ? 'Floore Updated Successfully' : "Floore Added Successfully";
                $this->response['message'] = $msg;
                $this->response["data"] = $data;
                print_r(json_encode($this->response));
                return false;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Floore does not Added';
                $this->response["data"] = array();
                print_r(json_encode($this->response));
                return false;
            }
        }
    }

    /** 2. delete floor */

    public function delete_floore()
    {
        /*
            partner_id:1
            floore_id:1
         */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('partner_id', 'partner ID', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('floore_id', 'Floore ID', 'trim|numeric|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $partner_id = $this->input->post("partner_id", true);
            $floore_id = $this->input->post("floore_id", true);

            if (!is_exist(['id' => $floore_id], 'floore')) {
                $response["error"]   = true;
                $response["message"] = "Floor does not exist.";
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }

            if (delete_details(['id' => $floore_id, 'partner_id' => $partner_id], 'floore')) {
                $this->response['error'] = false;
                $this->response['message'] = 'Floor Removed Successfully';
                $this->response['data'] = array();
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Something went wrong. Try again Later';
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    

    /** get floore list */

    public function get_floores()
    {

        /*
            partner_id:1 
            sort:a.name               // { a.name / a.id } optional
            order:DESC/ASC      // { default - ASC } optional
            limit:10  {optional}
            offset:10  {optional}
       */

        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('sort', 'sort', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('limit', 'Limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'Offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'id';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';
            $limit = ($this->input->post('limit', true)) ? $this->input->post('limit', true) : '';
            $offset = ($this->input->post('offset', true)) ? $this->input->post('offset', true) : 0;
            $partner_id = $this->input->post('partner_id', true);

            $result =  fetch_details(['partner_id' => $partner_id, 'status' => 1], 'floore', "*", $limit, $offset, $sort, $order);
            $total =  fetch_details(['partner_id' => $partner_id, 'status' => 1], 'floore', "*");

            if (!empty($result)) {

                $this->response['error'] = false;
                $this->response['message'] = 'Floor Retrived Successfully';
                $this->response['total'] = count($total);
                $this->response['data'] = $result;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Floor does not exist';
                $this->response['data'] = array();
            }
            // $result = $this->Table_booking_model->get_floores($partner_id, $limit, $offset, $sort, $order, $search);
            print_r(json_encode($this->response));
        }
    }

    /** 4. add/update table */

    public function add_table()
    {
        /*
            partner_id:1 
            title:floore1
            status:1/0
            floore_id:floore_id  
            table_id:table_id  {optional} {pass when update table}
       */
        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('floore_id', 'Floore Id', 'trim|required|numeric|xss_clean');
        $this->form_validation->set_rules('table_id', 'Table Id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('title', 'Title', 'trim|required|xss_clean');
        $this->form_validation->set_rules('status', 'Status', 'trim|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response["data"] = array();
            print_r(json_encode($this->response));
            return false;
        } else {
            $data = array();
            $partner_id = $this->input->post("partner_id", true);
            $floore_id = $this->input->post("floore_id", true);
            $title = $this->input->post("title", true);
            $status = $this->input->post("staus", true);

            $data['edit_table'] = (isset($_POST['table_id']) && !empty($_POST['table_id'])) ? $this->input->post("table_id", true) : "";
            if (empty($data['edit_table'])) {
                if (is_exist(['title' => $this->input->post('title', true)], 'tables')) {
                    $response["error"]   = true;
                    $response["message"] = "This Table is Already Exist.";
                    $response["data"] = array();
                    echo json_encode($response);
                    return false;
                }
            }
            $data['title'] = $title;
            $data['floore_id'] = $floore_id;
            $data['partner_id'] = $partner_id;
            $data['status'] = $status;

            $floore =  fetch_details(['id' => $floore_id], 'floore', "*");

            if (empty($floore)) {

                $this->response['error'] = true;
                $this->response['message'] = 'Floore does not exist';
                $this->response["data"] = array();
                print_r(json_encode($this->response));
                return false;
            } else {

                $floore_id = $this->Table_booking_model->add_table($data);
                if (!empty($floore_id)) {
                    $data =  fetch_details(['id' => $floore_id], 'tables', "*");


                    $this->response['error'] = false;
                    $msg = (isset($_POST['table_id']) && !empty($_POST['table_id'])) ? 'Table Updated Successfully' : "Table Added Successfully";
                    $this->response['message'] = $msg;
                    $this->response["data"] = $data;
                    print_r(json_encode($this->response));
                    return false;
                } else {
                    $this->response['error'] = true;
                    $this->response['message'] = 'Table does not Added';
                    $this->response["data"] = array();
                    print_r(json_encode($this->response));
                    return false;
                }
            }
        }
    }



    /** 5. delete table */

    public function delete_table()
    {
        /*
            partner_id:1
            table_id:1
         */
        if (!verify_tokens()) {
            return false;
        }

        $this->form_validation->set_rules('partner_id', 'partner ID', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('table_id', 'Table ID', 'trim|numeric|required|xss_clean');
        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            $this->response['data'] = array();
        } else {
            $partner_id = $this->input->post("partner_id", true);
            $table_id = $this->input->post("table_id", true);

            if (!is_exist(['id' => $table_id], 'tables')) {
                $response["error"]   = true;
                $response["message"] = "Table does not exist.";
                $response["data"] = array();
                echo json_encode($response);
                return false;
            }

            if (delete_details(['id' => $table_id, 'partner_id' => $partner_id], 'tables')) {
                $this->response['error'] = false;
                $this->response['message'] = 'Table Removed Successfully';
                $this->response['data'] = array();
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Something went wrong. Try again Later';
                $this->response['data'] = array();
            }
        }
        print_r(json_encode($this->response));
    }

    /** get table list */

    public function get_tables()
    {

        /*
            partner_id:1 
            floore_id : floore_id {optional}
            sort:a.name               // { a.name / a.id } optional
            order:DESC/ASC      // { default - ASC } optional
            limit:10  {optional}
            offset:10  {optional}
       */

        if (!verify_tokens()) {
            return false;
        }
        $this->form_validation->set_rules('sort', 'sort', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('order', 'order', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('floore_id', 'Floore Id', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('partner_id', 'partner Id', 'trim|numeric|required|xss_clean');
        $this->form_validation->set_rules('limit', 'Limit', 'trim|numeric|xss_clean');
        $this->form_validation->set_rules('offset', 'Offset', 'trim|numeric|xss_clean');

        if (!$this->form_validation->run()) {
            $this->response['error'] = true;
            $this->response['message'] = strip_tags(validation_errors());
            print_r(json_encode($this->response));
            return false;
        } else {
            $sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $this->input->post('sort', true) : 'id';
            $order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $this->input->post('order', true) : 'DESC';
            $limit = ($this->input->post('limit', true)) ? $this->input->post('limit', true) : 10;
            $offset = ($this->input->post('offset', true)) ? $this->input->post('offset', true) : 0;
            $partner_id = $this->input->post('partner_id', true);
            $floore_id = $this->input->post('floore_id', true);
            if (isset($_POST['floore_id']) && !empty($_POST['floore_id'])) {

                $result =  fetch_details(['partner_id' => $partner_id, 'floore_id' => $floore_id, 'status' => 1], 'tables', "*", $limit, $offset, $sort, $order);
                $total =  fetch_details(['partner_id' => $partner_id, 'floore_id' => $floore_id, 'status' => 1], 'tables', "*");
            } else {

                $result =  fetch_details(['partner_id' => $partner_id, 'status' => 1], 'tables', "*", $limit, $offset, $sort, $order);
                $total =  fetch_details(['partner_id' => $partner_id, 'status' => 1], 'tables', "*");
            }

            if (!empty($result)) {
                $this->response['error'] = false;
                $this->response['message'] = 'Tables Retrived Successfully';
                $this->response['total'] = count($total);
                $this->response['data'] = $result;
            } else {
                $this->response['error'] = true;
                $this->response['message'] = 'Table does not exist';
                $this->response['data'] = array();
            }
            // $result = $this->Table_booking_model->get_floores($partner_id, $limit, $offset, $sort, $order, $search);
            print_r(json_encode($this->response));
        }
    }

    public function register()
    {
    }

}
