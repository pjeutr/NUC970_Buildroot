<div>

  <p>ID: <?php echo $holiday->id ?></p>

  <p>Holiday Title: <?php echo h($holiday->name) ?></p>

</div>

<hr/>
<?php echo link_to('Back to holidays', 'holidays') ?>
