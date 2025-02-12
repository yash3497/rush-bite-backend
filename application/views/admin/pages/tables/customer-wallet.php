<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <!-- Main content -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h4>Customer Wallet Transactions </h4>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a class="home_breadcrumb" href="<?= base_url('admin/home') ?>">Home</a></li>
            <li class="breadcrumb-item active">Customer Wallet</li>
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <!-- start -->
        <div class="col-md-6 ">
          <div class="card card-info">
            <!-- form start -->
            <form class="form-horizontal form-submit-event" action="<?= base_url('admin/customer/update_customer_wallet'); ?>" method="POST" enctype="multipart/form-data">
              <div class="card-body">
                <input type='hidden' name="user_id" id="user_id" value='' />
                <input type='hidden' name="status" id="status" value='success' />
                <div class="form-group row">
                  <label for="name" class="col-sm-2 col-form-label">Details <span class='text-danger text-sm'>*</span></label>
                  <div class="col-sm-10">
                    <textarea class="form-control" rows="3" id="details" disabled></textarea>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="type" class="col-sm-2 col-form-label"> Type</label>
                  <div class="col-sm-10">
                   
                    <input type="text" name="type" id="credit" value="credit" class="form-control" readonly>

                  </div>
                </div>
                <div class="form-group row">
                  <label for="name" class="col-sm-2 col-form-label">Amount<span class='text-danger text-sm'>*</span></label>
                  <div class="col-sm-10">
                    <input type="number" name="amount" id="amount" class="form-control">
                  </div>
                </div>
                <div class="form-group row">
                  <label for="name" class="col-sm-2 col-form-label">Message<span class='text-danger text-sm'>*</span> </label>
                  <div class="col-sm-10">
                    <textarea class="form-control" rows="3" name="message" id="message"></textarea>
                  </div>
                </div>
                <div class="form-group">
                  <button type="reset" class="btn btn-warning">Reset</button>
                  <button type="submit" class="btn btn-info" id="submit_btn"> Submit </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="col-md-6 ">
          <div class="card content-area p-4">
            <div class="card-header bg-white border-0 h5">Select User</div>
            <div class="card-innr">
              <div class="gaps-1-5x"></div>
              <table class='table table-striped' id="customer_details" data-toggle="table" data-url="<?= base_url('admin/customer/view_wallet_customer') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-query-params="transaction_query_params">
                <thead>
                  <tr>
                  
                    <th data-field="state" data-radio="true"></th>
                    <th data-field="id" data-sortable="true">ID</th>
                    <th data-field="username" data-sortable="false">Name</th>
                    <th data-field="email" data-sortable="true">Email</th>
                    <th data-field="mobile" data-sortable="true">Mobile No</th>
                    <th data-field="balance" data-sortable="true">Balance</th>

                  </tr>
                </thead>
              </table>
            </div><!-- .card-innr -->
          </div><!-- .card -->
        </div>
        <!-- end -->
        <div class="col-md-12 main-content">
          <div class="card content-area p-4">
            <div class="card-innr">
              <div class="gaps-1-5x"></div>
              <table class='table-striped' data-toggle="table" data-url="<?= base_url('admin/transaction/view_transactions') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-query-params="customer_wallet_query_params">
                <thead>
                  <tr>
                    <th data-field="id" data-sortable="true">ID</th>
                    <th data-field="name" data-sortable="false">User Name</th>
                    <th data-field="type" data-sortable="false">Type</th>
                    <th data-field="amount" data-sortable="false">Amount</th>
                    <th data-field="status" data-sortable="false">Status</th>
                    <th data-field="message" data-sortable="false">Message</th>
                    <th data-field="date" data-sortable="false">Date</th>
                  </tr>
                </thead>
              </table>
            </div><!-- .card-innr -->
          </div><!-- .card -->
        </div>
      </div>
      <!-- /.row -->
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>