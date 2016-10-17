<?php
/**
 * $aErrors string[]
 *
 */
?>
<?php if(count($aErrors) > 1) : ?>
<ul class='text-danger list-unstyled' role='alert'>
    <?php foreach($aErrors as $key=>$error) : ?>
    <li><?php echo $error; ?></li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<p class='text-danger' role='alert'>
    <?php echo reset($aErrors); ?>
</p>
<?php endif; ?>
