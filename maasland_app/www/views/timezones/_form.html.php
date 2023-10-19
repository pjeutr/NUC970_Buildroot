<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-body table-responsive">

<form id="timezoneForm" method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />

  <div class="form-group">
    <label>Timezone name:</label>
    <input type="text" class="form-control" name="timezone[name]" id="timezone_name" value="<?php echo h($timezone->name) ?>" placeholder="Enter a name"/>
  </div>
  <div class="form-group">
    <label>Timezone start:</label>
    <input type="text" class="form-control timepicker" name="timezone[start]" id="datetimepicker" value="<?php echo h($timezone->start) ?>" placeholder="Enter a start"/>
  </div>
  <div class="form-group">
    <label>Timezone end:</label>
    <input type="text" class="form-control timepicker" name="timezone[end]" id="datetimepicker" value="<?php echo h($timezone->end) ?>" placeholder="Enter a end"/>
  </div>
  <div class="form-group">
    <label>Timezone days of the week:</label>
    <div id="weekdays">
    </div>
    <input id="weekdays_form" type="hidden" name="timezone[weekdays]" value="<?php echo h($timezone->weekdays) ?>">
  </div>

  <?php echo buttonLink_to('Cancel', 'timezones'), "\n" ?>
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
