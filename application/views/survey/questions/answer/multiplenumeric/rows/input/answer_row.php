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
    <label class="control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?><?php echo ($sLabelWidth===0) ? " hidden":""; ?>" for="answer<?php echo$myfname;?>">
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
            <?php
            echo \CHtml::textField($myfname,$dispVal,array(
                'id' => "answer{$myfname}",
                'class' => "form-control numeric {$kpclass}",
                'title' => gT('Only numbers may be entered in this field.'),
                'size' => ($inputsize ? $inputsize : null),
                'maxlength' => ($maxlength ? $maxlength : null),
                'data-number' => 1,
                'data-integer' => $integeronly,
            ));
            ?>
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
