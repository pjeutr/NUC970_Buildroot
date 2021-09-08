<div>

  <p>ID: <?php echo $user->id ?></p>

  <p>user Name: <?php echo h($user->name) ?></p>

  <p>Birthday: <?php echo h($user->last->seen) ?></p>

  <p>Bio:
    <blockquote>
      <?php echo $user->remarks, "\n" ?>
    </blockquote>
  </p>

</div>

<hr/>
<?php echo link_to('Back to users', 'users') ?>
