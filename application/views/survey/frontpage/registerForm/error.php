<?php
/**
 * error
 *
 */
?>
<ul class='alert alert-danger list-unstyled' role='alert'>
    <?php foreach($aErrors as $error) : ?>
    <li><?php echo $error; ?></li>
    <?php endforeach; ?>
</ul>
