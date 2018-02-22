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
<li id="javatbd<?php echo $myfname; ?>" class="question-item answer-item text-item form-group<?php if($alert):?> ls-error-mandatory has-error<?php endif; ?><?php echo $extraclass;?>" <?php echo $sDisplayStyle;?> >
    <label class='control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?><?php echo ($sLabelWidth===0) ? " hidden":""; ?>' for="answer<?php echo$myfname;?>">
        <?php echo $question; ?>
    </label>
    <div class="col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
        <?php if ($prefix != '' || $suffix != ''): ?>
            <div class="ls-input-group">
        <?php endif; ?>
            <?php if ($prefix != ''): ?>
                <div class="ls-input-group-extra prefix-text prefix">
                    <?php echo $prefix; ?>
                </div>
            <?php endif; ?>
            <input
                class="form-control <?php echo $kpclass; ?>"
                type="text"
                name="<?php echo $myfname; ?>"
                id="answer<?php echo $myfname; ?>"
                value="<?php echo $dispVal; ?>"
                <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?>
                <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
                <?php echo ($numbersonly)? "data-number='{$numbersonly}'":""; ?>
                />
            <?php if ($suffix != ''): ?>
                <div class="ls-input-group-extra suffix-text suffix">
                    <?php echo $suffix; ?>
                </div>
            <?php endif; ?>
        <?php if ($prefix != '' || $suffix != ''): ?>
            </div>
        <?php endif; ?>
    </div>
</li>
<!-- end of answer_row_inputtext -->
