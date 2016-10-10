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
<input type="hidden" name="MULTI<?php echo $name; ?>" value="<?php echo $anscount; ?>" />
<div class="<?php echo $coreClass;?> row">
<?php
    // rows/answer_row*.php
    echo $sRows;
?>
<!-- end of answer -->
</div>
