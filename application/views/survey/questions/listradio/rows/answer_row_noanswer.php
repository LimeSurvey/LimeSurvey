<?php
/**
 * List radio Html : item 'no answer' row
 *
 * @var $name   $ia[1]
 * @var $check_ans
 * @var $checkconditionFunction
 */
?>

<!-- answer_row_noanswer -->
<div  class="col-xs-12 form-group answer-item radio-item no-anwser-item radio">
    <input
    class="radio"
    type="radio"
    name="<?php echo $name; ?>"
    id="answer<?php echo $name; ?>"
    value=""
    <?php echo $check_ans; ?>
    onclick="if (document.getElementById('answer<?php echo $name;?>othertext') != null) document.getElementById('answer<?php echo $name; ?>othertext').value='';<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
    aria-labelledby="label-answer<?php echo $name; ?>"
    />
    <label for="answer<?php echo $name; ?>" class="answertext control-label label-radio"></label>

    <!--
         The label text is provided inside a div,
         so final user can add paragraph, div, or whatever he wants in the subquestion text
         This field is related to the input thanks to attribute aria-labelledby
    -->
    <div class="label-text label-clickable" id="label-answer<?php echo $name; ?>">
        <?php echo eT('No answer'); ?>
    </div>
</div>
<!-- endof answer_row_noanswer -->
