<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Languages</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="home_breadcrumb" href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Languages</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="text-right m-2">
                        <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#language-modal">Add Language</a>
                    </div>
                    <div class="card card-info">
                        <!-- form start -->
                        <div class="card-body">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="">Languages</label>
                                    <select name="selected_language" id="selected_language" class="form-control">
                                        <?php foreach ($languages as $row) { ?>
                                            <option value="<?= $row['id'] ?>" <?= (isset($_GET['id']) && $_GET['id'] == $row['id']) ? 'selected' : '' ?>><?= $row['language'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/.card-->
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<div class="modal fade" id="language-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Language</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="add-new-language-form" action="<?= base_url('admin/language/create'); ?>" method="POST">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Name <small>(Language name should be in english)<small< /label>
                                                <input type="text" name="language" id="language" class="form-control" placeholder="Ex. English , Hindi" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Code</label>
                                    <input type="text" name="code" id="code" class="form-control" placeholder="Ex. EN , हिन्दी" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Country Code</label>
                                <input type="text" name="country_code" id="country_code" class="form-control" placeholder="IN" />
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="checkbox" name="is_rtl" class="form-checkbox" id="is_rtl_create" value="1" />
                            <label for="is_rtl_create" class="control-checkbox">Enable RTL</label>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success" id="submit_btn">Save</button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center form-group">
                        <div id="result" class="p-3"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>