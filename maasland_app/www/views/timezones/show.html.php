<div>

  <p>ID: <?php echo $timezone->id ?></p>

  <p>Door Title: <?php echo h($timezone->title) ?></p>

  <p>Publication Year: <?php echo h($timezone->year) ?></p>

</div>

<hr/>
<?php echo link_to('Back to timezones', 'timezones') ?>
