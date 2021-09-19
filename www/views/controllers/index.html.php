<?php 
set('id', 1);
set('title', 'Contollers');
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                    	<?= iconLink_to('New controller', 'controllers/new', 'btn-outline', 'fa-user') ?>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Ip</th>
                            </thead>
                            <tbody>
                                <?php foreach ($controllers as $row) { ?>
                                <tr>
                                	<td><?= $row->id ?></td>
                                    <td><?= $row->name ?></td>
                                    <td><?= $row->ip ?></td>
                                    <td><?= iconLink_to("Edit", 'controllers/'.$row->id.'/edit', 'btn-link', null) ?>
                                    	&nbsp;
                                    	<?= deleteLink_to('Delete', 'controllers', $row->id) ?>
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

