<?php 
set('id', 0);
set('title', L("dashboard_name"));

$door_open_time = find_setting_by_id(1) * 1000;//2 * 1000;
$doors = find_doors();
$controllers = find_controllers();
$presents = count_presents();
?>

<div class="content">
    <div class="container-fluid">
        <!-- <h5><?=  L("dashboard_buttons"); ?></h5> -->
        <div class="row">
            <?php if(useAPBMode()) { ?>
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
                                data-url2="http://<?= $controller->ip ?>/?/api/version"
                                data-url="http://<?= $controller->ip ?>/?/api/overview"
                                >
                                <i class="fa fa-spinner fa-spin"></i>
                            </span>
                            <?= $controller->ip ?> 
                            <a href="/?/controllers/<?= $controller->id ?>/edit"><?= apbDecorator($controller->apb, $controller->name) ?></a>
                        <?php } ?> 
                    </div>
                </div>
            </div>
            <!-- Master controller  -->
            <?php } 
                //Handle door names for Master manualy
                $doorName1 = $doors[0]->name;
                $doorName2 = $doors[1]->name;
            ?> 

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
                                <?= apbDecorator($doors[0]->apb, $doors[0]->cname) ?><br>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr>
                        <span class="lockIcon"><i class="fa fa-lg
                            fa-<?= (getOutputStatus(1) == 0) ? "lock" : "unlock-alt" ?> 
                            text-<?= (getOutputStatus(1) == 0) ? "warning" : "success" ?>"></i>
                        </span>
                        <?= showTimezoneButton($doors[0]); ?>
                        <label><?= $doorName1 ?></label><br>
                        <button class="btn btn-success btn-block"  type="button" 
                            onclick="app.timerAlert('<?= $doorName1 ?> is open', <?= $door_open_time ?>, '/?/door/1/1')">Open </button>
                        <?= showOpenCloseButtons($doors[0]); ?> 
                        <hr>
                        <span class="lockIcon"><i class="fa fa-lg
                            fa-<?= (getOutputStatus(2) == 0) ? "lock" : "unlock-alt" ?> 
                            text-<?= (getOutputStatus(2) == 0) ? "warning" : "success" ?>"></i>
                        </span>
                        <?= showTimezoneButton($doors[1]); ?>
                        <label><?= $doorName2 ?></label><br>
                        <button class="btn btn-success btn-block"  type="button" 
                            onclick="app.timerAlert('<?= $doorName2 ?> is open', <?= $door_open_time ?>, '/?/door/1/2')">Open </button>
                        <?= showOpenCloseButtons($doors[1]); ?> 
                    </div>
                </div>
            </div>
            <!-- Slave controllers  -->   
        <?php 
        $obj = new ArrayObject($doors);
        $iterator = $obj->getIterator();
        //while($iterator->valid()) {
        foreach ($iterator as &$door) {  
            $door = $iterator->current();
            //Skip Master, we did it already above, because getOutputStatus can be don locally
            if($door->controller_id === "1") continue;  

            //prepare url to be used
            $ip = "http://".$door->cip;
            ?> 
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
                                <!-- <a href="/?/doors/<?= $door->id ?>/edit"><?= $door->cname ?></a><br> -->
                                <?= apbDecorator($door->apb, $door->cname) ?>
                                <!-- <sub><?=  L("controller"); ?></sub> -->
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <hr><!-- Slave 1st door  -->
                        <span class="lockIcon" 
                            data-key="<?= $door->enum ?>"
                            data-url="<?= $ip ?>/?/api/overview"
                            >
                            <i class="fa fa-spinner fa-spin"></i>
                        </span> 
                        <?= showTimezoneButton($door); ?>
                        <label><?= $door->name ?></label><br>
                        <button class="btn btn-success btn-block"  type="button" 
                            onclick="app.timerAlert('<?= $door->name ?> is open', <?= $door_open_time ?>, '/?/door/<?= $door->controller_id ?>/<?= $door->id ?>')">Open </button>
                        <?= showOpenCloseButtons($door); ?>   
                             
                    <?php 
                        //Next door on same slave
                        //When a controller is created, also 2 doors are created, so this should be save!
                        $iterator->next();
                        $door = $iterator->current();
                    ?>  
                        <hr><!-- Slave 2nd door  -->
                        <span class="lockIcon" 
                            data-key="<?= $door->enum ?>"
                            data-url="<?= $ip ?>/?/api/overview"
                            >
                            <i class="fa fa-spinner fa-spin"></i>
                        </span> 
                        <?= showTimezoneButton($door); ?>
                        <label><?= $door->name ?></label><br>
                        <button class="btn btn-success btn-block"  type="button" 
                            onclick="app.timerAlert('<?= $door->name ?> is open', <?= $door_open_time ?>, '/?/door/<?= $door->controller_id ?>/<?= $door->id ?>')">Open </button>    
                        <?= showOpenCloseButtons($door); ?>                       
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

