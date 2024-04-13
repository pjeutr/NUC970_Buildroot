<?php 
set('id', 8);
set('title', L("network"));
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                    </div>
                    <div class="card-body">
                        <div class="card-body table-responsive">

<!-- Show extra warning message alert  -->
<?= alert_message(L("warning_change_unreachable"), $title="Oops", $type="alert-danger") ?>

<hr>
<?= L("warning_change_network") ?>
<p>
<?php foreach (find_controllers() as $controller) {  ?>
    <a href="http://<?= $controller->ip ?>/?/manage/network" target="<?= $controller->ip ?>"><?= $controller->name ?>-<?= $controller->name ?></a><br>
<?php } ?>
</p>


<form class="networkForm" action="<?= url_for('network', 1) ?>" method="POST">
    <input type="hidden" name="_method" id="_method" value="PUT">

    <div class="flex-table row" role="rowgroup">
        <div class="flex-row-3 flex-cell flex-cell" role="cell">DHCP</div>
        <div class="flex-row-4 flex-cell" role="cell">
            <input class="toggle-dhcp" name="dhcp" type="checkbox" data-toggle="switch" 
            <?= $network["dhcp"] ? 'checked' : ""  ?>
            data-on-color="info" data-off-color="info" data-eye-open-class="fa-toggle-off"  data-eye-close-class="fa-toggle-on">
        </div>
        <div class="flex-row-2 flex-cell" role="cell">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-edit"></i> <?=  L("button_save"); ?>
            </button>
        </div>
    </div>

    <div id="static_config" 
        <?= $network["dhcp"] ? 'style="display: none;"' : ""  ?>
    >

        <div class="flex-table row" role="rowgroup">
            <div class="flex-row-3 flex-cell flex-cell" role="cell">IPv4 Address</div>
            <div class="flex-row-4 flex-cell" role="cell">
                <input name="ip" type="text" minlength="7" maxlength="15" size="15" 
                value = "<?= $network["ip"] ?>"
                pattern="^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$">
            </div>
        </div>

        <div class="flex-table row" role="rowgroup">
            <div class="flex-row-3 flex-cell flex-cell" role="cell">Subnet Mask</div>
            <div class="flex-row-4 flex-cell" role="cell">
                <input name="subnet" type="text" minlength="7" maxlength="15" size="15" 
                value = "<?= $network["subnet"] ?>"
                pattern="^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$">
            </div>
        </div>

        <div class="flex-table row" role="rowgroup">
            <div class="flex-row-3 flex-cell flex-cell" role="cell">Gateway</div>
            <div class="flex-row-4 flex-cell" role="cell">
                <input name="router" type="text" minlength="7" maxlength="15" size="15" 
                value = "<?= $network["router"] ?>"
                pattern="^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$">
            </div>
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

<script type="text/javascript">
    function networkFormValidation(id) {
        console.log("neworkFormValidation");
        //$("#networkForm").validate();

        var master = $("[name='master']").bootstrapSwitch();
        master.on("switchChange.bootstrapSwitch", function(event, state) {
            console.log("change="+state);
            if(state){
                $('#master_config').hide();
            } else {
                $('#master_config').show();
            }
        });

        var dhcp = $("[name='dhcp']").bootstrapSwitch();
        dhcp.on("switchChange.bootstrapSwitch", function(event, state) {
            console.log("change="+state);
            if(state){
                $('#static_config').hide();
            } else {
                $('#static_config').show();
            }
        });
        /*
        * Validation docs https://jqueryvalidation.org/documentation/
        */
        $("#networkForm").validate({
            rules: {
                maxLength: 4,
                hostname: {
                    required: true,
                    maxlength: 30
                }
            }
            // ,
            // messages: {
            //     hostname: "Please enter a hostname",
            // },
        });
        $("#masterForm").validate({
            rules: {
                maxLength: 4,
                hostname: {
                    required: true,
                    maxlength: 30
                }
            }
            // ,
            // messages: {
            //     hostname: "Please enter a hostname",
            // },
        });
    }

</script>