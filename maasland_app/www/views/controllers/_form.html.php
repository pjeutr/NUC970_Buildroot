
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body table-responsive">
<form id="controllerForm" method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />

  <?php 
    //disable the change of Master ip 127.0.0.1 Application needs that to work properly 
    $isMaster = (isset($controller->id) && $controller->id == 1);
    if(!$isMaster) { ?> 
  <div class="form-group">
    <label><?php echo L::controller; ?>:</label>
       <div class="input-group">
        <select xclass="form-control custom-select" size="3" id="controller_chooser" name="controller[chooser]" 
        title="Scan for a new contoller and press this button">
        <option value="0"><?php echo L::choose ?></option>
      </select>
      <div class="input-group-append">
        <button class="btn btn-success" type="button" title="Search for available contollers and press this button" id="scan_key">
            <i class="fa fa fa-search"></i><?php echo L::search_controller_button; ?></button>
      </div>
    </div>
    <small id="codeHelp" class="form-text text-muted"><?php echo L::search_controller_remark; ?></small>
  </div>
  <?php } ?>

  <div class="form-group">
    <label><?php echo L::name; ?>:</label>
    <input type="text" class="form-control" name="controller[name]" id="controller_name" value="<?php echo h($controller->name) ?>" placeholder="<?php echo L::generic_sub." ".L::name; ?>"/>
  </div>
  <div class="form-group">
    <label><?php echo L::networkaddress; ?>:</label>
    <input <?= $isMaster ? "disabled" : "" ?> type="text" class="form-control" name="controller[ip]" id="controller_ip" value="<?php echo h($controller->ip) ?>" placeholder="<?php echo L::generic_sub." ".L::networkaddress; ?>"/>
    <small id="codeHelp" class="form-text text-muted"><?php echo L::networkaddress_remark; ?></small>
  </div>
  <div class="form-group">
    <label><?php echo L::remarks; ?>:</label>
    <!-- TODO https://stackoverflow.com/questions/37629860/automatically-resizing-textarea-in-bootstrap -->
    <textarea type="text" class="form-control" name="controller[remarks]" id="controller_remarks" placeholder="<?php echo L::remarks_sub; ?>" 
     style="height:100%;" rows="3"><?php echo h($controller->remarks) ?></textarea>
  </div>
    <?php echo buttonLink_to(L::button_cancel, 'controllers'), "\n" ?>
    <button type="submit" class="btn btn-success">
        <i class="fa fa-save"></i> <?php echo L::button_save; ?>
    </button>
    <?php if(isset($controller->id) && $controller->id != 1) { ?>
    <a rel="tooltip" title="" class="btn btn-danger" href="/?/controllers/<?= $controller->id ?>" onclick="app.areYouSure(this);return false;" data-original-title="Delete"><i class="fa fa-times"></i><?php echo L::button_delete; ?></a>
    <?php } ?>
</form>

                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>

