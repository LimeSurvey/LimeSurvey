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
<td class="answer_cell_<?php echo $i;?> answer-item radio-item radio text-center">
    <input
        class="radio"
        type="radio"
        name="<?php echo $myfname; ?>"
        id="answer<?php echo $myfname; ?>-<?php echo $i;?>"
        value="<?php echo $value; ?>"
        <?php echo $CHECKED;?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
     />
    <label for="answer<?php echo $myfname;?>-<?php echo $i; ?>"  class="text-hide-md text-hide-lg">
        <?php echo $labelText;?>
    </label>
</td>
<!-- end of answer_td_input -->
