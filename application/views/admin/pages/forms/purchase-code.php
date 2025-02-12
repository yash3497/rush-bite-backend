<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>eRestro Purchase Code Validator</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="text text-info"
                                href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Purchase Code</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class=" content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <!-- app purchase code  -->
                        <?php $doctor_brown = get_settings('doctor_brown', true);
                        if (empty($doctor_brown) && !isset($doctor_brown['code_bravo'])) { ?>
                            <form class="form-horizontal form-submit-event"
                                action="<?= base_url('admin/purchase-code/validator'); ?>" method="POST"
                                enctype="multipart/form-data">
                                <div class="card-body">
                                    <div class="form-group row"> <label for="purchase_code_app"
                                            class="col-sm-2 col-form-label">eRestro App Purchase Code<span
                                                class='text-danger text-sm'>*</span></label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="purchase_code_app"
                                                placeholder="Enter your app purchase code here" name="purchase_code_app"
                                                value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="reset" class="btn btn-warning">Reset</button>
                                        <button type="submit" class="btn btn-info" id="submit_btn">
                                            <?= (isset($fetched_data[0]['id'])) ? 'Register' : 'Register Now' ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php } ?>
                        <!-- end -->
                        <!-- web purchasecode  -->
                        <?php $web_doctor_brown = get_settings('doctor_brown_web', true);

                        if (empty($web_doctor_brown) && !isset($web_doctor_brown['code_bravo'])) { ?>
                            <form class="form-horizontal form-submit-event"
                                action="<?= base_url('admin/purchase-code/validator'); ?>" method="POST"
                                enctype="multipart/form-data">
                                <div class="card-body">
                                    <div class="form-group row"> <label for="purchase_code_web"
                                            class="col-sm-2 col-form-label">eRestro Web Purchase Code<span
                                                class='text-danger text-sm'>*</span></label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="purchase_code_web"
                                                placeholder="Enter your web purchase code here" name="purchase_code_web"
                                                value="">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="reset" class="btn btn-warning">Reset</button>
                                        <button type="submit" class="btn btn-info" id="submit_btn">
                                            <?= (isset($fetched_data[0]['id'])) ? 'Register' : 'Register Now' ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php } ?>
                        <!-- end -->
                        <!-- app deregister -->
                        <?php $doctor_brown = get_settings('doctor_brown', true);
                        if (!empty($doctor_brown) && isset($doctor_brown['code_bravo'])) { ?>
                            <div class="row">
                                <div class="col-md-6 mt-2 pl-5">

                                    <div class="alert alert-success">
                                        Your system is successfully registered with us! Enjoy selling online!
                                    </div>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary" name="erestro_deregister_app"
                                            id="erestro_deregister_app"
                                            value="<?= $doctor_brown['code_bravo']; ?>">De-register App</button>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <!-- end -->
                        <!-- web deregister -->
                        <?php $doctor_brown_web = get_settings('doctor_brown_web', true);
                        if (!empty($doctor_brown_web) && isset($doctor_brown_web['code_bravo'])) { ?>
                            <div class="row">
                                <div class="col-md-6 mt-2 pl-5">

                                    <div class="alert alert-success">
                                        Your system is successfully registered with us! Enjoy selling online!
                                    </div>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary" name="erestro_deregister_web"
                                            id="erestro_deregister_web"
                                            value="<?= $doctor_brown_web['code_bravo']; ?>">De-register web</button>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <!-- end -->
                    </div>
                    <!--/.card-->
                </div>
                <!--/.col-md-12-->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>