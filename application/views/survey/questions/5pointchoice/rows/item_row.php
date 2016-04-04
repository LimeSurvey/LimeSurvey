<?php
/**
 * 5 point choice Html : item row
 *
 * @var $name
 * @var $value
 * @var $id
 * @var $labelText
 * @var $itemExtraClass
 * @var $checkedState
 * @var $checkconditionFunction
 */
?>

<!-- item_row -->
<div class="col-xs-12 col-sm-2 answer-item radio-item <?php  echo $itemExtraClass; ?>">
    <input
        class="radio"
        type="radio"
        name="<?php echo $name; ?>"
        id="answer<?php echo $id; ?>"
        value="<?php echo $value;?>"
        <?php echo $checkedState; ?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
    />
    <label for="answer<?php echo $id; ?>" class="answertext radio-label">
        <?php echo $labelText; ?>
    </label>
</div>
<!-- end of item_row -->
