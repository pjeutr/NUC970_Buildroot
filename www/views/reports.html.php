<?php 
set('id', 5);
set('title', L("reports"));
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-header ">
                        <?= iconLink_to(L("button_downloadcsv"), 'reports_csv', 'btn-outline', 'fa fa-download') ?>
                        <?= iconLink_to('', 'reports', 'btn-outline', 'fa fa-refresh') ?>
                    </div>
                    <div class="card-body table-responsive">
                        
                        <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                            <thead>
                                <th><?=  L("id"); ?></th>
                                <th><?=  L("key"); ?></th>
                                <th><?=  L("user"); ?></th>
                                <th><?=  L("door"); ?></th>
                                <th><?=  L("time"); ?></th>
                            </thead>
                            <tbody>
                            <?php foreach ($reports as $row) { ?>
                            <tr>
                                <td><?= $row->id ?></td>
                                <td><?= $row->keycode ?></td>
                                <td><?= $row->user ?></td>
                                <td><?= $row->door ?></td>
                                <td><?= print_date($row->created_at) ?></td>
                            </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>
