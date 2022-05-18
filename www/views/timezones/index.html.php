<?php 
set('id', 4);
set('title', L("timezones"));
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <?= iconLink_to(L::button_new." ".L::timezone, 'timezones/new', 'btn-outline') ?>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <th><?=  L("id"); ?></th>
                                <th><?=  L("name"); ?></th>
                                <th><?=  L("start"); ?></th>
                                <th><?=  L("end"); ?></th>
                                <th><?=  L("weekdays"); ?></th>
                                <th><?=  L("action"); ?></th>
                            </thead>
                            <tbody>
<?php foreach ($timezones as $row) { ?>
<tr>
    <td><?= $row->id ?></td>
    <td><?= $row->name ?></td>
    <td><?= $row->start //date("H:i", $row->start) ?></td>
    <td><?= $row->end //date("H:i", $row->end) ?></td>
    <td><?= weekDaysPlus($row->weekdays) ?></td>
    <!-- <td><?= link_to($row->name, 'timezones', $row->id) ?></td> -->
    <td><?= iconLink_to(L::button_edit, 'timezones/'.$row->id.'/edit', 'btn-link', null) ?>
        &nbsp;
        <?= deleteLink_to(L::button_delete, 'timezones', $row->id) ?>
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
