<?php
/**
 * @var $i
 * @var $myfname
 * @var $CHECKED
 * @var $checkconditionFunction
 * @var $value
 */
?>

<!-- answer_td_input -->
<td data-title='<?php echo $i;?>' class="answer-cell-1 answer_cell_<?php echo $i;?> answer-item radio-item">
    <label for="answer<?php echo $myfname;?>-<?php echo $i; ?>">
        <input
            class="radio"
            type="radio"
            name="<?php echo $myfname; ?>"
            id="answer<?php echo $myfname; ?>-<?php echo $i;?>"
            value="<?php echo $value; ?>"
            <?php echo $CHECKED;?>
            onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
         />
    </label>
</td>
<!-- end of answer_td_input -->
