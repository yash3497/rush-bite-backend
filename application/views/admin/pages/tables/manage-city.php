<?php $system_settings = get_settings("system_settings", true);
$javascript_map_key = $system_settings['google_map_javascript_api_key'];
$map_url = "https://maps.googleapis.com/maps/api/js?key=$javascript_map_key&libraries=drawing,places&v=weekly"
    ?>
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<script async defer src="<?= $map_url ?>" />
</script>
<script>
    $(document).ready(function () {
        initMap();
    });
</script>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Manage City's Center Points <small>(Latitude & Longitude)</small> </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"> <a class="btn btn-xs btn-dark text-white" data-toggle="modal"
                                data-target="#howItWorksModal" title="How it works">How city will work?</a></li>
                        <li class="breadcrumb-item"><a class="home_breadcrumb"
                                href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">City</li>
                    </ol>

                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <!-- form start -->
                        <form class="form-horizontal form-submit-event" action="<?= base_url('admin/area/add_city'); ?>"
                            method="POST" id="add_product_form" enctype="multipart/form-data">
                            <input type="hidden" name="range_wise_charges" id="range_wise_charges" value="">
                            <input type="hidden" id="edit_city" name="edit_city" value="">
                            <div class="card-body">
                                <?php if (!isset($fetched_data[0]['id'])) { ?>
                                    <div class="row">
                                        <div class="col-md-4 map-div">
                                            <label for="city_name">Search City</label>
                                            <input id="city-input" type="text" class="form-control"
                                                placeholder="Enter a location" />
                                            </br>
                                            <span class="text text-primary">Search your city where you will deliver the food
                                                and to find co-ordinates.</span>
                                        </div>
                                        <div class="col-md-8">
                                            <div id="map"></div>
                                            <div id="infowindow-content">
                                                <span id="place-name" class="title"></span><br />
                                                <span id="place-address"></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="latitude">Latitude <span
                                                    class='text-danger text-sm'>*</span></label>
                                            <input type="number" min="0" step="0.000000000000000001" readonly
                                                class="form-control" name="latitude" id="city_lat"
                                                value="<?= (isset($fetched_data[0]['latitude']) ? $fetched_data[0]['latitude'] : '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="longitude">Longitude <span
                                                    class='text-danger text-sm'>*</span></label>
                                            <input type="number" min="0" step="0.000000000000000001" readonly
                                                class="form-control" name="longitude" id="city_long"
                                                value="<?= (isset($fetched_data[0]['longitude']) ? $fetched_data[0]['longitude'] : '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="city_name">City Name <span
                                                    class='text-danger text-sm'>*</span></label>
                                            <input type="text" readonly class="form-control" name="city_name"
                                                id="city_name"
                                                value="<?= (isset($fetched_data[0]['name']) ? $fetched_data[0]['name'] : '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="">Min order amount for free delivery</label>
                                            <input type="number" oninput="validateNumberInput(this)"
                                                class="form-control" name="min_order_amount_for_free_delivery"
                                                id="min_order_amount_for_free_delivery"
                                                value="<?= @$fetched_data[0]['min_order_amount_for_free_delivery'] ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <!-- take time per kilometer because we have calculated distance in km -->
                                    <label for="time_to_travel">Time to travel 1 (km) <span
                                            class='text-danger text-sm'>*</span> <small>(Enter in minutes)</small>
                                    </label>
                                    <input type="number" class="form-control" name="time_to_travel" id="time_to_travel"
                                        value="<?= @$fetched_data[0]['time_to_travel'] ?>" min="0">
                                </div>
                                <div class="form-group ">
                                    <label for="max_deliverable_distance"> Maximum Delivarable Distance<span
                                            class='text-danger text-xs'>*</span> <small>[Kilometre]</small></label>
                                    <input type="number" class="form-control" name="max_deliverable_distance"
                                        id="max_deliverable_distance"
                                        value="<?= @$fetched_data[0]['max_deliverable_distance'] ?>"
                                        placeholder="Enter Delivarable Maximum Distance in km" min='0' />
                                </div>
                                <div class="form-group ">
                                    <label for="cities">Releted Deliverable Cities <span
                                            class='text-danger text-sm'>*</span></label>

                                    <select name="bordering_city_ids[]" id="bordering_city_ids"
                                        class="search_city w-100 form-control" multiple onload="multiselect()">

                                    </select>

                                </div>
                                <div class="form-group">
                                    <label for="delivery_charge_method">Delivery Charge Methods <span
                                            class='text-danger text-sm'>*</span></label>
                                    <select class='form-control' name="delivery_charge_method"
                                        id="delivery_charge_method">
                                        <option value=''>Select Method</option>
                                        <option value='fixed_charge'
                                            <?= (isset($fetched_data[0]['delivery_charge_method']) && $fetched_data[0]['delivery_charge_method'] == 'fixed_charge') ? 'selected' : '' ?>>Fixed Delivery Charges</option>
                                        <option value='per_km_charge'
                                            <?= (isset($fetched_data[0]['delivery_charge_method']) && $fetched_data[0]['delivery_charge_method'] == 'per_km_charge') ? 'selected' : '' ?>>Per KM Delivery Charges</option>
                                        <option value='range_wise_charges'
                                            <?= (isset($fetched_data[0]['delivery_charge_method']) && $fetched_data[0]['delivery_charge_method'] == 'range_wise_charges') ? 'selected' : '' ?>>Range Wise Delivery Charges</option>
                                    </select>
                                </div>
                                <hr>
                                <div class="form-group d-none" id="fixed_charge_input">
                                    <label for="fixed_charge">Fixed Delivery Charges <span
                                            class='text-danger text-sm'>*</span></label>
                                    <input type="number" class="form-control" name="fixed_charge" id="fixed_charge"
                                        value="<?= @$fetched_data[0]['fixed_charge'] ?>"
                                        placeholder="Global Flat Charges" min="0">
                                </div>
                                <div class="form-group d-none" id="per_km_charge_input">
                                    <label for="per_km_charge">Per KM Delivery Charges <span
                                            class='text-danger text-sm'>*</span> </label>
                                    <input type="number" class="form-control" name="per_km_charge" id="per_km_charge"
                                        value="<?= @$fetched_data[0]['per_km_charge'] ?>"
                                        placeholder="Per Kilometer Delivery Charge" min="0">
                                </div>
                                <div class="form-group col-sm-12 d-none" id="range_wise_charges_input">
                                    <label for="range_wise_charges">Range Wise Delivery Charges <span
                                            class='text-danger text-sm'>* </span> <span
                                            class='text-secondary text-sm'>(Set Proper ranges for delivery charge. Do
                                            not repeat the range value to next range. For e.g. 1-3,4-6)</span> </label>

                                    <?php for ($i = 1; $i < 16; $i++) { ?>
                                        <div class="form-group row">
                                            <div><?= $i . '. ' ?></div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="from_range[]"
                                                    id="from_range<?= $i ?>" placeholder="From Range" min="0">
                                            </div>
                                            <div class="btn  btn-secondary">To</div>
                                            <div class="col-sm-2">
                                                <input type="number" class="form-control" name="to_range[]"
                                                    id="to_range<?= $i ?>" placeholder="To Range" min="0">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" class="form-control" name="price[]" id="price<?= $i ?>"
                                                    placeholder="Price" min="0">
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group  d-none" id="range_wise_charges_input_btn">
                                    <button type="button" class="btn btn-info ml-3" id="save_charges">Save
                                        Charges</button>
                                </div>
                                <div class="form-group">
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                    <button type="submit" class="btn btn-info" id="submit_btn"> Submit</button>
                                </div>

                            </div>
                           
                        </form>
                    </div>
                    <!--/.card-->
                </div>
                <div class="modal fade edit-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">Edit City</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-12 main-content">
                        <div class="card content-area p-4">
                            <div class="card-head">
                                <h4 class="card-title">City Details</h4>
                            </div>
                            <hr>
                            <div class="card-innr">
                                <div class="gaps-1-5x"></div>
                                <table class='table-striped' data-toggle="table"
                                    data-url="<?= base_url('admin/area/view_city') ?>" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-toolbar="" data-show-export="true" data-maintain-selected="true"
                                    data-export-types='["txt","excel"]' data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">ID</th>
                                            <th data-field="name" data-sortable="false">Name</th>
                                            <th data-field="latitude" data-sortable="false" data-visible="false">
                                                Latitude</th>
                                            <th data-field="longitude" data-sortable="false" data-visible="false">
                                                Longitude</th>
                                            <th data-field="bordering_city_ids" data-sortable="false"
                                                data-visible="true">Bordering City Ids</th>
                                            <th data-field="time_to_travel" data-sortable="false" data-visible="false">
                                                Time to Travel 1 km(in Minutes)</th>
                                            <th data-field="geolocation_type" data-sortable="false"
                                                data-visible="false">Geolocation Type</th>
                                            <th data-field="radius" data-sortable="false" data-visible="false">Radius
                                            </th>
                                            <th data-field="boundary_points" data-sortable="false" data-visible="false">
                                                Boundary Points</th>
                                            <th data-field="max_deliverable_distance" data-sortable="false"
                                                data-visible="false">Max Deliverable Distance</th>
                                            <th data-field="delivery_charge_method" data-sortable="false"
                                                data-visible="false">Delivery Charge Method</th>
                                            <th data-field="delivery_charge_amount" data-sortable="false"
                                                data-visible="false">Delivery Charge Amount</th>
                                            <th data-field="min_order_amount_for_free_delivery" data-sortable="false">
                                                Min order amount for free delivery</th>
                                            <th data-field="operate" data-sortable="false" data-events="actionEvents">
                                                Actions</th>

                                        </tr>
                                    </thead>
                                </table>
                            </div><!-- .card-innr -->
                        </div><!-- .card -->
                    </div>
                </div>
            </div>
            <div class="modal fade" id="howItWorksModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel">How city will work?</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body ">
                            <ol>
                                <li>Search City: First search city you want to delivery the orders online.</li>
                                <li>Latitude & Longitude will automatically fill by selecting the city from map</li>
                                <li>Also City name will be fill</li>
                                <li>Time to travel: Here you have to set in minutes to travel 1 km in city.</li>
                                <li>Maximum Delivarable Distance [KM] : Here you have to set Maximum Delivarable for
                                    order that Distance from user to restaurant. </li>
                                <li>Releted Deliverable Cities: Hre you have to set reletedd city which are nearby
                                    city.This city you can add befire main city. </li>
                                <li>For example, If you want to add <strong>Bhuj City</strong> and their nearest cities
                                    are <strong>Mundra, mandvi etc.</strong> So you ahve to add that nearby cities
                                    first.</li>
                                <li>After adding that nearest cities then you can add Bhuj city. Here you can select
                                    that all nearest cities which you have added for bhuj city.</li>
                                <li>By adding this <strong> Releted Deliverable Cities</strong> All partners which are
                                    in these cities, they al will list in application.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>