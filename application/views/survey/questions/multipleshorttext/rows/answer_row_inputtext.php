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
        <?php echo $prefix; ?>
        <input
            class="form-control <?php echo $kpclass; ?>"
            type="text"
            name="<?php echo $myfname; ?>"
            id="answer<?php echo $myfname; ?>"
            value="<?php echo $dispVal; ?>"
            onkeyup="<?php echo $checkconditionFunction; ?>"
            <?php echo $maxlength; ?>
            />
        <?php echo $suffix; ?>
    </div>
</li>
<!-- end of answer_row_inputtext -->
