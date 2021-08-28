<?php 
set('id', 5);
set('title', 'Reports');
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-header ">
                        <?= iconLink_to('Download csv', 'reports_csv', 'btn-outline', 'fa fa-download') ?>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table  table-hover table-striped">
                            <thead>
                                <th>ID</th>
                                <th>Keycode</th>
                                <th>User</th>
                                <th>Door</th>  
                                <th>Time</th>
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
