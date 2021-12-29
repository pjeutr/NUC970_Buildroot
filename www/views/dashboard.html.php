<?php 
set('id', 0);
set('title', L("dashboard_name"));

$door_open = find_setting_by_id(1) * 1000;//2 * 1000;
$doors = find_doors();
?>

<div class="content">
    <div class="container-fluid">
        <h5><?=  L("dashboard_buttons"); ?></h5>
        <div class="row">
        <?php foreach ($doors as $door) {  ?> 
            <div class="col-lg-3 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-3">
                                <div class="icon-mid text-center icon-warning">
                                    <i class="nc-icon nc-vector text-success"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <?= $door->cname ?> <sub><?=  L("controller"); ?></sub>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer ">
                        <hr>
                        <button class="btn btn-info btn-block" type="button" 
                            onclick="app.timerAlert('<?= $door->name ?> is open', <?= $door_open ?>, '/?/door/<?= $door->controller_id ?>/<?= $door->id ?>')"><?= $door->name ?> </button>



<!-- <button class="btn btn-<?= getGPIO(getDoorGPIO($door->id)) == 1 ? "success" : "info" ?> btn-block" type="button" 
                            onclick="app.timerAlert('Door 1 is open', <?= $door_open ?>, '/?/door/<?= $door->controller_id ?>/<?= $door->id ?>')"><?= $door->name ?> <?= getGPIO(getDoorGPIO($door->id)) == 1 ? " is open" : "" ?></button> -->


                        <!-- <hr>
                            <button class="btn btn-warning btn-small" type="button">Door sensor 1</button>
                            <button class="btn btn-warning btn-small" type="button">Door sensor 2</button>
                             -->
                    </div>
                </div>
            </div>
        <?php } ?>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Maasland - Flexess Duo</h4>
                    </div>
                    <div class="card-body">
                        <?=  L("dashboard_title", ":"); ?>
                        <?=  L("dashboard_text1"); ?>
                        <?=  L("dashboard_text2"); ?>
                    </div>
                </div>
            </div>
        </div>              
    </div>
</div>

