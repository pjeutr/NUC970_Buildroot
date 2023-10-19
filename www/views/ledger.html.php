<?php 
set('id', 6);
set('title', L("ledger"));
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-header ">
                        <?= iconLink_to(L("button_downloadcsv"), 'ledger_csv', 'btn-outline', 'fa fa-download') ?>
                        <?= iconLink_to('', 'ledger', 'btn-outline', 'fa fa-refresh') ?>
                        <i class="fa fa-check text-success"></i><?= $presents->hi ?>
                        /
                        <i class="fa fa-times text-danger"></i><?= $presents->bye ?>
                    </div>
                    <div class="card-body table-responsive">
                        
                        <table class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                            <thead>
                                <th><?=  L("key"); ?></th>
                                <th><?=  L("user"); ?></th>
                                <?php if(useLedgerMode()) { ?>
                                    <th><?=  L("presence"); ?></th>
                                <?php } ?>
                                <th><?=  L("time_in"); ?></th>
                                <th><?=  L("time_out"); ?></th>
                            </thead>
                            <tbody>
                            <?php foreach ($ledger as $row) { ?>
                            <tr>
                                <td><?= $row->keycode ?></td>
                                <td><?= $row->name ?></td>
                                <?php if(useLedgerMode()) { ?>
                                    <td><?= $row->present ? '<i class="fa fa-check text-success"></i>':'<i class="fa fa-times text-danger"></i>' ?></td>
                                <?php } ?>
                                <td><?= print_date($row->time_in) ?></td>
                                <td><?= $row->time_out ?></td>
                                <td><?= deleteLink_to(L::button_delete, 'ledger', $row->id) ?></td>
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
