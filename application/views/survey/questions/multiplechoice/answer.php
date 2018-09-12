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
<div class="row multiple-choice-container subquestion-list questions-list checkbox-list">
        <input type="hidden" name="MULTI<?php echo $name; ?>" value="<?php echo $anscount; ?>" />

        <?php
            // rows/answer_row*.php
            echo $sRows;
        ?>
</div>
<!-- end of answer -->
