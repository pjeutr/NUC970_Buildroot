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
                            <div class="flex-table row header" role="rowgroup">
                              <div class="flex-row-1 first" role="columnheader"><?=  L("id"); ?></div>
                              <div class="flex-row-3" role="columnheader"><?=  L("name"); ?></div>
                              <div class="flex-row-4" role="columnheader"><?=  L("value"); ?></div>
                              <div class="flex-row-2" role="columnheader"><?=  L("action"); ?></div>
                            </div>

                       
<form class="neworkForm" action="<?= url_for('network') ?>" method="POST">
    <input type="hidden" name="_method" id="_method" value="PUT">

    <div class="flex-table row" role="rowgroup">
        <div class="flex-row-3 flex-cell flex-cell" role="cell">Master IP<br>
            <sub>This overules automatic discovery of the Master controller for slaves<br>
            Only use in special Network situation, where multicast doesn't work.</sub>
        </div>
        <div class="flex-row-4 flex-cell" role="cell">
            <input type="text" class="form-control"
                name="master" value="127.0.0.1"> 
        </div>
        <div class="flex-row-2 flex-cell" role="cell">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-edit"></i> <?=  L("button_save"); ?>
            </button>
        </div>

    </div>

    <div class="flex-table row" role="rowgroup">
        <div class="flex-row-3 flex-cell flex-cell" role="cell">DHCP</div>
        <div class="flex-row-4 flex-cell" role="cell">
            <select name="dhcp" class="form-control">
                <option>enabled</option>
                <option disabled="">static</option>
            </select>
        </div>
        <div class="flex-row-2 flex-cell" role="cell">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-edit"></i> <?=  L("button_save"); ?>
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

<script type="text/javascript">
    function settingsFormValidation(id) {
        console.log("settingsFormValidation");
        //$("#settingsForm").validate();

        // Show the name of the file appear on select
        $(".custom-file-input").on("change", function() {
          var fileName = $(this).val().split("\\").pop();
          console.log("fileName:"+fileName);
          $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
        /*
        * Validation docs https://jqueryvalidation.org/documentation/
        */
        $("#row_door_open").validate({
            rules: {
                door_open: {
                    range: [1, 60],
                    required: true,
                    number: true
                }
            }
        });
        $("#row_apb").validate({
            rules: {
                door_open: {
                    range: [1, 60],
                    required: true,
                    number: true
                }
            }
        });
        $("#row_alarm").validate({
            rules: {
                door_open: {
                    range: [1, 60],
                    required: true,
                    number: true
                }
            }
        });
        $("#row_hostname").validate({
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
        $("#row_password").validate({
            rules: {
                password: {
                    required: true,
                    maxlength: 30
                }
            },
            // messages: {
            //     password: "Please enter a password",

            // },
            errorPlacement: function(error, element) {
                error.insertAfter(".input-group");
            },
        });
    }

</script>