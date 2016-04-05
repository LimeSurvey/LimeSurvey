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
<div  class="form-group answer-item radio-item no-anwser-item">
    <input
    class="radio"
    type="radio"
    name="<?php echo $name; ?>"
    id="answer<?php echo $name; ?>NANS"
    value=""
    <?php echo $check_ans; ?>
    onclick="if (document.getElementById('answer<?php echo $name;?>othertext') != null) document.getElementById('answer<?php echo $name; ?>othertext').value='';<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
    />
    <label for="answer<?php echo $name; ?>NANS" class="answertext control-label label-radio">
        <?php echo eT('No answer'); ?>
    </label>
</div>
<!-- endof answer_row_noanswer -->
