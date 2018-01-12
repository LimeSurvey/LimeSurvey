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

<div class="<?php echo $coreClass;?> row" role="radiogroup" aria-labelledby="ls-question-text-<?php echo $name; ?>">
<?php
    // rows/answer_row.php
    echo $sRows;
?>
</div>
<?php
/* Value for expression manager javascript (use id) ; no need to submit */
echo \CHtml::hiddenField("java{$name}",$value,array(
    'id' => "java{$name}",
    'disabled' => true,
));
?>
<!-- end of answer -->
