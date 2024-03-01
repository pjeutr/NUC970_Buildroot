<?php 
set('id', 7);
set('title', L("settings"));
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        
                    <form action="/?/settings_upload" method="POST" enctype="multipart/form-data">
                        <div class="custom-file">
                            <input type="file" name="fileToUpload" class="custom-file-input form-control" id="customFile">
                            <label class="custom-file-label form-control" for="customFile"><?=  L("choose_file"); ?></label>
                        </div>        
                        <button type="submit" name="submit" class="btn btn-success btn-outline">
                            <i class="fa fa-upload"></i> <?=  L("setting_upload"); ?>
                        </button>
                        <?= iconLink_to(L("setting_download") , 'settings_download', 'btn-outline', 'fa fa-download') ?>

                        <button class="btn btn-success btn-outline" type="button" rel="tooltip" title="Copy config to all the slaves" id="replicate_button">
                        <i class="fa fa fa-link"></i>Replicate</button>
                    </form>

                    </div>
                    <div class="card-body">
                        <div class="card-body table-responsive">
                            <div class="flex-table row header" role="rowgroup">
                              <div class="flex-row-1 first" role="columnheader"><?=  L("id"); ?></div>
                              <div class="flex-row-3" role="columnheader"><?=  L("name"); ?></div>
                              <div class="flex-row-4" role="columnheader"><?=  L("value"); ?></div>
                              <div class="flex-row-2" role="columnheader"><?=  L("action"); ?></div>
                            </div>

    <?php foreach ($settings as $row) { 
    // 1=pass
    // 2=checkbox
    // 3=number
    $fieldType = 'text';
    $fieldAtrribute = '';
    $extraClass = '';

    if( $row->type == 1) {
        $fieldType = 'password';
        $fieldAtrribute = 'data-toggle="password"';
    }
    if( $row->type == 3) {
        $fieldType = 'number';
        $fieldAtrribute = 'min="1" max="60"';
    }
    if( $row->type == 6) {
        $fieldType = 'checkbox';
        $fieldAtrribute = 'data-toggle="switch" '.($row->value ? 'checked=""': '').' data-on-color="info" data-off-color="info" data-eye-open-class="fa-toggle-off"  data-eye-close-class="fa-toggle-on"';
    }
    if( $row->type == 5) {
        $fieldType = 'text';
        $fieldAtrribute = useLedgerMode()||useLowNetworkMode() ? 'style="color:green"' : 'style="color:red"';
    }
    if( $row->type == 9) {
        $fieldType = 'text';
        $fieldAtrribute = ' id="datetimepicker"';
        $extraClass = ' datetimepicker';
    }
        ?>                        
<form class="settingsForm" id="row_<?= $row->name ?>" action="<?= url_for('settings', $row->id) ?>" method="POST">
    <input type="hidden" name="_method" id="_method" value="PUT">
    <input type="hidden" name="setting_name" value="<?= $row->name ?>">
    <input type="hidden" name="setting_type" value="<?= $row->type ?>">

    <div class="flex-table row" role="rowgroup">
        <div class="flex-row-1 flex-cell first" role="cell"><?= $row->id ?></div>
        <div class="flex-row-3 flex-cell flex-cell" role="cell"><?= L('setting_'.$row->name) ?></div>
        <div class="flex-row-4 flex-cell" role="cell">
            <?php if( $row->type == 2) { 
                //TODO fill array with lang variables
                $select_options = array(
                  1 =>"1 day",
                  7 =>"7 days",
                  14 =>"14 days",
                  30 =>"1 month",
                  182 =>"6 months",
                  365 => "1 year"
                );
            ?>
            <select name="<?= $row->name ?>" class="form-control" aria-label="Default select example">
            <?php foreach($select_options as $key => $value) { ?>
                <option <?= ($row->value == $key)?"selected":"" ?> 
                    value="<?= $key ?>"><?= $value ?></option>
            <?php }?>
            </select>
        <?php } else { ?>             
            <input type="<?= $fieldType ?>" <?= $fieldAtrribute ?> class="form-control<?= $extraClass ?>"
                name="<?= $row->name ?>" value="<?= $row->value ?>"> 
        <?php } ?>                  
        </div>
        <div class="flex-row-2 flex-cell" role="cell">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-edit"></i> <?=  L("button_save"); ?>
            </button>
        </div>

    </div>
</form>
    
<?php } ?>             
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

        $("#replicate_button").click(function () {
            app.addSpinnerWithLinkToButton(this, true, "Replicate","/?/settings_replicate");
            var self = this;
        });

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