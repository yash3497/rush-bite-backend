<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Manage Orders</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="home_breadcrumb" href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 main-content">
                    <div class="card content-area p-4">
                        <div class="card-innr">
                            <div class="gaps-1-5x row d-flex adjust-items-center">
                                <h5 class="col">Order Outlines</h5>
                                <div class="row col-12 d-flex">
                                     <!-- awaiting -->
                                    <div class="col-sm-3">
                                        <div class="small-box bg-gradient-info">
                                            <div class="inner">
                                                <h3><?= $status_counts['awaiting'] ?></h3>
                                                <p>Awaiting</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-xs fas fa-clock"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end -->
                                    <div class="col-sm-3">
                                        <div class="small-box bg-secondary">
                                            <div class="inner">
                                                <h3><?= $status_counts['pending'] ?></h3>
                                                <p>Pending</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-xs fa-history"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="small-box bg-primary">
                                            <div class="inner">
                                                <h3><?= $status_counts['confirmed'] ?></h3>
                                                <p>Confirmed</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-xs fa-level-down-alt"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="small-box bg-info">
                                            <div class="inner">
                                                <h3><?= $status_counts['preparing'] ?></h3>
                                                <p>Preparing</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-xs fa-people-carry"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- ready for pickup -->
                                    <div class="col-sm-3">
                                        <div class="small-box bg-gradient-dark">
                                            <div class="inner">
                                                <h3><?= $status_counts['ready_for_pickup'] ?></h3>
                                                <p>Ready For Pickup</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-xs fa-people-carry"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end -->
                                    <div class="col-sm-3">
                                        <div class="small-box bg-warning">
                                            <div class="inner">
                                                <h3><?= $status_counts['out_for_delivery'] ?></h3>
                                                <p>Out for Delivery</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-xs fa-shipping-fast"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="small-box bg-success">
                                            <div class="inner">
                                                <h3><?= $status_counts['delivered'] ?></h3>
                                                <p>Delivered</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-xs fa-user-check"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="small-box bg-danger">
                                            <div class="inner">
                                                <h3><?= $status_counts['cancelled'] ?></h3>
                                                <p>Cancelled</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fa fa-xs fa-times-circle"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row col-md-12">
                                    <div class="form-group col-md-3">
                                        <label>Date and time range:</label>
                                        <div class="input-group col-md-8">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="far fa-clock"></i></span>
                                            </div>
                                            <input type="text" class="form-control float-right" id="datepicker">
                                            <input type="hidden" id="start_date" class="form-control float-right">
                                            <input type="hidden" id="end_date" class="form-control float-right">
                                        </div>
                                        <!-- /.input group -->
                                    </div>
                                    <div class="form-group col-md-2">
                                        <div>
                                            <label>Filter Orders By status</label>
                                            <select id="order_status" name="order_status" placeholder="Select Status" required="" class="form-control">
                                                <option value="">All Orders</option>
                                                <option value="pending">Pending</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="preparing">Preparing</option>
                                                <option value="out_for_delivery">Out For Delivery</option>
                                                <option value="delivered">Delivered</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- Filter By payment  -->
                                    <div class="form-group col-md-3">
                                        <div>
                                            <label>Filter By Payment Method</label>
                                            <select id="payment_method" name="payment_method" placeholder="Select Payment Method" required="" class="form-control">
                                                <option value="">All Payment Methods</option>
                                                <option value="COD">Cash On Delivery</option>
                                                <option value="Paypal">Paypal</option>
                                                <option value="RazorPay">RazorPay</option>
                                                <option value="Paystack">Paystack</option>
                                                <option value="Flutterwave">Flutterwave</option>
                                                <option value="Paytm">Paytm</option>
                                                <option value="Stripe">Stripe</option>
                                                <option value="Midtrans">Midtrans</option>
                                                <option value="Phonepe">Phonepe</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-3">
                                        <div>
                                            <label>Filter By Order Type</label>
                                            <select id="is_self_pick_up" name="is_self_pick_up" placeholder="Select Order Type" required="" class="form-control">
                                                <option value="">All Orders</option>
                                                <option value="1">Self Pickup</option>
                                                <option value="0">Delivery</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group col-md-4 d-flex align-items-center pt-4">
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="status_date_wise_search()">Filter Orders</button>
                                    </div>
                                    
                                </div>
                            </div>
                            <input type='hidden' id='order_user_id' value='<?= (isset($_GET['user_id']) && !empty($_GET['user_id'])) ? $_GET['user_id'] : '' ?>'>
                            <input type='hidden' id='order_partner_id' value='<?= (isset($_GET['partner_id']) && !empty($_GET['partner_id'])) ? $_GET['partner_id'] : '' ?>'>
                            <hr>
                            <table class='table-striped' data-toggle="table" data-url="<?= base_url('admin/orders/view_orders') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="o.id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel","csv"]' data-export-options='{"fileName": "orders-list","ignoreColumn": ["state"] }' data-query-params="orders_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable='true' data-footer-formatter="totalFormatter">Order ID</th>
                                        <th data-field="user_id" data-sortable='false' data-visible="false">User ID</th>
                                        <th data-field="qty" data-sortable='false' data-visible="false">Qty</th>
                                        <th data-field="name" data-sortable='true'>User Name</th>
                                        <th data-field="commission_credited" data-sortable='false' data-visible="false">Commission</th>
                                        <th data-field="partner" data-sortable='true'>Partner</th>
                                        <th data-field="mobile" data-sortable='false' data-visible='false'>Mobile</th>
                                        <th data-field="partner_commission_amount" data-sortable='false' data-visible='false'>partner Payment Amount (<?= $curreny ?>)</th>
                                        <th data-field="admin_commission_amount" data-sortable='false' data-visible='false'>Admin Commission Amount (<?= $curreny ?>)</th>
                                        <th data-field="notes" data-sortable='false' data-visible='true'>O. Notes</th>
                                        <th data-field="items" data-sortable='false' data-visible="false">Items</th>
                                        <th data-field="total" data-sortable='false' data-visible="true">Total(<?= $curreny ?>)</th>
                                        <th data-field="delivery_charge" data-sortable='false' data-footer-formatter="delivery_chargeFormatter">D.Charge</th>
                                        <th data-field="wallet_balance" data-sortable='false' data-visible="true">Wallet Used(<?= $curreny ?>)</th>
                                        <th data-field="promo_code" data-sortable='false' data-visible="false">Promo Code</th>
                                        <th data-field="deliver_by" data-sortable='false' data-visible='false'>Deliver By</th>
                                        <th data-field="promo_discount" data-sortable='false' data-visible="true">Promo disc.(<?= $curreny ?>)</th>
                                        <th data-field="delivery_tip" data-sortable='false' data-visible="true">Delivery Tip (<?= $curreny ?>)</th>
                                        <th data-field="discount" data-sortable='false' data-visible="false">Discount <?= $curreny ?>(%)</th>
                                        <th data-field="final_total" data-sortable='false'>Final Total(<?= $curreny ?>)</th>
                                        <th data-field="payment_method" data-sortable='false' data-visible="true">Payment Method</th>
                                        <th data-field="is_self_pick_up" data-sortable='false'>Order Type</th>
                                        <th data-field="owner_note" data-sortable='false' data-visible="false">Owner Note</th>
                                        <th data-field="self_pickup_time" data-sortable='false' data-visible="false">Self Pickup Time</th>
                                        <th data-field="address" data-sortable='false' data-visible='false'>Address</th>
                                        <th data-field="status" data-sortable='false' data-visible='false'>Status</th>
                                        <th data-field="active_status" data-sortable='false' data-visible='true'>Active Status</th>
                                        <th data-field="date_added" data-sortable='false'>Order Date</th>
                                        <th data-field="operate" data-sortable='false'>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div><!-- .card-innr -->
                    </div><!-- .card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>