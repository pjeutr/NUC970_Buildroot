<?php 
set('id', 1);
set('title', 'Users');
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                    	<?= iconLink_to('New user', 'users/new', 'btn-outline', 'fa-user') ?>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Keycode</th>
                                <th>Group</th>
                                <th>Visits</th>
                                <th>Last seen</th>
                                <th>Action</th>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row) { ?>
                                <tr>
                                	<td><?= $row->id ?></td>
                                    <td><?= $row->name ?></td>
                                    <td><?= $row->keycode ?></td>
                                    <td><?= $row->group_name ?></td>
                                    <td><?= $row->visit_count ?></td>
                                    <td><?= $row->last_seen ?></td>
                                    <td><?= iconLink_to("Edit", 'users/'.$row->id.'/edit', 'btn-link', null) ?>
                                    	&nbsp;
                                    	<?= deleteLink_to('Delete', 'users', $row->id) ?>
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

