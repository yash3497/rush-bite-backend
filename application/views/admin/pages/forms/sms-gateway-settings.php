<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <!-- Main content -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>SMS Gateway Settings</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item active">SMS Gateway Settings</li>
                    </ol>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <?php

    use function PHPSTORM_META\type;
    $sms = json_encode($sms_gateway_settings);

    ?>
    <section class="content">
        <input type="hidden" id="sms_gateway_settings" name="sms_gateway_settings" value='<?= $sms ?>'>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="nav" id="product-tab" role="tablist">
                        <nav class="w-100">
                            <ul class="nav nav-tabs">
                              
                            </ul>
                        </nav>
                    </div>
                    <div class="tab-content w-100" id="nav-tabContent">
                        <!-- sms gateway config -->
                        <div class="tab-pane fade active show" id="config-tab" role="tabpanel" aria-labelledby="sms-gateway-config-tab">
                            <div class="align-items-center">
                                <div class="card card-info">
                                    <!-- Button trigger modal -->
                                    <div class="align-items-baseline d-flex mt-4">
                                        <p class="mx-2 text-bold">are you confuse how to do ?? </p>
                                        <a type="button" class="text-danger" data-toggle="modal" data-target="#sms_instuction_modal">
                                            follow this for reference

                                        </a>
                                        

                                    </div>
                                    <form class="form-horizontal form-submit-event smsgateway_setting_form" action="<?= base_url('admin/Sms_gateway_settings/add_sms_data'); ?>" method="POST" id="smsgateway_setting_form" enctype="multipart/form-data">

                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="mb-3 col-md">
                                                    <label for="base_url" class="--">Base URL : </label>
                                                    </button>
                                                    <input type="text" class="form-control" id="base_url" name="base_url" value="<?= isset($sms_gateway_settings['base_url']) ? $sms_gateway_settings['base_url'] : '' ?>">
                                                </div>
                                                <div class="mb-3 col-md">
                                                    <label for="sms_gateway_method" class="form-label">Method</label>
                                                    <select id="sms_gateway_method" name="sms_gateway_method" class="form-control col-md-5">
                                                        <option value="POST">POST</option>
                                                        <option value="GET">GET</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="py-3">
                                                <h4 class="mb-3">Create Authorization Token </h4>
                                                <div class="d-flex mb-2">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="converterInputAccountSID" class="form-label">Account SID</label>
                                                            <input type="text" id="converterInputAccountSID" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="converterInputAuthToken" class="form-label">Auth Token</label>
                                                            <input type="text" id="converterInputAuthToken" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <div class="col-md-4 mb-3">
                                                        <button type="button" onClick="createHeader()" class="btn btn-info">Create</button>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <h4 id="basicToken"></h4>
                                                    </div>

                                                </div>
                                            </div>

                                            <div class="">
                                                <ul class="nav nav-tabs mb-4">
                                                    <li class="nav-item">
                                                        <a class="nav-item nav-link product-nav-tab active" id="product-header-tab" data-toggle="tab" href="#product-header" role="tab" aria-controls="product-header" aria-selected="false"><?= !empty($this->lang->line('header')) ? $this->lang->line('header') : 'Header' ?></a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-item nav-link product-nav-tab " id="product-body-tab" data-toggle="tab" href="#product-body" role="tab" aria-controls="product-body" aria-selected="false"><?= !empty($this->lang->line('body')) ? $this->lang->line('body') : 'Body' ?></a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-item nav-link product-nav-tab " id="product-params-tab" data-toggle="tab" href="#product-params" role="tab" aria-controls="product-params" aria-selected="false"><?= !empty($this->lang->line('params')) ? $this->lang->line('params') : 'Params' ?></a>
                                                    </li>
                                                </ul>
                                                <div class="tab-content w-100" id="nav-tabContent">
                                                    <!-- header -->
                                                    <div class="tab-pane fade active show" id="product-header" role="tabpanel" aria-labelledby="product-header-tab">
                                                        <div>
                                                            <div class="d-flex">
                                                                <h5 class="modal-title">Add Header data</h5>
                                                                <a href="#" id="add_sms_header" class="btn btn-primary btn-sm mx-5">
                                                                    <i class="fa fa-plus"></i>
                                                                </a>
                                                            </div>
                                                            <div class="card-body p-0">
                                                                <div id="formdata_header_section" class="col-md-8"> </div>
                                                                <div class="d-flex justify-content-center">
                                                                    <div class="form-group" id="error_box">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- product body tab -->
                                                    <div class="tab-pane fade show" id="product-body" role="tabpanel" aria-labelledby="product-body-tab">
                                                        <div class="row">
                                                            <ul class="nav nav-tabs">
                                                                <li class="nav-item">
                                                                    <a class="nav-item nav-link product-nav-tab active" id="product-text-tab" data-toggle="tab" href="#product-text" role="tab" aria-controls="product-text" aria-selected="false"><?= !empty($this->lang->line('text/JSON')) ? $this->lang->line('text/JSON') : 'text/JSON' ?></a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-item nav-link product-nav-tab " id="product-formdata-tab" data-toggle="tab" href="#product-formdata" role="tab" aria-controls="product-formdata" aria-selected="false"><?= !empty($this->lang->line('formdata')) ? $this->lang->line('formdata') : 'Formdata' ?></a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <!-- params -->
                                                    <div class="tab-pane fade" id="product-params" role="tabpanel" aria-labelledby="product-params-tab">
                                                        <div>
                                                            <div class="d-flex">
                                                                <h5 class="modal-title">Add Params </h5>
                                                                <a href="#" id="add_sms_params" class="btn btn-primary btn-sm mx-5">
                                                                    <i class="fa fa-plus"></i>
                                                                </a>
                                                            </div>
                                                            <div class="card-body p-0">
                                                                <div id="formdata_params_section" class="col-md-8"> </div>
                                                                <div class="d-flex justify-content-center">
                                                                    <div class="form-group" id="error_box">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tab-content p-3 w-100" id="nav-tabContent">
                                                    <!-- product faq tab -->
                                                    <div class="tab-pane fade" id="product-text" role="tabpanel" aria-labelledby="product-text-tab">
                                                        <div class="row">
                                                            <div class="col-12 description">
                                                                <div class="form-group col-md-12">
                                                                    <div class="mb-3">
                                                                        <textarea name="text_format_data" class="text_format_data sms_text_format_data"  rows="3" placeholder="Place some text here"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane fade" id="product-formdata" role="tabpanel" aria-labelledby="product-formdata-tab">
                                                        <div>
                                                            <div class="d-flex">
                                                                <h5 class="modal-title">Add Body data Parameter and values </h5>
                                                                <a href="#" id="add_sms_body" class="btn btn-primary btn-sm mx-5">
                                                                    <i class="fa fa-plus"></i>
                                                                </a>
                                                            </div>

                                                            <div class="card-body p-0">
                                                                <div id="formdata_section" class="col-md-8">

                                                                </div>

                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <div class="tab-content p-3 w-100" id="nav-tabContent">
                                                    <div class="tab-pane fade show" id="product-body" role="tabpanel" aria-labelledby="product-body-tab">
                                                        <div class="row">
                                                            <ul class="nav nav-tabs">
                                                                <li class="nav-item">
                                                                    <a class="nav-item nav-link product-nav-tab active" id="product-text-tab" data-toggle="tab" href="#product-text" role="tab" aria-controls="product-text" aria-selected="false"><?= !empty($this->lang->line('text/JSON')) ? $this->lang->line('text/JSON') : 'text/JSON' ?></a>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <a class="nav-item nav-link product-nav-tab " id="product-formdata-tab" data-toggle="tab" href="#product-formdata" role="tab" aria-controls="product-formdata" aria-selected="false"><?= !empty($this->lang->line('formdata')) ? $this->lang->line('formdata') : 'Formdata' ?></a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body d-flex">

                                                    <pre class="sms_gateway_setup_details">{only_mobile_number}</pre>
                                                    <pre class="sms_gateway_setup_details">{mobile_number_with_country_code}</pre>
                                                    <pre class="sms_gateway_setup_details">{country_code}</pre>
                                                    <pre class="sms_gateway_setup_details">{message}</pre>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        <button type="reset" class="btn btn-warning">Reset</button>
                                                        <button class="btn btn-info" id="sms_gateway_submit">Update SMS Gayeway Settings</button>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade sms-modal" id="sms-gateway-modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLongTitle">Custom message </h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- form start -->

                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade bd-example-modal-lg" id="sms_instuction_modal" tabindex="-1" role="dialog" aria-labelledby="sms_instuction_modal_Label" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="sms_instuction_modal_Label">Sms Gateway Configuration</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <ul>
                                            <li class="my-4">Read and follow instructions carefully while configuration sms gateway setting </li>

                                            <li class="my-4">Firstly open your sms gateway account . You can find api keys in your account -> API keys & credentials -> create api key </li>
                                            <li class="my-4">After create key you can see here Account sid and auth token </li>
                                            <div class="simplelightbox-gallery">
                                                <a href="<?= base_url('assets/admin/images/base_url_and_params.png') ?>" target="_blank">
                                                    <img src="<?= base_url('assets/admin/images/base_url_and_params.png') ?>" class="w-100">
                                                </a>
                                            </div>

                                            <li class="my-4">For Base url Messaging -> Send an SMS</li>
                                            <div class="simplelightbox-gallery">
                                                <a href="<?= base_url('assets/admin/images/api_key_and_token.png') ?>" target="_blank">
                                                    <img src="<?= base_url('assets/admin/images/api_key_and_token.png') ?>" class="w-100">
                                                </a>
                                            </div>

                                            <li class="my-4">check this for admin panel settings</li>
                                            <div class="simplelightbox-gallery">
                                                <a href="<?= base_url('assets/admin/images/sms_gateway_1.png') ?>" target="_blank">
                                                    <img src="<?= base_url('assets/admin/images/sms_gateway_1.png') ?>" class="w-100">
                                                </a>
                                            </div>
                                            <div class="simplelightbox-gallery">
                                                <a href="<?= base_url('assets/admin/images/sms_gateway_2.png') ?>" target="_blank">
                                                    <img src="<?= base_url('assets/admin/images/sms_gateway_2.png') ?>" class="w-100">
                                                </a>
                                            </div>
                                            <li class="my-4"><b>Make sure you entered valid data as per instructions before proceed</b></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>