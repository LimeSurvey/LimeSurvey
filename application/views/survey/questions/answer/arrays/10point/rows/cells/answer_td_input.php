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
<td class="answer_cell_<?php echo $i;?><?php echo ($i==="") ? ' noanswer-item':''; ?> answer-item radio-item">
    <input
        type="radio"
        name="<?php echo $myfname; ?>"
        id="answer<?php echo $myfname; ?>-<?php echo $i;?>"
        value="<?php echo $value; ?>"
        <?php echo $CHECKED;?>
     />
    <label for="answer<?php echo $myfname;?>-<?php echo $i; ?>"  class="ls-label-xs-visibility">
        <?php echo $labelText;?>
    </label>
</td>
<!-- end of answer_td_input -->
