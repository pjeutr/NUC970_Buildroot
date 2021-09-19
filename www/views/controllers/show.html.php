<div>

  <p>ID: <?php echo $controller->id ?></p>

  <p>Controller Name: <?php echo h($controller->name) ?></p>

  <p>Remarks:
    <blockquote>
      <?php echo $controller->remarks, "\n" ?>
    </blockquote>
  </p>

</div>

<hr/>
<?php echo link_to('Back to controllers', 'controllers') ?>
