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
<li id='javatbd<?php echo $name; ?>' class="form-group answer-item radio-item no-anwser-item">
    <input
    type="radio"
    name="<?php echo $name; ?>"
    id="answer<?php echo $name; ?>"
    value=""
    <?php echo $check_ans; ?>
    onclick="if (document.getElementById('answer<?php echo $name;?>othertext') != null) document.getElementById('answer<?php echo $name; ?>othertext').value='';<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
    />
    <label for="answer<?php echo $name; ?>" class="control-label radio-label">
        <?php echo eT('No answer'); ?>
    </label>
</li>
<!-- endof answer_row_noanswer -->
