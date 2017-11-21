<?php
/**
 * Multiple Choice question item Html
 *
 * @var $sRows      : the rows, generated with the views rows/answer_row*.php
 *
 * @var $name
 * @var $anscount
 */
?>
<!-- Multiple Choice -->

<!-- answer -->
<?php /* What is the usage of this input ? */ ?>
<input type="hidden" name="MULTI<?php echo $name; ?>" value="<?php echo $anscount; ?>" />
<div class="<?php echo $coreClass;?> row" role="group" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
<?php
    // rows/answer_row*.php
    echo $sRows;
?>
<!-- end of answer -->
</div>
