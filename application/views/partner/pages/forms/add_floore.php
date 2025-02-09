<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Add Floore</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="home_breadcrumb" href="<?= base_url('partner/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Floore</li>
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
                        <form class="form-horizontal form-submit-event" action="<?= base_url('partner/table_booking/add_floore'); ?>" method="POST" enctype="multipart/form-data">
                            <div class="card-body">


                                <?php if (isset($fetched_data[0]['id'])) { ?>
                                    <input type="hidden" name="edit_floore" value="<?= @$fetched_data[0]['id'] ?>">
                                <?php  } ?>
                                <div class="form-group row">
                                    <label for="title" class="col-sm-2 col-form-label">Floore Name <span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" placeholder="Title" name="title" value="<?= @$fetched_data[0]['title'] ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                    <button type="submit" class="btn btn-info" id="submit_btn"><?= (isset($fetched_data[0]['id'])) ? 'Update Floore' : 'Add Floore' ?></button>
                                </div>
                            </div>
                           
                            <!-- /.card-body -->

                        </form>
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