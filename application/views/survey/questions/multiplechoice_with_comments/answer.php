<?php
/**
 * Multiple Choice with Comment question item Html
 *
 * @var $sRows      : the rows, generated with the views rows/answer_row.php
 *
 * @var $name
 * @var $value
 */
?>
<!-- Multiple Choice with comment-->

<!-- answer -->
<input type='hidden' name='<?php echo $name; ?>' value='<?php echo $value;?>' />
<table class="multiple-choice-with-comment list-unstyled subquestion-list questions-list checkbox-text-list no-more-tables table table-condensed table-hover">
    <?php
        // rows/answer_row.php
        echo $sRows;
    ?>
</table>
<!-- end of answer -->
