<?php
/**
 * List with comment, list layout, Html
 *
 * @var $name
 * @var $id
 * @var $value
 * @var $check_ans
 * @var $checkconditionFunction
 * @var $labeltext
 * @var $li_classes
 */
?>
<!-- answer_row -->
<div class="answer-item radio-item <?php if(isset($li_classes)){echo $li_classes;}?> radio">
    <div class='form-group'>
        <input
            type="radio"
            name="<?php echo $name; ?>"
            id="<?php echo $id; ?>"
            value="<?php echo $value; ?>"
            class="radio"
            <?php echo $check_ans; ?>
            onclick="<?php echo $checkconditionFunction; ?>"
            aria-labelledby="label-<?php echo $id; ?>"
        />
        <label for="<?php echo $id; ?>" class="answertext radio-label control-label"></label>

        <!--
             The label text is provided inside a div,
             so final user can add paragraph, div, or whatever he wants in the subquestion text
             This field is related to the input thanks to attribute aria-labelledby
        -->
        <div class="label-text label-clickable" id="label-<?php echo $id; ?>">
                <?php echo $labeltext;?>
        </div>
    </div>
</div>
<!-- end of answer_row -->
