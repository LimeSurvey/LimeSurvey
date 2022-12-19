<?php
/**
 * $aErrors string[]
 * $class : extraclass (optional ? ) (@see ??: http://twig.sensiolabs.org/doc/templates.html#other-operators)
 *
 */
?>
<?php if(count($aErrors) > 1) : ?>
<ul class='<?php echo $class ?? "" ?> alert alert-danger list-unstyled' role='alert'>
    <?php foreach($aErrors as $key=>$error) : ?>
    <li><?php echo $error; ?></li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<p class='<?php echo isset($class) ? $class." ": "" ?> alert alert-danger' role='alert'>
    <?php echo reset($aErrors); ?>
</p>
<?php endif; ?>
