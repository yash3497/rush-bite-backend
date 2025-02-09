<div class="">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-10">
                    <h4 class="text text-info">Rider Registration <span class='text-danger text-sm'>DO NOT REFRESH OR
                            RELOAD THIS PAGE</span></h4>
                    <div id="google_translate_element"></div>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <section class="content">
        <div class="container-fluid">
            <form class="form-horizontal form-submit-event" action="<?= base_url('rider/auth/create_rider'); ?>" method="POST"
                id='sign_up_riderss_form'>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-info">
                            <!-- form start -->
                            <form class="form-horizontal form-submit-event" action="<?= base_url('admin/riders/add_rider'); ?>" method="POST" id="add_product_form">
                                <?php if (isset($fetched_data[0]['id'])) { ?>
                                    <input type="hidden" name="edit_rider" value="<?= $fetched_data[0]['id'] ?>">
                                <?php
                                } ?>
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label for="name" class="col-sm-2 col-form-label">Name <span
                                                class='text-danger text-sm'>*</span></label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="name"
                                                placeholder="Rider Name" name="name"
                                                value="<?= @$fetched_data[0]['username'] ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="mobile" class="col-sm-2 col-form-label">Mobile <span
                                                class='text-danger text-sm'>*</span></label>
                                        <div class="col-sm-10">
                                            <input type="text" id="numberInput"
                                                oninput="validateNumberInput(this)" class="form-control"
                                                id="mobile" placeholder="Enter Mobile" name="mobile"
                                                value="<?= @$fetched_data[0]['mobile'] ?>" min="0">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="email" class="col-sm-2 col-form-label">Email <span
                                                class='text-danger text-sm'>*</span></label>
                                        <div class="col-sm-10">
                                            <input type="email" class="form-control" id="email"
                                                placeholder="Enter Email" name="email"
                                                value="<?= @$fetched_data[0]['email'] ?>">
                                        </div>
                                    </div>
                                    <?php
                                    if (!isset($fetched_data[0]['id'])) {
                                    ?>
                                        <div class="form-group row ">
                                            <label for="password" class="col-sm-2 col-form-label">Password <span
                                                    class='text-danger text-sm'>*</span></label>
                                            <div class="col-sm-10">
                                                <input type="password" class="form-control" id="password"
                                                    placeholder="Enter Passsword" name="password"
                                                    value="<?= @$fetched_data[0]['password'] ?>">
                                            </div>
                                        </div>
                                        <div class="form-group row ">
                                            <label for="confirm_password" class="col-sm-2 col-form-label">Confirm
                                                Password <span class='text-danger text-sm'>*</span></label>
                                            <div class="col-sm-10">
                                                <input type="password" class="form-control" id="confirm_password"
                                                    placeholder="Enter Confirm Password" name="confirm_password">
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="form-group row">
                                        <label for="address" class="col-sm-2 col-form-label">Address <span
                                                class='text-danger text-sm'>*</span></label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="address"
                                                placeholder="Enter Address" name="address"
                                                value="<?= @$fetched_data[0]['address'] ?>">
                                        </div>
                                    </div>

                                    <?php
                                    $city = (isset($fetched_data[0]['serviceable_city']) && $fetched_data[0]['serviceable_city'] != NULL) ? $fetched_data[0]['serviceable_city'] : "";
                                    ?>

                                    <div class="form-group row">
                                        <label for="cities" class="col-sm-2 col-form-label">Serviceable City
                                            <span class='text-danger text-sm'>*</span></label>
                                        <div class="col-sm-10">
                                            <select name="serviceable_city[]" id="serviceable_cities"
                                                class="serviceable_cities search_city w-100" multiple
                                                onload="multiselect()">
                                                <option value="">Select Serviceable City</option>
                                                <?php
                                                $this->db->select('id,name');
                                                $this->db->from('cities');
                                                $this->db->where("FIND_IN_SET(id, '$city')");
                                                $city_name = $this->db->get()->result_array();
                                                foreach ($city_name as $row) {
                                                ?>
                                                    <option value=<?= $row['id'] ?> selected> <?= output_escaping($row['name']) ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php if (isset($fetched_data[0]['id']) && !empty($fetched_data[0]['id'])) { ?>
                                        <div class="form-group">
                                            <label class="col-sm-3 col-form-label">Status <span
                                                    class='text-danger text-sm'>*</span></label>
                                            <div id="active" class="btn-group col-sm-8">
                                                <label class="btn btn-default" data-toggle-class="btn-default"
                                                    data-toggle-passive-class="btn-default">
                                                    <input type="radio" name="active" value="0"
                                                        <?= (isset($fetched_data[0]['active']) && $fetched_data[0]['active'] == '0') ? 'Checked' : '' ?>>
                                                    Deactive
                                                </label>
                                                <label class="btn btn-primary" data-toggle-class="btn-primary"
                                                    data-toggle-passive-class="btn-default">
                                                    <input type="radio" name="active" value="1"
                                                        <?= (isset($fetched_data[0]['active']) && $fetched_data[0]['active'] == '1') ? 'Checked' : '' ?>> Active
                                                </label>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <div class="form-group row">
                                        <label for="profile" class="col-sm-4 col-form-label">Rider
                                            Profile</label>
                                        <div class="col-sm-10">
                                            <?php if (isset($fetched_data[0]['profile']) && !empty($fetched_data[0]['profile'])) { ?>
                                                <span class="text-danger">*Leave blank if there is no change</span>
                                            <?php } ?>
                                            <input type="file" class="form-control" name="profile" id="profile"
                                                accept="image/*" />
                                        </div>
                                    </div>
                                    <?php if (isset($fetched_data[0]['profile']) && !empty($fetched_data[0]['profile'])) { ?>
                                        <div class="form-group ">
                                            <div class="mx-auto product-image"><a
                                                    href="<?= base_url($fetched_data[0]['profile']); ?>"
                                                    data-toggle="lightbox" data-gallery="gallery_restro"><img
                                                        src="<?= base_url($fetched_data[0]['profile']); ?>"
                                                        class="img-fluid rounded"></a></div>
                                        </div>
                                    <?php } ?>

                                    <div class="form-group">
                                        <button type="reset" class="btn btn-warning">Reset</button>
                                        <button type="submit" class="btn btn-info" id="submit_btn"><?= (isset($fetched_data[0]['id'])) ? 'Update Rider' : 'Register Rider' ?></button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                </div>
                                <!-- /.card-footer -->
                            </form>
                        </div>
                        <!--/.card-->
                    </div>
                    <!--/.col-md-12-->
                </div>
                <!-- /.row -->
                
            </form>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>