<?php 
set('id', 3);
set('title', 'Doors');

$controller = $controllers[0];
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                    	<!-- <?= iconLink_to('New door', 'doors/new', 'btn-outline') ?> -->
                    </div>
                    <div class="card-body table-responsive">
                        <div class="container-fluid border rounded">
                            <div class="row border">
                                <div class="col-sm-4 custom-header-head border-left-0">
                                        <?= $controller->name ?> Controller                               
                                </div>
                                <?php 

                                foreach ($doors as $row) { 

                                ?>
                                <div class="col-sm-4 custom-header">
                                    <div class="float-left">
                                        <?= $row->name ?> <?= $row->timezone_id ?>
                                    </div>
                                    <div class="float-right">
                                        <?= iconLink_to("Change", 'doors/'.$row->id.'/edit', 'btn-link text-success', null) ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <form class="doorForm" id="row" action="<?= url_for('controller', 1) ?>" method="POST">
                            <input type="hidden" name="_method" id="_method" value="PUT">

                            <?php foreach (["Reader 1","Reader 2","Button 1","Button 2"] as $key=>$value) { 
                                $switch_1 = $controller->reader_1;
                                $switch_2 = $controller->reader_2;
                                $switch_3 = $controller->button_1;
                                $switch_4 = $controller->button_2;
                                $nr = $key + 1; ?>

                                <div class="row border border-top-0">
                                    <div class="col-sm-4 p-3 bg-custom">
                                        <?= $value ?> 
                                    </div>
                                    <div class="col-sm-4 custom-cell">
                                        <input class="form-check-input" type="radio" 
                                        <?= (${'switch_'.$nr} == "1") ? 'checked' : ''?>  
                                        name="switch[<?= $nr ?>]" value="1"><!-- Door 1 -->
                                    </div>
                                    <div class="col-sm-4 custom-cell">
                                        <input class="form-check-input" type="radio" 
                                        <?= (${'switch_'.$nr} == "2") ? 'checked' : ''?> 
                                        name="switch[<?= $nr ?>]" value="2"><!-- Door 2 -->
                                    </div>
                                </div>

                            <?php } ?>

                            <div class="row border">
                                <div class="col-sm-4 custom-header border-left-0">
                                                                 
                                </div>
                                <div class="col-sm-4 custom-header">
                                    Alarm 1
                                </div>
                                <div class="col-sm-4 custom-header">
                                    Alarm 2
                                </div>
                            </div>

                            <?php foreach (["Sensor 1","Sensor 2"] as $key=>$value) {
                                $nr = $key + 1; ?>

                                <div class="row border border-top-0">
                                    <div class="col-sm-4 p-3 bg-custom">
                                        <?= $value ?> 
                                    </div>
                                    <div class="col-sm-4 custom-cell">
                                        <input class="form-check-input" type="radio" 
                                        <?= ($controller->{'sensor_'.$nr} == "1") ? 'checked' : ''?>  
                                        name="sensor[<?= $nr ?>]" value="1"><!-- Alarm 1 -->
                                    </div>
                                    <div class="col-sm-4 custom-cell">
                                        <input class="form-check-input" type="radio" 
                                        <?= ($controller->{'sensor_'.$nr} == "2") ? 'checked' : ''?> 
                                        name="sensor[<?= $nr ?>]" value="2"><!-- Alarm 2 -->
                                    </div>
                                </div>

                            <?php } ?>

                            <div class="row border border-top-0">
                                <div class="col-sm-4 p-3 custom-header"></div>
                                <div class="col-sm-8 p-3 d-flex justify-content-center">
                                    <button type="submit" class="btn btn-success btn-outline">
                                      <i class="fa fa-edit"></i> Save
                                    </button>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>               
        </div>
    </div>
</div>

