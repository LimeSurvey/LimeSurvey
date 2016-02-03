<?php
/**
 * 5 point choice Html : item 'no answer' row
 *
 * @var $ia
 * @var $checkedState
 * @var $checkconditionFunction
 */
?>
<!-- 5 point choice no answer -->
<div class="col-xs-2 answer-item radio-item noanswer-item">
    <input
        class="radio"
        type="radio"
        name="<?php echo $ia[1]; ?>"
        id="answer<?php echo $ia[1]; ?>NANS"
        value=""
        <?php echo $checkedState; ?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type);"
    />
    <label for="answer<?php echo $ia[1];?>NANS" class="answertext">
        <?php echo gT('No answer'); ?>
    </label>
</div>
