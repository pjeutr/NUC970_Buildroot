
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body table-responsive">
<form id="userForm" method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />
  <div class="form-group">
    <label><?=  L("name"); ?>:</label>
    <input type="text" class="form-control" name="user[name]" id="name" value="<?php echo h($user->name) ?>" placeholder="<?=  L("generic_sub")." ".L("name");?>"/>
  </div>
  <div class="form-group">
    <label><?=  L("key"); ?>:</label>
    <div class="input-group">
      <input type="text" class="form-control" name="user[keycode]" id="user_keycode" value="<?php echo h($user->keycode) ?>" placeholder="<?=  L("key_sub"); ?>"/>
      <div class="input-group-append">
        <button class="btn btn-success" type="button" title="Scan a key and press this button" id="scan_key">
        <?=  L("key_button"); ?></button>
      </div>
    </div>
    <small id="codeHelp" class="form-text text-muted"><?=  L("key_remark"); ?></small>
  </div>
  <div class="form-group">
    <label><?=  L("group"); ?>:</label>
      <select name="user[group_id]" class="form-control" id="user_group_id">
        <option id="0"></option>
        <?php
            foreach ($groups as $row) {
                echo option_tag($row->id, $row->name, $user->group_id), "\n";
            }
        ?>
      </select>    
  </div>
  <div class="form-group">
    <label><?=  L("startdate"); ?>:</label>
    <input type="text" class="form-control datepicker" name="user[start_date]" id="datetimepicker" value="<?php echo h($user->start_date) ?>" placeholder="<?=  L("generic_sub")." ".L("startdate");?>"/>
    <small id="emailHelp" class="form-text text-muted"><?=  L("startdate_remark"); ?></small>
  </div>
  <div class="form-group">
    <label><?=  L("enddate"); ?>:</label>
    <input type="text" class="form-control datepicker" name="user[end_date]" id="datetimepicker" value="<?php echo h($user->end_date) ?>" placeholder="<?=  L("generic_sub")." ".L("enddate");?>"/>
    <small id="emailHelp" class="form-text text-muted"><?=  L("enddate_remark"); ?></small>
  </div>
  <div class="form-group">
    <label><?=  L("maxvisits"); ?>:</label>
    <input type="text" class="form-control number" name="user[max_visits]" id="datetimepicker" value="<?php echo h($user->max_visits) ?>" placeholder="<?=  L("generic_sub"); ?> <?=  L("maxvisits"); ?>"/>
    <small id="emailHelp" class="form-text text-muted"><?=  L("maxvisits_remark"); ?></small>
  </div>
  <div class="form-group">
    <label><?=  L("remarks"); ?>:</label>
    <!-- TODO https://stackoverflow.com/questions/37629860/automatically-resizing-textarea-in-bootstrap -->
    <textarea type="text" class="form-control" name="user[remarks]" id="user_remarks" placeholder="<?=  L("remarks_sub"); ?>" 
     style="height:100%;" rows="3"><?php echo h($user->remarks) ?></textarea>
  </div>
    <?php echo buttonLink_to(L("button_cancel"), 'users'), "\n" ?>
    <button type="submit" class="btn btn-success">
        <i class="fa fa-save"></i> <?=  L("button_save"); ?>
    </button>
</form>

                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>

