<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Database Bakup</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Database Bakup</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Select Tables for Backup or Delete</h3>
                </div>
                <div class="card-body">
                    <form id="database-operation-form">
                        <div class="form-group">
                            <label>Select Tables:</label>
                            <div class="row">
                                <?php if (!empty($tables)): ?>
                                    <?php foreach ($tables as $index => $table): ?>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="tables[]" value="<?= $table ?>" id="table_<?= $index ?>">
                                                <label class="form-check-label" for="table_<?= $index ?>"><?= $table ?></label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No tables found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="button" id="backup-btn" class="btn btn-primary">Backup</button>
                            <button type="button" id="delete-btn" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>