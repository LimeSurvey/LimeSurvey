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

<div  id='javatbd<?php echo $myfname; ?>' class="question-item answer-item numeric-item  text-item <?php echo $extraclass;?>" <?php echo $sDisplayStyle;?>>
    <?php if($alert):?>
        <div class="alert alert-danger errormandatory"  role="alert">
            <?php echo $labelText;?>
        </div> <!-- alert -->
    <?php endif;?>
    <div class="form-group row">
        <label class='control-label numeric-label pull-left' for="answer<?php echo $myfname; ?>" style="margin-top: 0.5em;">
            <?php echo $labelText;?>
        </label>

        <span class='pull-left col-sm-offset-1'  style="margin-top: 0.5em;">
            <?php echo $prefix; ?>
        </span>

        <div class='col-sm-1' style="min-width: 7em;">
                <input
                    class="text form-control numeric <?php echo $kpclass;?>"
                    type="text"
                    size="<?php echo $tiwidth;?>"
                    name="<?php echo $myfname;?>"
                    id="answer<?php echo $myfname; ?>"
                    value="<?php echo $dispVal;?>"
                    onkeyup="<?php echo $checkconditionFunction; ?>"
                    title="<?php eT('Only numbers may be entered in this field.'); ?>"
                    <?php echo $maxlength; ?>
                />
        </div>

        <span class='pull-left'  style="margin-top: 0.5em;">
            <?php echo $suffix;?>
        </span>

        <!-- xs-12 -->
        <input type="hidden" name="slider_user_no_action_<?php echo $myfname; ?>" id="slider_user_no_action_<?php echo $myfname; ?>" value="<?php echo $dispVal;?>" />
    </div> <!-- form group -->
</div>
<!-- end of answer_row -->
