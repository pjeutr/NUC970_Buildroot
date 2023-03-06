<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-body table-responsive">


<form method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />

  <div class="form-group">
    <label><?php echo L::name; ?>:</label>
    <input type="text" class="form-control" name="door[name]" id="door_name" value="<?php echo h($door->name) ?>" placeholder="<?php echo L::generic_sub." ".L::name; ?>"/>
  </div>

    <div class="form-group">
        <label><?php echo L::timezone; ?>:</label>
        <div class="input-group">
        
            <select class="form-control" name="door[timezone_id]" class="selectpicker" id="rule_timezone_id"
            data-title="<?php echo L::timezone_warning; ?>" data-style="btn-default btn-outline">
                <option value=""></option>
                <?php
                    foreach ($timezones as $tz) {
                        echo option_tag($tz->id, $tz->name, $door->timezone_id), "\n";
                    }
                ?>
            </select>   

            <?php if($door->timezone_id) { ?>

            <button class="btn btn-danger" type="submit" title="<?=  L("door_timezone_button_info"); ?>" name="remove_timezone" value="true">
            <?=  L("door_timezone_button"); ?></button>
            <?php } else {?>
                <!-- dummy, otherwise select looks strange -->
                <button disabled="true"></button>
            <?php } ?>

        </div>
        <small id="codeHelp" class="form-text text-muted"><?php echo L::timezone_remark; ?></small>
    </div>

    <?php echo buttonLink_to(L::button_cancel, 'doors'), "\n" ?>
    <button type="submit" class="btn btn-success">
        <i class="fa fa-save"></i> <?php echo L::button_save; ?>
    </button>
</form>

                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>