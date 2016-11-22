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
<td class="answer_cell_<?php echo $ld;?><?php echo ($ld==="") ? ' noanswer-item':''; ?> answer-item radio-item">
    <input
        type="radio"
        name="<?php echo $myfname;?>"
        value="<?php echo $ld; ?>"
        id="answer<?php echo $myfname;?>-<?php echo $ld; ?>"
        <?php echo $CHECKED; ?>
    />
    <label for="answer<?php echo $myfname;?>-<?php echo $ld; ?>" class="ls-label-xs-visibility">
        <?php echo $label;?>
    </label>
</td>
<!-- end of answer_td -->
