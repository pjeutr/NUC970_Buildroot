
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body table-responsive">
<form id="userForm" method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />
  <div class="form-group">
    <label>Name:</label>
    <input type="text" class="form-control" name="user[name]" id="name" value="<?php echo h($user->name) ?>" placeholder="Enter a name"/>
  </div>
  <div class="form-group">
    <label>Keycode:</label>
    <div class="input-group">
      <input type="text" class="form-control" name="user[keycode]" id="user_keycode" value="<?php echo h($user->keycode) ?>" placeholder="Enter a code"/>
      <div class="input-group-append">
        <button class="btn btn-success" type="button" title="Scan a key and press this button" id="scan_key">
        Use scanned key</button>
      </div>
    </div>
    <small id="codeHelp" class="form-text text-muted">The code to type on the codetableau or code of a keytag</small>
  </div>
  <div class="form-group">
    <label>Group:</label>
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
    <label>Start date:</label>
    <input type="text" class="form-control datepicker" name="user[start_date]" id="datetimepicker" value="<?php echo h($user->start_date) ?>" placeholder="Enter a end date"/>
    <small id="emailHelp" class="form-text text-muted">Before this date the key/code is invalid (empty is for ever)</small>
  </div>
  <div class="form-group">
    <label>End date:</label>
    <input type="text" class="form-control datepicker" name="user[end_date]" id="datetimepicker" value="<?php echo h($user->end_date) ?>" placeholder="Enter a end date"/>
    <small id="emailHelp" class="form-text text-muted">After this date the key/code is invalid (empty is for ever)</small>
  </div>
  <div class="form-group">
    <label>Maximum visits:</label>
    <input type="text" class="form-control number" name="user[max_visits]" id="datetimepicker" value="<?php echo h($user->max_visits) ?>" placeholder="Enter a maximum visits"/>
    <small id="emailHelp" class="form-text text-muted">After the maximum number of visits  the key/code is invalid (empty is unlimited)</small>
  </div>
  <div class="form-group">
    <label>Remarks:</label>
    <!-- TODO https://stackoverflow.com/questions/37629860/automatically-resizing-textarea-in-bootstrap -->
    <textarea type="text" class="form-control" name="user[remarks]" id="user_remarks" placeholder="Space for some notations" 
     style="height:100%;" rows="3"><?php echo h($user->remarks) ?></textarea>
  </div>
    <?php echo buttonLink_to('Cancel', 'users'), "\n" ?>
    <button type="submit" class="btn btn-success">
        <i class="fa fa-save"></i> Save
    </button>
</form>

                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>

