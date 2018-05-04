<?php
/**
 * List DropDown Option Html
 * @var $value          $ansrow['code'];
 * @var $opt_select
 * @var $answer         flattenText($_prefix.$ansrow['answer'])."
 * @var $classes
 */
?>

<!-- option -->
<option value='<?php echo $value?>' <?php echo $opt_select;?> <?php if(isset($classes)):?> class="<?php echo $classes;?>" <?php endif;?> >
    <?php echo flattenText(LimeExpressionManager::ProcessStepString($answer,array(),3,true)); // Using static and flat ?>
</option>
<!-- end of option -->
