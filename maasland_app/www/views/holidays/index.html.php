<?php 
set('id', 21);
set('title', L("holidays"));
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <?= iconLink_to(L::button_new." ".L::holiday, 'holidays/new', 'btn-outline') ?>
                    </div>
                    <div class="card-body">
                        <div class="card-body table-responsive">



<table class="table table-hover table-striped">
    <thead>
        <th><?=  L("id"); ?></th>
        <th><?=  L("name"); ?></th>
        <th><?=  L("startdate"); ?></th>
        <th><?=  L("enddate"); ?></th>
    </thead>
    <tbody>
        <?php foreach ($holidays as $holiday) { ?>
        <tr>
            <td><?= $holiday->id ?></td>
            <td><?= $holiday->name ?></td>
            <td><?= $holiday->start_date ?></td>
            <td><?= $holiday->end_date ?></td>
            <td><?= iconLink_to("Edit", 'holidays/'.$holiday->id.'/edit', 'btn-link', null) ?>
                &nbsp;
                <?= deleteLink_to('Delete', 'holidays', $holiday->id) ?>
            </td>
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
</div>

<script type="text/javascript">

    function myFormValidation(id) {
        console.log("myFormValidation");
        //$("#networkForm").validate();

    }

</script>