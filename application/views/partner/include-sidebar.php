<?php $settings = get_settings('system_settings', true); ?>
<aside class="main-sidebar elevation-2 sidebar-light-info" id="admin-sidebar">
    <!-- Brand Logo -->
    <a href="<?= base_url('partner/home') ?>" class="brand-link">
        <img src="<?= base_url() . get_settings('favicon') ?>" alt="<?= $settings['app_name']; ?>" title="<?= $settings['app_name']; ?>" class="brand-image">
        <span class="brand-text font-weight-light small"><?= $settings['app_name']; ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <li class="nav-item has-treeview">
                    <a href="<?= base_url('partner/home') ?>" class="nav-link">
                        <i class="nav-icon fas fa-home text-primary"></i>
                        <p>
                            Home
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('partner/orders/') ?>" class="nav-link">
                        <i class="nav-icon fas fa-shopping-cart text-warning"></i>
                        <p>
                            Orders
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="<?= base_url('partner/category/') ?>" class="nav-link">
                        <i class="nav-icon fas fa-bullseye text-success"></i>
                        <p>
                            Categories
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('partner/tag/manage-tag') ?>" class="nav-link">
                        <i class="nav-icon fas fa-tag text-info"></i>
                        <p>
                            Tags
                        </p>
                    </a>
                </li>

                <li class="nav-item has-treeview ">
                    <a href="#" class="nav-link menu-open">
                        <i class="nav-icon fas fa-cubes text-danger"></i>
                        <p>
                            Products
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="<?= base_url('partner/attributes/manage-attribute') ?>" class="nav-link">
                                <i class="fas fa-sliders-h nav-icon"></i>
                                <p>Attributes</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= base_url('partner/taxes/') ?>" class="nav-link">
                                <i class="fas fa-percentage nav-icon"></i>
                                <p>Tax</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= base_url('partner/product/create-product') ?>" class="nav-link">
                                <i class="fas fa-plus-square nav-icon"></i>
                                <p>Add Products</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('partner/product/bulk-upload') ?>" class="nav-link">
                                <i class="fas fa-upload nav-icon"></i>
                                <p>Bulk upload</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('partner/product/') ?>" class="nav-link">
                                <i class="fas fa-boxes nav-icon"></i>
                                <p>Manage Products</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- promocode -->
                <?php 
                        $partner_details = fetch_details(['user_id' => $_SESSION['user_id']],'partner_data','permissions');
                        $partner_permission = isset($partner_details[0]['permissions']) ? json_decode($partner_details[0]['permissions'],true) : [];
                      
                        if(isset($partner_permission['partner_wise_promocode']) && $partner_permission['partner_wise_promocode'] == 1){
                
                ?>
                    <li class="nav-item">
                        <a href="<?= base_url('partner/promo-code/manage-promo-code') ?>" class="nav-link">
                            <i class="nav-icon fa fa-puzzle-piece text-warning"></i>
                            <p>
                                Promo code
                            </p>
                        </a>
                    </li>

                    <?php } ?>
                
                <!-- promocode end -->

                <li class="nav-item">
                    <a href="<?= base_url('partner/point_of_sale/') ?>" class="nav-link">
                        <i class="nav-icon fas fa-calculator text-primary"></i>
                        <p>
                            Point of sale
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('partner/media/') ?>" class="nav-link">
                        <i class="nav-icon fas fa-icons text-success"></i>
                        <p>
                            Media
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('partner/transaction/') ?>" class="nav-link">
                        <i class="fa fa-rupee-sign nav-icon text-warning"></i>
                        <p>Wallet Transactions</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('partner/payment-request/withdrawal-requests') ?>" class="nav-link">
                        <i class="nav-icon fas fa-money-bill-wave text-danger"></i>
                        <p> Withdrawal Requests</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= base_url('partner/area/manage-cities') ?>" class="nav-link">
                        <i class="fa fa-location-arrow nav-icon text-info "></i>
                        <p>City</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-pie nav-icon text-primary"></i>
                        <p>Reports
                            <i class="right fas fa-angle-left "></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('partner/sales-inventory') ?>" class="nav-link">
                                <i class="fa fa-chart-line nav-icon "></i>
                                <p>Sales And Inventory Report</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>