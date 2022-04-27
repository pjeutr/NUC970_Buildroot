<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-body table-responsive">


<form method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />

  <div class="form-group">
    <label><?=  L("name"); ?>:</label>
    <input type="text" class="form-control" name="group[name]" id="group_name" value="<?php echo h($group->name) ?>" placeholder="Enter a name"/>
  </div>

    <?php echo buttonLink_to(L("button_cancel"), 'groups'), "\n" ?>
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