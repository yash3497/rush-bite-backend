<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid p-3">
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"> <i class="ion-ios-cart-outline display-4"></i></span>
                       
                        <div class="info-box-content">
                            <h3><?= number_format($order_counter) ?></h3>
                            <p>Orders</p>
                            <a href="<?= base_url('admin/orders/') ?>" class="small-box-footer homepage_cards">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"> <i class="ion-ios-personadd-outline display-4"></i></span>
                      
                        <div class="info-box-content">
                            <h3><?= number_format($user_counter) ?></h3>
                            <p>New Signups</p>
                            <a href="<?= base_url('admin/customer/') ?>" class="small-box-footer homepage_cards">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"> <i class="ion-ios-people-outline display-4"></i></span>
                      
                        <div class="info-box-content">
                            <h3><?= number_format($rider_counter) ?></h3>
                            <p>Riders</p>
                            <a href="<?= base_url('admin/riders/manage-rider') ?>" class="small-box-footer homepage_cards">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"> <i class="fas fa-hamburger"></i></span>
                       
                        <div class="info-box-content">
                            <h3><?= number_format($partner_counter) ?></h3>
                            <p>Partners</p>
                            <a href="<?= base_url('admin/partners/') ?>" class="small-box-footer homepage_cards">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-12" id="ecommerceChartView">
                    <div class="card card-shadow chart-height">
                        <div class="m-3">Sales Analytics</div>
                        <div class="card-header card-header-transparent py-20 border-0">
                            <ul class="nav nav-pills nav-pills-rounded chart-action float-right btn-group" role="group">
                                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#scoreLineToDay">Day</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#scoreLineToWeek">Week</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#scoreLineToMonth">Month</a></li>
                            </ul>
                        </div>
                        <div class="widget-content tab-content bg-white p-20">
                            <div class="ct-chart tab-pane active scoreLineShadow" id="scoreLineToDay"></div>
                            <div class="ct-chart tab-pane scoreLineShadow" id="scoreLineToWeek"></div>
                            <div class="ct-chart tab-pane scoreLineShadow" id="scoreLineToMonth"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-12">
                    <!-- Category Wise Product's Sales -->
                    <div class="card ">
                        <h3 class="card-title m-3">Category Wise Product's Count</h3>
                        <div class="card-body">
                            <div id="piechart_3d" class='piechat_height'></div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"> <i class="ion-cash display-4"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Earnings <?= "(" . $curreny . ")" ?> </span>
                            <span class="info-box-number"><?= number_format($total_earnings) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box bg-success">
                        <span class="info-box-icon "> <i class="ion-cash display-4"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Admin Earnings <?= "(" . $curreny . ")" ?></span>
                            <span class="info-box-number"><?= number_format($admin_total_earnings) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"> <i class="ion-cash display-4"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Partner Earnings <?= "(" . $curreny . ")" ?></span>
                            <span class="info-box-number"><?= number_format($partner_total_earnings)  ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h6><i class="icon fa fa-info"></i> <?= $count_products_availability_status ?> Product(s) sold out!</h6>
                        <a href="<?= base_url('admin/product/?flag=sold') ?>" class="text-decoration-none small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <?php $settings = get_settings('system_settings', true); ?>
                <div class="col-md-6 col-xs-12">
                    <div class="alert alert-primary alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h6><i class="icon fa fa-info"></i> <?= $count_products_low_status ?> Product(s) low in stock!<small> (Low stock limit <?= isset($settings['low_stock_limit']) ? $settings['low_stock_limit'] : '5' ?>)</small></h6>
                        <a href="<?= base_url('admin/product/?flag=low') ?>" class="text-decoration-none small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- top partner -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="m-3">Top Partners</div>
                                <div class="card-body">
                                    <table class='table-striped' id='partner_table' data-toggle="table" data-url="<?= base_url('admin/partners/top_partner') ?>" data-click-to-select="true" data-side-pagination="server" data-show-columns="true" data-show-refresh="true" data-sort-name="pd.id" data-sort-order="DESC" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-field="partner_id" data-sortable="true">ID</th>
                                                <th data-field="partner_name" data-sortable="true">Partner name</th>
                                                <th data-field="total_sales" data-sortable="false">Total</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- top category -->
                        <div class="col-md-6 ">
                            <div class="card h-100">
                                <div class="m-3">Top Categories</div>
                                <div class="card-body">
                                    <table class='table-striped' id='partner_table' data-toggle="table" data-url="<?= base_url('admin/Category/top_category')  ?>" data-click-to-select="true" data-side-pagination="server" data-show-columns="true" data-show-refresh="true" data-sort-name="pd.id" data-sort-order="DESC" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable="true">ID</th>
                                                <th data-field="name" data-sortable="false">Category Name</th>
                                                <th data-field="clicks" data-sortable="false">Clicks</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- end -->
                    </div>
                </div><br>
                <!-- end -->
                <h5 class="col"><br>Order Outlines</h5><br>
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
                        <div class="small-box bg-dark">
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
                                <p>Out For Delivery</p>
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
                <!-- Best partners -->

                <!-- end -->
                <div class="col-md-12 main-content">
                    <div class="card content-area p-4">
                        <div class="card-innr">
                            <div class="gaps-1-5x row d-flex adjust-items-center">
                                <div class="row col-md-12">
                                    <div class="form-group col-md-4">
                                        <label>Date and time range:</label>
                                        <div class="input-group col-md-12">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="far fa-clock"></i></span>
                                            </div>
                                            <input type="text" class="form-control float-right" id="datepicker">
                                            <input type="hidden" id="start_date" class="form-control float-right">
                                            <input type="hidden" id="end_date" class="form-control float-right">
                                        </div>
                                        <!-- /.input group -->
                                    </div>
                                    <div class="form-group col-md-4">
                                        <div>
                                            <label>Filter Orders By status</label>
                                            <select id="order_status" name="order_status" placeholder="Select Status" required="" class="form-control">
                                                <option value="">All Orders</option>
                                                <option value="awaiting">Awaiting</option>
                                                <option value="pending">Pending</option>
                                                <option value="ready_for_pickup">Ready For Pickup</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="preparing">Preparing</option>
                                                <option value="out_for_delivery">Out For Delivery</option>
                                                <option value="delivered">Delivered</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    </div>
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

                                    <div class="form-group col-md-4 d-flex align-items-center pt-4">
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="status_date_wise_search()">Filter</button>
                                    </div>
                                </div>
                            </div>
                            <table class='table-striped' data-toggle="table" data-url="<?= base_url('admin/orders/view_orders') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel","csv"]' data-export-options='{
                        "fileName": "order-list",
                        "ignoreColumn": ["state"] 
                        }' data-query-params="home_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable='true' data-footer-formatter="totalFormatter">Order ID</th>
                                        <th data-field="user_id" data-sortable='false' data-visible="false">User ID</th>
                                        <th data-field="partner" data-sortable='true'>Partner</th>
                                        <th data-field="qty" data-sortable='false' data-visible="false">Qty</th>
                                        <th data-field="name" data-sortable='true'>User Name</th>
                                        <th data-field="commission_credited" data-sortable='false' data-visible="false">Commission</th>
                                        <th data-field="mobile" data-sortable='false' data-visible="false">Mobile</th>
                                        <th data-field="items" data-sortable='false' data-visible="false">Items</th>
                                        <th data-field="total" data-sortable='false' data-visible="true">Total(<?= $curreny ?>)</th>
                                        <th data-field="delivery_charge" data-sortable='false' data-footer-formatter="delivery_chargeFormatter" data-visible="true">D.Charge</th>
                                        <th data-field="wallet_balance" data-sortable='false' data-visible="true">Wallet Used(<?= $curreny ?>)</th>
                                        <th data-field="promo_code" data-sortable='false' data-visible="false">Promo Code</th>
                                        <th data-field="promo_discount" data-sortable='false' data-visible="true">Promo disc.(<?= $curreny ?>)</th>
                                        <th data-field="delivery_tip" data-sortable='false' data-visible="true">Delivery Tip (<?= $curreny ?>)</th>
                                        <th data-field="discount" data-sortable='false' data-visible="false">Discount <?= $curreny ?>(%)</th>
                                        <th data-field="final_total" data-sortable='false'>Final Total(<?= $curreny ?>)</th>
                                        <th data-field="deliver_by" data-sortable='false' data-visible='false'>Deliver By</th>
                                        <th data-field="payment_method" data-sortable='false' data-visible="true">Payment Method</th>
                                        <th data-field="address" data-sortable='false'>Address</th>
                                        <th data-field="delivery_date" data-sortable='false' data-visible='false'>Delivery Date</th>
                                        <th data-field="delivery_time" data-sortable='false' data-visible='false'>Delivery Time</th>
                                        <th data-field="notes" data-sortable='false' data-visible='false'>O. Notes</th>
                                        <th data-field="status" data-sortable='false' data-visible='false'>Status</th>
                                        <th data-field="active_status" data-sortable='false' data-visible='true'> Active Status</th>
                                        <th data-field="date_added" data-sortable='false'>Order Date</th>
                                        <th data-field="operate">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div><!-- .card-innr -->
                    </div><!-- .card -->
                </div>
            </div>
        </div>
    </section>
</div>