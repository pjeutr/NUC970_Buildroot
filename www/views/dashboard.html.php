<?php 
set('id', 0);
set('title', L("dashboard_name"));

$door_open = find_setting_by_id(1) * 1000;//2 * 1000;
$doors = find_doors();
$controllers = find_controllers();
$presents = count_presents();
?>

<div class="content">
    <div class="container-fluid">
        <!-- <h5><?=  L("dashboard_buttons"); ?></h5> -->
        <div class="row">
            <?php if(useLedgerMode()) { ?>
            <div class="col-lg-3 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-3">
                                <div class="icon-mid text-center icon-warning">
                                    <i class="nc-icon nc-badge text-success"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <?php echo L::ledger; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer ">
                        <hr>
                        <i class="fa fa-check text-success"></i><?= $presents->hi ?> in
                        <br>
                        <i class="fa fa-times text-danger"></i><?= $presents->bye ?> out
                    </div>
                </div>
            </div>
            <?php } ?>
            <?php if(!empty($controllers)) {  ?> 
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
                                Controllers status
                            </div>
                        </div>
                    </div>
                    <div class="card-footer ">
                        <?php foreach ($controllers as $controller) {  ?> 
                            <hr>
                            <span class="statusIcon" 
                                data-url="http://<?= $controller->ip ?>/?/api/version"
                                data-url2="http://<?= $controller->ip ?>/?/api/overview"
                                >
                                <i class="fa fa-spinner fa-spin"></i>
                            </span>
                            <?= $controller->ip ?> 
                            <a href="/?/controllers/<?= $controller->id ?>/edit"><?= $controller->name ?></a>
                        <?php } ?> 
                    </div>
                </div>
            </div>
            <?php } ?> 
        <?php foreach ($doors as $door) {  ?> 
            <div class="col-lg-3 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-3">
                                <div class="icon-mid text-center icon-warning">
                                    <i class="nc-icon nc-key-25 text-success"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                <?= $door->cname ?> <sub><?=  L("controller"); ?></sub>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer ">
                        <hr>
                        <button class="btn btn-success btn-block" type="button" 
                            onclick="app.timerAlert('<?= $door->name ?> is open', <?= $door_open ?>, '/?/door/<?= $door->controller_id ?>/<?= $door->id ?>')"><?= $door->name ?> </button>

                        <button class="btn btn-warning" type="button" 
                            onclick="app.ajaxCall('/?/output/<?= $door->controller_id ?>/<?= $door->enum ?>/1')"><?=  L("open"); ?> </button>
                        <button class="btn btn-info" type="button" 
                            onclick="app.ajaxCall('/?/output/<?= $door->controller_id ?>/<?= $door->enum ?>/0')"><?=  L("close"); ?> </button>
                    
                        <!-- <hr>
                        <button class="btn btn-warning" type="button" 
                            onclick="app.ajaxCall('/?/output/<?= $door->controller_id ?>/<?= $door->enum + 2 ?>/1')"> Alarm<?=  $door->enum; ?> on </button>
                        <button class="btn btn-info" type="button" 
                            onclick="app.ajaxCall('/?/output/<?= $door->controller_id ?>/<?= $door->enum + 2?>/0')">Alarm<?=  $door->enum; ?> off</button> -->
                             
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

