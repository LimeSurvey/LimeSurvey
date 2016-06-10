<?php
/**
 * Array, no drop down
 * @var $label
 * @var $ld
 * @var $myfname
 * @var $ld
 * @var $CHECKED
 * @var $checkconditionFunction
 */
?>

<!-- answer_td -->
<td class="answer-cell-3 answer_cell_<?php echo $ld;?> answer-item radio-item text-center radio">
    <input
        class="radio"
        type="radio"
        name="<?php echo $myfname;?>"
        value="<?php echo $ld; ?>"
        id="answer<?php echo $myfname;?>-<?php echo $ld; ?>"
        <?php echo $CHECKED; ?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
        aria-labelledby="label-answer<?php echo $myfname;?>-<?php echo $ld; ?>"
    />
    <label for="answer<?php echo $myfname;?>-<?php echo $ld; ?>" ></label>

    <!--
         The label text is provided inside a div,
         so final user can add paragraph, div, or whatever he wants in the subquestion text
         This field is related to the input thanks to attribute aria-labelledby
    -->
    <div class="visible-xs-block label-text" id="label-answer<?php echo $myfname;?>-<?php echo $ld; ?>">
        <?php echo $label;?>
    </div>
</td>
<!-- end of answer_td -->
