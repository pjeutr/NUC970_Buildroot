<?php 
set('id', 1);
set('title', L("users"));
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                    	<?= iconLink_to(L::button_new." ".L::user, 'users/new', 'btn-outline', 'fa-user') ?>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <th><?=  L("id"); ?></th>
                                <th><?=  L("name"); ?></th>
                                <th><?=  L("key"); ?></th>
                                <th><?=  L("group"); ?></th>
                                <th><?=  L("visits"); ?></th>
                                <th><?=  L("lastseen"); ?></th>
                                <th><?=  L("action"); ?></th>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row) { ?>
                                <tr <?= is_user_active($row) ? "" : " class='danger' " ?>>
                                	<td><?= $row->id ?></td>
                                    <td><?= $row->name ?></td>
                                    <td><?= $row->keycode ?></td>
                                    <td><?= $row->group_name ?></td>
                                    <td><?= $row->visit_count ?></td>
                                    <td><?= $row->last_seen ?></td>
                                    <td><?= iconLink_to(L("button_edit"), 'users/'.$row->id.'/edit', 'btn-link', null) ?>
                                    	&nbsp;
                                    	<?= deleteLink_to(L("button_delete"), 'users', $row->id) ?>
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

