<!--
=========================================================

 Coded by Sloots.nu

=========================================================

-->
<?php
if(! isset($title)) {
    $title = "";
}

if(! isset($id)) {
    $id = 2;
}

//Calculate time for js clock
$serverTime =  time() * 1000;
$timezone = date('2'); //+0200

//check error message
$flashMessage = flash_now();

?>
<!DOCTYPE html>
<html lang="<?= $_SESSION["lang"] ?>">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../../assets/img/favicon.ico">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Maasland Dashboard</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!-- CSS Files -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/fontawesome.min.css" rel="stylesheet" />
    <!-- TODO hamburger, radio en checkbox werken niet bij de lokale fontawesome, daarom de online versie als backup -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <link href="/assets/css/light-bootstrap-dashboard.css?v=2.0.3" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="/assets/css/app.css?2.0.3" rel="stylesheet" />
    <script type="text/javascript">
        //calculate clock with php server time
        var serverTime = <?php echo $serverTime;?>,
            timezone = "<?php echo $timezone;?>",
            timeDiff = serverTime - Date.now();

        //console.log(serverTime+"-"+timezone+"-"+timeDiff);
        setInterval(function () {
          serverClock.innerHTML= moment().add(timeDiff).utcOffset(timezone).format('DD-MM-Y HH:mm:ss');
        }, 1000);

    </script>    
</head>

