<?php 
set('id', 7);
set('title', 'Settings');
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
                              <div class="flex-row-1 first" role="columnheader">ID</div>
                              <div class="flex-row-3" role="columnheader">Name</div>
                              <div class="flex-row-4" role="columnheader">Value</div>
                              <div class="flex-row-2" role="columnheader">Action</div>
                            </div>

    <?php foreach ($settings as $row) { 
    // 1=pass
    // 2=checkbox
    // 3=number
    $fieldType = 'text';
    $fieldAtrribute = '';

    if( $row->type == 1) {
        $fieldType = 'password';
        $fieldAtrribute = 'data-toggle="password"';
    }
    if( $row->type == 3) {
        $fieldType = 'number';
        $fieldAtrribute = 'min="1" max="60"';
    }
    if( $row->type == 2) {
        $fieldType = 'checkbox';
        $fieldAtrribute = 'data-toggle="switch" '.($row->value ? 'checked=""': '').' data-on-color="info" data-off-color="info" data-eye-open-class="fa-toggle-off"  data-eye-close-class="fa-toggle-on"';
    }
        ?>                        
<form class="settingsForm" id="row<?= $row->id ?>" action="<?= url_for('settings', $row->id) ?>" method="POST">
    <input type="hidden" name="_method" id="_method" value="PUT">
    <input type="hidden" name="setting_name" value="<?= $row->name ?>">
    <input type="hidden" name="setting_type" value="<?= $row->type ?>">

    <div class="flex-table row" role="rowgroup">
        <div class="flex-row-1 flex-cell first" role="cell"><?= $row->id ?></div>
        <div class="flex-row-3 flex-cell flex-cell" role="cell"><?= $row->title ?></div>
        <div class="flex-row-4 flex-cell" role="cell">
            <input type="<?= $fieldType ?>" <?= $fieldAtrribute ?> class="form-control"
                name="<?= $row->name ?>" value="<?= $row->value ?>"> 
        </div>
        <div class="flex-row-2 flex-cell" role="cell">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-edit"></i> Change
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

        $("#row1").validate({
            rules: {
                door_open: {
                    range: [1, 60],
                    required: true,
                    number: true
                }
            }
        });
        // $("#row2").validate({
        //     rules: {
        //         sound_buzzer: "required"
        //     },
        // });
        $("#row3").validate({
            rules: {
                hostname: "required"
            },
            messages: {
                hostname: "Please enter a hostname",
            },
        });
        $("#row4").validate({
            rules: {
                password: "required"
            },
            messages: {
                password: "Please enter a password",
            },
            errorPlacement: function(error, element) {
                error.insertAfter(".input-group");
            },
        });
        // $(id).validate({
        //     rules: {
        //         1: {
        //             range: [1, 60],
        //             required: true,
        //             number: true
        //         },
        //         3: "required",
        //         4: "required"
        //     },
        //     messages: {
        //         3: "Please enter a hostname",
        //         4: "Please enter a password"
        //     },
        //     errorPlacement: function(error, element) {
        //         console.log(element.attr("name"));
        //         if (element.attr("name") == 4) {
        //             console.log("pass error");
        //             error.insertAfter(".input-group");
        //         } else {
        //             error.insertAfter(element);
        //         }
        //     },
        //     submitHandler: function(form) {
        //         // do other things for a valid form
        //         console.log(form);
        //         console.log(event);
        //         //form.submit();
        //     },

        // });

    }
</script>