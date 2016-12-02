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

<li id='javatbd<?php echo $myfname; ?>' class="question-item answer-item numeric-item text-item form-group<?php echo $extraclass;?><?php if($alert):?> ls-error-mandatory has-error<?php endif; ?>" <?php echo $sDisplayStyle;?>>
    <!--  color code missing mandatory questions red -->
    <label class="control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?>" for="answer<?php echo$myfname;?>">
        <?php echo $labelText; ?>
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
                class="form-control numeric <?php echo $kpclass;?>"
                type="text"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname; ?>"
                value="<?php echo $dispVal;?>"
                title="<?php eT('Only numbers may be entered in this field.'); ?>"
                <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?>
                <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
                data-number="1"
                data-integer="<?php echo $integeronly;?>"
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
<!-- end of answer_row -->
