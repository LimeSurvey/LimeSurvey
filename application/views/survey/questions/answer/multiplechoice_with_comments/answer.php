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
<ul class="<?php echo $coreClass; ?> list-unstyled" role="group" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
    <?php
        // rows/answer_row.php
        echo $sRows;
    ?>
</ul>
<!-- end of answer -->
