<?php 
set('id', 3);
set('title', L("doors"));

?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                    	<?= iconLink_to(L::button_new." ".L::controller, 'controllers/new', 'btn-outline') ?>
                    </div>

                <?php 

                foreach ($controllers as $controller) { 

                ?>   
                    <div class="card-body table-responsive">
                        <div class="container-fluid border rounded">
                            <div class="row border">
                                <div class="col-sm-4 custom-header-head border-left-0">
                                   <div class="float-left">
                                        <div class="tabsub"><?php echo L::controller; ?></div>
                                        <?= $controller->name ?> 
                                    </div>
                                    <div class="float-right">
                                        <?= iconLink_to(L::button_change, 'controllers/'.$controller->id.'/edit', 'btn-link text-success', null) ?>
                                    </div>                           
                                </div>
                                <?php 

                                foreach ($doors as $row) { 
                                    if($row->controller_id == $controller->id) {
                                ?>
                                <div class="col-sm-4 custom-header">
                                    <div class="float-left">
                                        <div class="tabsub"><?php echo L::door; ?></div>
                                        <?= $row->name ?> 
                                    </div>
                                    <div class="float-right">
                                        <?= iconLink_to(L::button_change, 'doors/'.$row->id.'/edit', 'btn-link text-success', null) ?>
                                        <?= $row->timezone_id ?>
                                    </div>
                                </div>
                                <?php }
                                } ?>
                            </div>
                            <form class="doorForm" id="row" action="<?= url_for('controller', $controller->id) ?>" method="POST">
                            <input type="hidden" name="_method" id="_method" value="PUT">

                            <?php foreach ([
                                L::term_reader.(useLedgerMode()?" 1 (IN)":" 1"),
                                L::term_reader.(useLedgerMode()?" 2 (OUT)":" 2"),
                                L::term_button." 1",
                                L::term_button." 2"] 
                                as $key=>$value) { 
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

                            <?php foreach ([L::term_sensor." 1",L::term_sensor." 2"] as $key=>$value) {
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
                                      <i class="fa fa-edit"></i> <?php echo L::button_save; ?>
                                    </button>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>               
        </div>
    </div>
</div>

