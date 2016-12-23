<?php
/**
 * List Radio Html
 *
 * @var $name
 * @var $value
 */
?>

<!-- List Radio -->

<!-- answer -->
<?php echo $sTimer; ?>

<div class="<?php echo $coreClass;?> row" role="radio-group" aria-labelledby="ls-question-text-<?php echo $name; ?>">
<?php
    // rows/answer_row.php
    echo $sRows;
?>
</div>
<input
    type="hidden"
    name="java<?php echo $name; ?>"
    id="java<?php echo $name; ?>"
    value="<?php echo $value;?>"
    disabled
/>
<!-- end of answer -->
