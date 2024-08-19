<?php 
set('id', 2);
set('title', L("groups"));
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                    	<?= iconLink_to(L::button_new." ".L::group, 'groups/new', 'btn-outline') ?>
                    </div>
                    <div class="card-body">

                    <?php foreach ($groups as $group) { ?>
                        <div class="row px-3 pt-3">
                            <div class="card-header bg-custom">
                                <?= collapseButton($group->name, 'multiCollapse'.$group->id, 'btn-link text-success', null) ?>
                                &nbsp;
                                <div class="float-right">
                                    <?= iconLink_to(L::button_edit, 'groups/'.$group->id.'/edit', 'btn-link text-success', null) ?>
                                    &nbsp;
                                    <?= deleteLink_to(L::button_delete, 'groups', $group->id) ?>   
                                </div>
                            </div>   

                            <div class="card-body collapse multi-collapse
                            <?= (isset($group_focus) && $group_focus == $group->id) ? "show" : "" ?>
                            " id="multiCollapse<?= $group->id ?>">
                            <?php 
                            $counter = 0;
                            foreach ($rules as $rule) { 
                                //only show rules for current group, this means the result must be sorted in the right order!
                                if($group->id == $rule->group_id) { 
                                $counter++;  ?>
                                <form class="validateForm" id="row<?= $rule->id ?>" action="<?= url_for('grules', $rule->id) ?>" method="POST">
                                    <input type="hidden" name="_method" id="_method" value="PUT">
                                    <input type="hidden" name="rule[group_id]" value="<?= $group->id ?>">    

                                    <div class="row border border-left-0 border-right-0 border-left-0 border-top-0">

                                        <div class="col-sm-4 form-group">
                                        <label><?=  L("door"); ?>:</label>
                                          <select name="rule[door_id]" class="selectpicker" id="rule_door_id" 
                                          data-title="Choose a door" data-style="btn-default btn-outline">
                                            <?php
                                                foreach ($doors as $door) {
                                                    echo option_tag($door->id, $door->name, $rule->door_id), "\n";
                                                }
                                            ?>
                                          </select>    
                                        </div>
                                        <div class="col-sm-4 form-group">
                                        <label><?=  L("timezone"); ?>:</label>
                                          <select name="rule[timezone_id]" class="selectpicker" id="rule_timezone_id"
                                          data-title="Choose a timezone" data-style="btn-default btn-outline">
                                            <?php
                                                foreach ($timezones as $tz) {
                                                    echo option_tag($tz->id, $tz->name, $rule->timezone_id), "\n";
                                                }
                                            ?>
                                          </select>    
                                        </div>
                                        <div class="col-sm-4 form-group mt-4">
                                        <button type="submit" class="btn btn-link text-success">
                                          <i class="fa fa-edit"></i> <?= L::button_save ?>
                                        </button>
                                        <?= deleteLink_to(L::button_delete, 'grules', $rule->id) ?> 
                                        </div>

                                    </div>
                                </form>
                                <?php }} ?>

                                <?php 
                                //only show 'New Rule' if there less than 2 defined
                                if($counter < count($doors)) { ?>

                                <form class="validateForm" id="row0" action="<?= url_for('grules') ?>" method="POST">
                                    <input type="hidden" name="_method" id="_method" value="POST">
                                    <input type="hidden" name="rule[group_id]" value="<?= $group->id ?>"> 
                                    <div class="row">  
                                        <div class="col-sm-4 form-group">
                                        <label><?php echo L::door; ?>:</label>
                                          <select name="rule[door_id]" class="selectpicker" id="rule_door_id"
                                            data-title="Choose a door" data-style="btn-default btn-outline">
                                            <?php
                                                foreach ($doors as $row2) {
                                                    echo option_tag($row2->id, $row2->name, 0), "\n";
                                                }
                                            ?>
                                          </select>    
                                        </div>
                                        <div class="col-sm-4 form-group">
                                        <label><?php echo L::timezone; ?>:</label>
                                          <select name="rule[timezone_id]" class="selectpicker" id="rule_timezone_id"
                                            data-title="Choose a timezone" data-style="btn-default btn-outline">
                                            <?php
                                                foreach ($timezones as $row2) {
                                                    echo option_tag($row2->id, $row2->name, 0), "\n";
                                                }
                                            ?>
                                          </select>    
                                        </div>
                                        <div class="col-sm-4 form-group mt-4">
                                            <button type="submit" class="btn btn-link text-success">
                                              <i class="fa fa-edit"></i> <?= L::button_save ?>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>

                    
                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>

