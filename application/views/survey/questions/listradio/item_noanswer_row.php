<?php
/**
 * List radio Html : item 'no answer' row
 *
 * @var $ia
 * @var $check_ans
 * @var $checkconditionFunction
 */
?>
<div class="col-sm-12">
    <div  class="form-group answer-item radio-item no-anwser-item">
        <label for="answer<?php echo $ia[1]; ?>NANS" class="answertext control-label label-radio">
            <input
            class="radio"
            type="radio"
            name="<?php echo $ia[1]; ?>"
            id="answer<?php echo $ia[1]; ?>NANS"
            value=""
            <?php echo $check_ans; ?>
            onclick="if (document.getElementById('answer<?php echo $ia[1];?>othertext') != null) document.getElementById('answer<?php echo $ia[1]; ?>othertext').value='';<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
            />
            <span>
                <?php echo eT('No answer'); ?>
            </span>
        </label>
    </div>
</div>
