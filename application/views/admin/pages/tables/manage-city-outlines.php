<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Deliverable Area</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="home_breadcrumb" href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Deliverable Area</li>
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
                        <div class="card-body">
                            <h4>Deliverable Area for City </h4>
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group border border-secondary rounded">
                                        <ol type="I">
                                            <li>Search your city in which you will deliver the foods and city points.</li>
                                            <li>Please edit Map or City Deliverable Area in desktop. It may not work in mobile device.</li>
                                            <li>Here you can set city zone to deliver the orders. </li>
                                            <li>Recommended you to use polygon to accurate the area for deliverable zone</li>
                                            <li> <strong>Note: </strong> If you are drawing big zone for multiple city then please add that all cities in your main city.</li>
                                            <li>For example, you are selecting 3 cities <strong>bhuj, mundra, mandvi</strong> Here main city will be BHUJ so you need to set other two cities to this main city in <strong>Releted Deliverable Cities</strong> section in city menu.</li>
                                        </ol>
                                    </div>
                                    <input type="hidden" name="city_outlines" id="city_outlines" value="">
                                    <div class="form-group ">
                                        <label for="city" class="control-label col-md-12">Select City <span class='text-danger text-xs'>*</span></label>
                                        <div class="col-md-6">
                                          
                                            <select class="target form-control" name="city" id="city_id">
                                                <option value="geolocation_type">---Select City---</option>
                                                <?php foreach ($fetched_data as $row) { ?>
                                                    <option value="<?= $row['latitude'] . ',' . $row['longitude'] ?>" data-city_id="<?= $row['id'] ?>" data-geolocation_type="<?= $row['geolocation_type'] ?>" data-boundary_points='<?= $row['boundary_points'] ?>' data-radius="<?= $row['radius'] ?>"><?= $row['name'] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group mt-5 d-none">
                                        <label for="latitudesandlongitudes" class="control-label col-md-12">Boundry Points<span class='text-danger text-xs'>*</span> </label>
                                        <textarea class="form-control" placeholder="here will be your selected outlines latitude and longitude" name="vertices" id="vertices" cols="30" rows="10"></textarea>
                                    </div>

                                    <div class="offset-5 ">
                                        <input id="remove-line" type="button" class="btn btn-primary mb-3 btn-xs" value="Remove Newly Added Line" />
                                        <input id="clear-line" type="button" class="btn btn-danger mb-3 btn-xs" value="Clear Map" />
                                        <input id="add-line" type="button" class="btn btn-success mb-3 btn-xs" value="Restore Old Map" />
                                    </div>
                                    
                                    <div class="map-canvas" id="map-canvas"></div>
                                    <button type="button" class="btn btn-info mt-3" id="save_city">Save Boundries</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/.card-->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $google_map_api_key ?>&libraries=drawing&v=weekly">