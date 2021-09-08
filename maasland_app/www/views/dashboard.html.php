<?php 
set('id', 0);
set('title', 'Dashboard');

$door_open = find_setting_by_id(1) * 1000;//2 * 1000;
$doors = find_doors();

?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-3">
                                <div class="icon-mid text-center icon-warning">
                                    <i class="nc-icon nc-vector text-danger"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                Master Controller
                            </div>
                        </div>
                    </div>
                    <div class="card-footer ">
                        <hr>
                        <?php foreach ($doors as $row) {  ?>
                        <button class="btn btn-<?= getGPIO(getDoorGPIO($row->id)) == 1 ? "success" : "info" ?> btn-block" type="button" 
                            onclick="app.timerAlert('Door 1 is open', <?= $door_open ?>, '/?/door/<?= $row->id ?>')"><?= $row->name ?> <?= getGPIO(getDoorGPIO($row->id)) == 1 ? " is open" : "" ?></button>
                        <?php } ?>
                        <!-- <hr>
                            <button class="btn btn-warning btn-small" type="button">Door sensor 1</button>
                            <button class="btn btn-warning btn-small" type="button">Door sensor 2</button>
                             -->
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card card-stats">
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-3">
                                <div class="icon-mid text-center icon-warning">
                                    <i class="nc-icon nc-vector text-danger"></i>
                                </div>
                            </div>
                            <div class="col-7">
                                Second Controller
                            </div>
                        </div>
                    </div>
                    <div class="card-footer ">
                        <hr>
                        <button class="btn btn-info btn-block" type="button" 
                            onclick="app.timerAlert('Door 1 is open', <?= $door_open ?>, '/?/controller/2/input/6')">Open Door 1</button>
                        <a class="btn btn-info btn-block" 
                            href="http://maasland/?/controller/2/input/6">Open Door 2</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Maasland - Flexess Duo</h4>
                    </div>
                    <div class="card-body">
                                <?=  L("dashboard_title", ":"); ?>
                        <div class="typography-line">
                            <p>
                                <span>Hardware</span>

                                <ul>
                                    <li>2 relays outputs - to connect to doorlocks</li>
                                    <li>2 wiegand inputs - to connect to keypad or NFC reader</li>
                                    <li>2 alarm outputs - to connect to alarms</li>
                                    <li>2 monitor inputs - to connect to door monitors</li>
                                    <li>UTP connector - to connect to an LAN</li>
                                    <li>A voltage in - to connect 8-24VDC</li>
                                </ul>

                            </p>
                        </div>
                        <div class="typography-line">
                            <span>Configuration</span>

                                    <ol>
                                    <li>Add doors from this Master controller, or from other Slave controllers</li>
                                    <li>Add timezones (24h and working hours are predefined)</li>
                                    <li>Create groups with timezones</li>
                                    <li>Create users and assign them to a group</li>
                                    <li>Add keypad code or NFC token to the user</li>
                                </ol>
                        </div>
                        <small>
                             <a href="https://docs.google.com/document/d/1ZOc3w04Ov9EECDrhrmd3qmBAxG5lbiUyGuRfasSbtHw/edit" target="docs">Here</a> you can find extra documentation
                        </small>
                    </div>
                </div>
            </div>
        </div>              
    </div>
</div>

