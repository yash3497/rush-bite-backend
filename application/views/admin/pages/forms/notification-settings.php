<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Notification Settings</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Notification Settings</li>
                    </ol>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <form class="form-horizontal form-submit-event" action="<?= base_url('admin/Notification_settings/update_notification_settings'); ?>" method="POST" id="payment_setting_form" enctype="multipart/form-data">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="vap_id_key">Vap Id Key : <span class="text-danger fs-12">*(Required for web notifications)</span></label>
                                            <textarea class="form-control" name="vap_id_key" placeholder='Vap Id Key' rows="5"><?= $vap_id_key ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="card-body">
                                        <div class="form-group mb-0">
                                            <label for="firebase_project_id">Firebase Project ID : </label>
                                            <input type="text" id="firebase_project_id" class="form-control" name="firebase_project_id" placeholder='Firebase Project ID' value="<?= (isset($firebase_project_id) && !empty($firebase_project_id)) ? $firebase_project_id : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="card-body">
                                        <div class="form-group mb-0">
                                            <label for="firebase_project_id">Service Account File <span class="text-danger fs-12">*(Only Json File is allowed)</span> : </label>
                                            <input type="file" name="service_account_file" id="service_account_file" class="form-contol" placeholder="Service Account File" accept=".json">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                    <button type="submit" class="btn btn-info" id="submit_btn">Update Notification Settings</button>
                                </div>


                            </div>
                        </form>
                    </div>
                    <!--/.card-->
                </div>
                <!--/.col-md-12-->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>