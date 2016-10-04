<?php
/**
* Multiple short texts question, item input text Html

* @var $question
* @var $alert
* @var $extraclass
* @var $sDisplayStyle
* @var $myfname
* @var $tiwidth
* @var $prefix
* @var $suffix
* @var $kpclass
* @var $maxlength
* @var $dispVal
* @var $checkconditionFunction
*/
?>

<!--answer_row_inputtext -->
<li id="javatbd<?php echo $myfname; ?>" class="question-item answer-item text-item form-group<?php if($alert):?> has-error<?php endif; ?><?php echo $extraclass;?>" <?php echo $sDisplayStyle;?> >
    <!--  color code missing mandatory questions red -->
    <label class='control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?><?php if($alert):?> errormandatory<?php endif; ?>' for="answer<?php echo$myfname;?>">
        <?php echo $question; ?>
    </label>

    <div class="input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
        <?php if ($prefix != ''): ?>
            <div class="ls-input-group-extra prefix-text prefix text-right">
                <?php echo $prefix; ?>
            </div>
        <?php endif; ?>
        <input
            class="form-control <?php echo $kpclass; ?>"
            type="text"
            name="<?php echo $myfname; ?>"
            id="answer<?php echo $myfname; ?>"
            value="<?php echo $dispVal; ?>"
            <?php echo $maxlength; ?>
            <?php if($numbersonly): echo "data-number='{$numbersonly}'"; endif; ?>
            />
        <?php if ($suffix != ''): ?>
            <div class="ls-input-group-extra suffix-text suffix text-right">
                <?php echo $suffix; ?>
            </div>
        <?php endif; ?>
        </div>
</li>
<!-- end of answer_row_inputtext -->
