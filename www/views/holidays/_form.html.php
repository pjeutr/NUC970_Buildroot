<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card strpied-tabled-with-hover">
                    <div class="card-body table-responsive">

<form id="initForm" method="POST" action="<?php echo $action ?>">
  <input type="hidden" name="_method" id="_method" value="<?php echo $method ?>" />

  <div class="form-group">
    <label><?php echo L::name; ?>:</label>
    <input type="text" class="form-control" name="holiday[name]" id="holiday_name" value="<?php echo h($holiday->name) ?>" placeholder="<?php echo L::generic_sub." ".L::name; ?>"/>
  </div>
  <div class="form-group">
    <label><?=  L("startdate"); ?>:</label>
    <input type="text" class="form-control datetimepicker" name="holiday[start_date]" id="datetimepicker" value="<?php echo h($holiday->start_date) ?>" placeholder="<?=  L("generic_sub")." ".L("startdate");?>"/>
  </div>
  <div class="form-group">
    <label><?=  L("enddate"); ?>:</label>
    <input type="text" class="form-control datetimepicker" name="holiday[end_date]" id="datetimepicker" value="<?php echo h($holiday->end_date) ?>" placeholder="<?=  L("generic_sub")." ".L("enddate");?>"/>
  </div>


  <?php echo buttonLink_to(L::button_cancel, 'holidays'), "\n" ?>
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
    function initMyForm(id) {
        console.log("holidayFormInit");

        $('#weekdays').weekdays({
            //get value of associated input
            selectedIndexes: $('#weekdays_form').val(),
            days: resource.weekdays ,
        });

        $("#holidayForm").validate({
          submitHandler: function(form) {
            var weekdays = jQuery.makeArray( $('#weekdays').selectedIndexes() );; 
            console.log(weekdays);
            $('#weekdays_form').val(weekdays);
            
            form.submit();
          }
         });

    }
</script>
