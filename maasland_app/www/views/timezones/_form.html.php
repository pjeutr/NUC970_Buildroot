<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-body table-responsive">

<form id="timezoneForm" method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />

  <div class="form-group">
    <label><?php echo L::name; ?>:</label>
    <input type="text" class="form-control" name="timezone[name]" id="timezone_name" value="<?php echo h($timezone->name) ?>" placeholder="<?php echo L::generic_sub." ".L::name; ?>"/>
  </div>
  <div class="form-group">
    <label><?php echo L::start; ?>:</label>
    <input type="text" class="form-control timepicker" name="timezone[start]" id="datetimepicker" value="<?php echo h($timezone->start) ?>" placeholder="<?php echo L::generic_sub." ".L::start; ?>"/>
  </div>
  <div class="form-group">
    <label><?php echo L::end; ?>:</label>
    <input type="text" class="form-control timepicker" name="timezone[end]" id="datetimepicker" value="<?php echo h($timezone->end) ?>" placeholder="<?php echo L::generic_sub." ".L::end; ?>"/>
  </div>
  <div class="form-group">
    <label><?php echo L::weekdays2; ?>:</label>
    <div id="weekdays">
    </div>
    <input id="weekdays_form" type="hidden" name="timezone[weekdays]" value="<?php echo h($timezone->weekdays) ?>">
  </div>

  <?php echo buttonLink_to(L::button_cancel, 'timezones'), "\n" ?>
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
<script type="text/javascript">
    function timezoneFormInit(id) {
        console.log("timezoneFormInit");

        $('#weekdays').weekdays({
            //get value of associated input
            selectedIndexes: $('#weekdays_form').val(),
            //days: [ "Domingo" ,"Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"] ,
        });

        $("#timezoneForm").validate({
          submitHandler: function(form) {
            var weekdays = jQuery.makeArray( $('#weekdays').selectedIndexes() );; 
            console.log(weekdays);
            $('#weekdays_form').val(weekdays);
            
            form.submit();
          }
         });

    }
</script>
