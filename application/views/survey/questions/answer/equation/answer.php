<?php
/**
 * Equation Html
 * @var $name
 * @var $sValue
 * @var $sEquation
 */
?>

<!-- Equation -->

<!-- answer -->
<div class='<?php echo $coreClass;?> hidden'>
    <input type="hidden" name="<?php echo $name;?>" id="java<?php echo $name;?>" value="<?php echo $sValue; ?>">
    <div class='<?php echo $insideClass;?>'>
        <?php echo $sEquation; ?>
    </div>
</div>
<!-- end of answer -->
