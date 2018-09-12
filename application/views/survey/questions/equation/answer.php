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
<input type="hidden" name="<?php echo $name;?>" id="java<?php echo $name;?>" value="<?php echo $sValue; ?>">
<div class='em_equation equation hidden' style='display:none;visibility:hidden'>
    <?php echo $sEquation; ?>
</div>
<!-- end of answer -->
