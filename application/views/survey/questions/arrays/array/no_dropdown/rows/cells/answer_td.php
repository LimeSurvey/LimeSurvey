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
<td class="answer_cell_<?php echo $ld;?> answer-item radio-item text-center radio">
    <input
        type="radio"
        name="<?php echo $myfname;?>"
        value="<?php echo $ld; ?>"
        id="answer<?php echo $myfname;?>-<?php echo $ld; ?>"
        <?php echo $CHECKED; ?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
    />
    <label for="answer<?php echo $myfname;?>-<?php echo $ld; ?>"  class="text-hide-md text-hide-lg">
        <?php echo $label;?>
    </label>
</td>
<!-- end of answer_td -->
