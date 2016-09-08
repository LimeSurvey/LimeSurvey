<?php
/**
 * Multiple short texts question, item input text Html
 * @var $tip
 * @var $alert
 * @var $maxlength
 * @var $tiwidth
 * @var $extraclass
 * @var $sDisplayStyle
 * @var $prefix
 * @var $myfname
 * @var $labelText
 * @var $sufix
 * @var $kpclass
 * @var $rows
 * @var $checkconditionFunction
 * @var $dispVal
 * @var $suffix
 */
?>
<!-- answer_row -->

<li id='javatbd<?php echo $myfname; ?>' class="question-item answer-item numeric-item text-item form-group<?php if($alert):?> has-error<?php endif; ?>" <?php echo $sDisplayStyle;?>>
    <!--  color code missing mandatory questions red -->
    <label class='control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?><?php if($alert):?> errormandatory<?php endif; ?>' for="answer<?php echo$myfname;?>">
        <?php echo $labelText; ?>
    </label>

    <div class="input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
        <?php echo $prefix; ?>
        <input
            class=" form-control numeric <?php echo $kpclass;?>"
            type="text"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname; ?>"
            value="<?php echo $dispVal;?>"
            onkeyup="<?php echo $checkconditionFunction; ?>"
            title="<?php eT('Only numbers may be entered in this field.'); ?>"
            <?php echo $maxlength; ?>
            data-number="true"
            />
        <?php echo $suffix;?>
    </div>
</li>
<!-- end of answer_row -->
