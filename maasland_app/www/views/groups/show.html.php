<div>

  <p>ID: <?php echo $group->id ?></p>

  <p>group Title: <?php echo h($group->title) ?></p>

  <p>Publication Year: <?php echo h($group->year) ?></p>

</div>

<hr/>
<?php echo link_to('Back to groups', 'groups') ?>
