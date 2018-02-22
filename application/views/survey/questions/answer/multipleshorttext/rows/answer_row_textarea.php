<?php
/**
 * Multiple short texts question, item text area Html
 * @var $extraclass
 * @var $sDisplayStyle
 * @var $prefix
 * @var $myfname
 * @var $labelText                  $ansrow['question']
 * @var $prefix
 * @var $kpclass
 * @var $rows                       $drows.' '.$maxlength
 * @var $checkconditionFunction     $checkconditionFunction.'(this.value, this.name, this.type)'
 * @var $dispVal
 * @var $suffix
 */
?>
<!-- answer_row_textarea -->
<!-- Multiple short texts question, item text area Html -->
<!-- question attribute "display_rows" is set -> we need a textarea to be able to show several rows -->
<li id="javatbd<?php echo $myfname; ?>" class="question-item answer-item text-item form-group <?php if($alert):?> ls-error-mandatory has-error<?php endif; ?><?php echo $extraclass;?>" <?php echo $sDisplayStyle;?>>
        <!--  color code missing mandatory questions red -->
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
            <textarea
                class="form-control <?php echo $kpclass;?>"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname;?>"
                rows="<?php echo $rows;?>"
                <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?>
                <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
                <?php echo ($numbersonly)? "data-number='{$numbersonly}'":""; ?>
            ><?php echo $dispVal;?></textarea>
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
<!-- end of answer_row_textarea -->