<body>
    <div class="wrapper">
        <div class="loaderImage" style="display: none;">
            <img src="/assets/img/spinner.gif">
        </div>  
        <div class="sidebar" data-image="../assets/img/sidebar.jpg" data-color="black">
            <div class="sidebar-wrapper">
                <div class="logo">
                    <a href="./" class="simple-text logo-mini">
                        <img width="30px" class="rounded" src="../../assets/img/apple-icon.png">
                    </a>
                    <a href="./" class="simple-text logo-normal text-left">
                        Maasland<br>
                    </a>
                </div>
                <ul class="nav">
                    <li <?php echo ($id == 0) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/dash">
                            <i class="nc-icon nc-chart-pie-35"></i>
                            <p><?=  L::dashboard_name ?></p>
                        </a>
                    </li>
                    <li <?php echo ($id == 1) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/users">
                            <i class="nc-icon nc-single-02"></i>
                            <p><?php echo L::users; ?></p>
                        </a>
                    </li>
                    <li <?php echo ($id == 2) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/groups">
                            <i class="nc-icon nc-badge"></i>
                            <p><?php echo L::groups; ?></p>
                        </a>
                    </li>
                    <li <?php echo ($id == 3) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/doors">
                            <i class="nc-icon nc-lock-circle-open"></i>
                            <p><?php echo L::doors; ?></p>
                        </a>
                    </li>
                    <li <?php echo ($id == 4) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/timezones">
                            <i class="nc-icon nc-watch-time"></i>
                            <p><?php echo L::timezones; ?></p>
                        </a>
                    </li>
                    <li <?php echo ($id == 5) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/reports">
                            <i class="nc-icon nc-notes"></i>
                            <p><?php echo L::reports; ?></p>
                        </a>
                    </li>

                    <?php if(useLedgerMode()) { ?>
                        <li <?php echo ($id == 6) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                            <a class="nav-link" href="./?/ledger">
                                <i class="nc-icon nc-bullet-list-67"></i>
                                <p><?php echo L::ledger; ?></p>
                            </a>
                        </li>
                    <?php } ?>

                    <li <?php echo ($id == 7) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/settings">
                            <i class="nc-icon nc-settings-gear-64"></i>
                            <p><?php echo L::settings; ?></p>
                        </a>
                    </li>

                    <?php if( isset($_SESSION["dev"]) ) { ?>
                    <hr>
                    <li <?php echo ($id == 11) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="/admin/phpliteadmin.php">
                            <i class="nc-icon nc-settings-tool-66"></i>
                            <p>DB</p>
                        </a>
                    </li>
                    <li <?php echo ($id == 10) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/manage/cleanup_db">
                            <i class="nc-icon nc-settings-90"></i>
                            <p>Prune reports</p>
                        </a>
                    </li>
                    <li <?php echo ($id == 12) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="/examples/icons.html">
                            <i class="nc-icon nc-html5"></i>
                            <p>Icons</p>
                        </a>
                    </li>
                    <li <?php echo ($id == 11) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/manage/opcache_reset">
                            <i class="nc-icon nc-settings-90"></i>
                            <p>Opcache reset</p>
                        </a>
                    </li>
                    <li <?php echo ($id == 11) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="/opcache-gui/index.php">
                            <i class="nc-icon nc-settings-90"></i>
                            <p>Opcache management</p>
                        </a>
                    </li>
                    <li <?php echo ($id == 11) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/tests/status.php">
                            <i class="nc-icon nc-settings-90"></i>
                            <p>Status test</p>
                        </a>
                    </li>
                    <li <?php echo ($id == 11) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/tests/network.sh">
                            <i class="nc-icon nc-settings-90"></i>
                            <p>Network test</p>
                        </a>
                    </li>
                    <li <?php echo ($id == 11) ? 'class="nav-item active"' : 'class="nav-item "' ?>>
                        <a class="nav-link" href="./?/manage/update_firmware">
                            <i class="nc-icon nc-settings-90"></i>
                            <p>Update firmware (WARNING!)</p>
                        </a>
                    </li>
                    <?php } ?>

                </ul>
                <div class="sidebar-footer">
                	<?=  L::language ?><br/>
                	<!--- https://github.com/tkrotoff/famfamfam_flags --->

                    <?php
                        $fr = "";
                        $en = "";
                        $nl = "";
                        if($_SESSION["lang"]=="nl") { 
                            $nl = "active";
                        } elseif($_SESSION["lang"]=="fr") {
                            $fr = "active";
                        } else {
                            $en = "active";
                        }
                    ?>
                    <a class="btn btn-success btn-sm <?= $nl ?>" href="./?/lang/nl"><i class="icon-flag-nl"></i> NL</a>
                    <a class="btn btn-success btn-sm <?= $fr ?>" href="./?/lang/fr"><i class="icon-flag-fr"></i> FR</a>
                    <a class="btn btn-success btn-sm <?= $en ?>" href="./?/lang/en"><i class="icon-flag-gb"></i> EN</a>                
                </div>
                <div class="sidebar-footer">
                    Flexess Duo v<?= GVAR::$DASHBOARD_VERSION ?>
                </div>
            </div>
        </div>
        <div class="main-panel">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg " color-on-scroll="500">
                <div class="container-fluid">
                    <div class="navbar-wrapper">
                        <div class="navbar-minimize">
                            <button id="minimizeSidebar" class="btn btn-success btn-fill btn-round btn-icon d-none d-lg-block">
                                <i class="fa fa-ellipsis-v visible-on-sidebar-regular"></i>
                                <i class="fa fa-navicon visible-on-sidebar-mini"></i>
                            </button>
                        </div>
                        <a class="navbar-brand" href="#wim"><?php echo $title ?></a>
                    </div>
                    <button href="" class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar burger-lines"></span>
                        <span class="navbar-toggler-bar burger-lines"></span>
                        <span class="navbar-toggler-bar burger-lines"></span>
                    </button>
                    <div class="collapse navbar-collapse justify-content-between" id="navigation">
                        <ul class="nav navbar-nav ml-auto">
                            <sub><div id="serverClock"><?= 
                            (new DateTime("now", new DateTimeZone('Europe/Amsterdam')))
                            ->format("d-m-Y H:i:s") ?></div></sub>
                        </ul> 
                        <ul class="nav navbar-nav ml-auto">
                            <!--
                            <li class="dropdown nav-item">
                                <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                                    <i class="nc-icon nc-planet"></i>
                                    <span class="notification">5</span>
                                    <span class="d-lg-none">Notification</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <a class="dropdown-item" href="#">12:34 Main door opened by John</a>
                                    <a class="dropdown-item" href="#">06:23 Fire alarm</a>
                                    <a class="dropdown-item" href="#">06:14 Main door opened by Daenerys </a>
                                    <a class="dropdown-item" href="#">02:34 Back door opened by Arya</a>
                                </ul>
                            </li>  
                        -->
                            <li class="nav-item">
                                <a href="./?/logout" class="nav-link">
                                    <i class="nc-icon nc-key-25"></i> <?=  L("button_logout"); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <!-- End Navbar -->

            <!-- Display error message, if available -->
            <?= isset($message['message']) ? '<div class="alert alert-danger">'.$message['message'].'</div>' : "" ?>
            <!-- flashMessage is used by controller and user during db constraint error -->
            <?= isset($flashMessage['message']) ? '<div class="alert alert-danger">'.$flashMessage['message'].'</div>' : "" ?>
            <?php echo $content ?>
            
            <footer class="footer">
                <div class="container-fluid">
                    <nav>
                        <ul class="footer-menu">
                            <li>
                                <a href="https://www.maaslandgroep.nl/" target="_blank">
                                    Company
                                </a>
                            </li>
                            <li>
                                <a href="https://maaslandserver.com/flexessduo/contact" target="_blank">
                                    Contact
                                </a>
                            </li>
                            <li>
                                <a href="https://maaslandserver.com/flexessduo/faq" target="_blank">
                                    Faq
                                </a>
                            </li>
                            <li>
                                <a href="https://maaslandserver.com/flexessduo/blog" target="_blank">
                                    Blog
                                </a>
                            </li>
                        </ul>
                        <p class="copyright text-center">
                            ©
                            <script>
                                document.write(new Date().getFullYear())
                            </script>
                            <a href="https://maaslandgroup.com/">Maasland Group</a>, Your Access To Safety. All Rights Reserved.
                        </p>
                    </nav>
                </div>
            </footer>
        </div>
    </div>
