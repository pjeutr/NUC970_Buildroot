
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body table-responsive">
<form id="controllerForm" method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />

  <div class="form-group">
    <label>Controllers:</label>
       <div class="input-group">
        <select xclass="form-control custom-select" size="3" id="controller_chooser" name="controller[chooser]" 
        title="Scan for a new contoller and press this button">
        <option selected>Choose...</option>
      </select>
      <div class="input-group-append">
        <button class="btn btn-success" type="button" title="Search for available contollers and press this button" id="scan_key">
            Search for controllers</button>
      </div>
    </div>
    <small id="codeHelp" class="form-text text-muted">Search and select a controller to fill the fields below</small>
  </div>

  <div class="form-group">
    <label>Name:</label>
    <input type="text" class="form-control" name="controller[name]" id="controller_name" value="<?php echo h($controller->name) ?>" placeholder="Enter a name"/>
  </div>
  <div class="form-group">
    <label>Network address:</label>
    <input type="text" class="form-control" name="controller[ip]" id="controller_ip" value="<?php echo h($controller->ip) ?>" placeholder="Enter a network address"/>
    <small id="codeHelp" class="form-text text-muted">Press search to find an ip address</small>
  </div>
  <div class="form-group">
    <label>Remarks:</label>
    <!-- TODO https://stackoverflow.com/questions/37629860/automatically-resizing-textarea-in-bootstrap -->
    <textarea type="text" class="form-control" name="controller[remarks]" id="controller_remarks" placeholder="Space for some notations" 
     style="height:100%;" rows="3"><?php echo h($controller->remarks) ?></textarea>
  </div>
    <?php echo buttonLink_to('Cancel', 'controllers'), "\n" ?>
    <button type="submit" class="btn btn-success">
        <i class="fa fa-save"></i> Save
    </button>
    <?php if(isset($controller->id)) { ?>
    <a rel="tooltip" title="" class="btn btn-danger" href="/?/controllers/<?= $controller->id ?>" onclick="app.areYouSure(this);return false;" data-original-title="Delete"><i class="fa fa-times"></i>Delete</a>
    <?php } ?>
</form>

                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>

