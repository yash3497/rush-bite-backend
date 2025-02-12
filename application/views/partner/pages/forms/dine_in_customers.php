<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Dine in Customers</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="home_breadcrumb" href="<?= base_url('partner/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Dine in customers</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        <div class="container-fluid p-3">
            <div class="row pt-4">
                <!-- card start -->
                
                <?php for ($i = 0; $i < count($tables); $i++) {
                    $guest =  fetch_details(['table_id' => $tables[$i]['table_id']], 'dine_in_cart', 'table_id,guest');
                ?>

                    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                        <div class="card pull-up">
                            <div class="card-content table_cart button" id=<?= $tables[$i]['table_id']; ?> data-bs-toggle="modal" data-bs-target="#myModal">
                                <div class="card-body">
                                    <div class="media d-flex">
                                        <div class="align-self-center text-danger">
                                            <i class="ion-ios-cart-outline display-4"></i>
                                        </div>
                                        <div class="media-body text-right">
                                            <h5 class="d-none"><?= $tables[$i]['table_id']; ?></h5>
                                            <h5 class="text-muted text-bold-500">Floor : <?= $tables[$i]['floor_name']; ?></h5>
                                            <h5 class="text-muted text-bold-500">Table : <?= $tables[$i]['table_name']; ?></h5>
                                            <h5 class="text-muted text-bold-500">Guests : <?= $guest[0]['guest']; ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php  } ?>
                <!-- card end  -->
            </div>

            <!-- The Modal -->




        </div>
    </section>
    <div class="modal" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title">Modal Heading</h4>
                </div>

                <!-- Modal body -->
                <div class="modal-body customer_cart">

                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger cart_modal" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
</div>