</body>

<!--   Core JS Files   -->
<script src="/assets/js/core/jquery.3.2.1.min.js" type="text/javascript"></script>
<script src="/assets/js/core/popper.min.js" type="text/javascript"></script>
<script src="/assets/js/core/bootstrap.min.js" type="text/javascript"></script>
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
<script src="/assets/js/plugins/bootstrap-switch.js"></script>
<!--  Notifications Plugin    -->
<script src="/assets/js/plugins/bootstrap-notify.js"></script>
<!--  Plugin for Date Time Picker and Full Calendar Plugin-->
<script src="/assets/js/plugins/moment.min.js"></script>
<!--  DatetimePicker   -->
<script src="/assets/js/plugins/bootstrap-datetimepicker.js"></script>
<!--  Sweet Alert  -->
<script src="/assets/js/plugins/sweetalert2.min.js" type="text/javascript"></script>
<!--  Tags Input  -->
<script src="/assets/js/plugins/bootstrap-tagsinput.js" type="text/javascript"></script>
<!--  Sliders  -->
<script src="/assets/js/plugins/nouislider.js" type="text/javascript"></script>
<!--  Bootstrap Select  -->
<script src="/assets/js/plugins/bootstrap-selectpicker.js" type="text/javascript"></script>
<!--  jQueryValidate https://jqueryvalidation.org  -->
<script src="/assets/js/plugins/jquery.validate.min.js" type="text/javascript"></script>
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="/assets/js/plugins/jquery.bootstrap-wizard.js"></script>
<!--  Bootstrap Table Plugin -->
<script src="/assets/js/plugins/bootstrap-table.js"></script>
<!--  DataTable Plugin -->
<script src="/assets/js/plugins/jquery.dataTables.min.js"></script>
<!--  Full Calendar   -->
<script src="/assets/js/plugins/fullcalendar.min.js"></script>
<!--  JQuery Plugin: WeekDays https://www.jqueryscript.net/time-clock/inline-week-day-picker.html -->
<script src="/assets/js/plugins/jquery-weekdays.js"></script>
<!--  Hide Password  -->
<script src="/assets/js/plugins/bootstrap-show-password.min.js"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="/assets/js/light-bootstrap-dashboard.js?v=2.0.1" type="text/javascript"></script>
<script src="/assets/js/resource.<?= $_SESSION["lang"] ?>.js?1.1.4"></script>
<script src="/assets/js/app.js?1.1.4"></script>
<script type="text/javascript">
    // Content for SweetAlert
    <?= empty($swalMessage) ? '' : 'swal( '.$swalMessage.');' ?>
    <?= isset($flashMessage['swalMessage']) ? 'swal( '.swal_message_success($flashMessage['swalMessage']).');' : '' ?>
</script>
</html>

