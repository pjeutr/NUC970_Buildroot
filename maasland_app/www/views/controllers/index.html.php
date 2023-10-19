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
                                <th><?=  L("id"); ?></th>
                                <th><?=  L("online"); ?></th>
                                <th><?=  L("name"); ?></th>
                                <th><?=  L("ip"); ?></th>
                            </thead>
                            <tbody>
                                <?php foreach ($controllers as $row) { ?>
                                <tr>
                                	<td><?= $row->id ?></td>
                                    <td><div class="statusIcon" 
                                        data-url="http://<?= $row->ip ?>/?/api/overview">
                                        <i class="fa fa-spinner fa-spin"></i>
                                    </div></td>
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

