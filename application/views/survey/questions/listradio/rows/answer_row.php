<?php
/**
 * Multiple Choice Html : item row
 *
 * @var $name
 * @var $code
 * @var $answer
 * @var $checkedState
 * @var $myfname
 */
?>

<!-- answer_row -->
<div id='javatbd<?php echo $myfname; ?>' class='col-xs-12 form-group answer-item radio-item radio' <?php echo $sDisplayStyle; ?> >
    <input
        class="radio"
        type="radio"
        value="<?php echo $code; ?>"
        name="<?php echo $name; ?>"
        id="answer<?php echo $name.$code; ?>"
        <?php echo $checkedState;?>
        onclick="if (document.getElementById('answer<?php echo $name; ?>othertext') != null) document.getElementById('answer<?php echo $name; ?>othertext').value='';checkconditions(this.value, this.name, this.type)"
        aria-labelledby="label-answer<?php echo $name.$code; ?>"
     />
    <label for="answer<?php echo $name.$code; ?>" class="control-label radio-label"></label>

    <!--
         The label text is provided inside a div,
         so final user can add paragraph, div, or whatever he wants in the subquestion text
         This field is related to the input thanks to attribute aria-labelledby
    -->
    <div class="label-text label-clickable" id="label-answer<?php echo $name.$code; ?>">
        <?php echo $answer; ?>
    </div>
</div>
<!-- end of answer_row -->
