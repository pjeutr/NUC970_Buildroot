<div>

  <p>ID: <?php echo $door->id ?></p>

  <p>Door Title: <?php echo h($door->title) ?></p>

  <p>Publication Year: <?php echo h($door->year) ?></p>

</div>

<hr/>
<?php echo link_to('Back to doors', 'doors') ?>
