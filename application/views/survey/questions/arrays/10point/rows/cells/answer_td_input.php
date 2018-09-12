<?php
/**
 * Array 5 point choice
 * @var $i
 * @var $myfname
 * @var $CHECKED
 * @var $checkconditionFunction
 * @var $value
 */
?>

<!-- answer_td_input -->
<td class="answer-cell-1 answer_cell_<?php echo $i;?> answer-item radio-item radio text-center">
    <input
        class="radio"
        type="radio"
        name="<?php echo $myfname; ?>"
        id="answer<?php echo $myfname; ?>-<?php echo $i;?>"
        value="<?php echo $value; ?>"
        <?php echo $CHECKED;?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
        aria-labelledby="label-answer<?php echo $myfname;?>-<?php echo $i; ?>"
     />
    <label for="answer<?php echo $myfname;?>-<?php echo $i; ?>"></label>
    <!--
         The label text is provided inside a div,
         so final user can add paragraph, div, or whatever he wants in the subquestion text
         This field is related to the input thanks to attribute aria-labelledby
    -->
    <div class="visible-xs-block label-text" id="label-answer<?php echo $myfname;?>-<?php echo $i; ?>">
        <?php echo $i;?>
    </div>
</td>
<!-- end of answer_td_input -->
