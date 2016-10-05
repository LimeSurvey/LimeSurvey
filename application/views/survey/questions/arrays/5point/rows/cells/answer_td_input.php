<?php
/**
 * array 5 point choice
 * @var $i
 * @var $myfname
 * @var $CHECKED
 * @var $checkconditionFunction
 * @var $value
 */
?>

<!-- td_input -->
<td class="answer_cell_<?php echo $i;?> answer-item radio-item">
    <input
        type="radio"
        name="<?php echo $myfname; ?>"
        id="answer<?php echo $myfname; ?>-<?php echo $i;?>"
        value="<?php echo $value; ?>"
        <?php echo $CHECKED;?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
     />
    <label for="answer<?php echo $myfname;?>-<?php echo $i; ?>" class="ls-label-xs-visibility">
        <?php echo $labelText;?>
    </label>
</td>
<!-- end of td_input -->
