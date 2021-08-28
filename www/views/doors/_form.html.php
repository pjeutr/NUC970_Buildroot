<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-body table-responsive">


<form method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />

  <div class="form-group">
    <label>Door name:</label>
    <input type="text" class="form-control" name="door[name]" id="door_name" value="<?php echo h($door->name) ?>" placeholder="Enter a name"/>
  </div>

    <div class="form-group">
        <label>Timezone:</label>
        <select name="door[timezone_id]" class="selectpicker" id="rule_timezone_id"
        data-title="Take care! The door will automatically open at chosen timezone" data-style="btn-default btn-outline">
        <option value=""></option>
        <?php
            foreach ($timezones as $tz) {
                echo option_tag($tz->id, $tz->name, $door->timezone_id), "\n";
            }
        ?>
        </select>    
        <small id="codeHelp" class="form-text text-muted">The door will automatically open at chosen timezone</small>
    </div>

    <?php echo buttonLink_to('Cancel', 'doors'), "\n" ?>
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