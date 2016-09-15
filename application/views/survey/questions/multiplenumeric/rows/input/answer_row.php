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

<tr  id='javatbd<?php echo $myfname; ?>' class="question-item answer-item numeric-item  text-item" <?php echo $sDisplayStyle;?>>
    <td class='text-right align-middle'>
        <?php if($alert):?>
            <label class="control-label numeric-label label label-danger errormandatory"  role="alert">
                <?php echo $labelText;?>
            </label> <!-- alert -->
        <?php else:?>
            <label class='control-label numeric-label' for="answer<?php echo $myfname; ?>">
                <?php echo $labelText;?>
            </label>
        <?php endif;?>
    </td>

    <?php if (!empty($prefix)): ?>
        <td class='text-right align-middle'>
            <label class='no-label'>
                <?php echo $prefix; ?>
            </label>
        </td>
    <?php endif; ?>

    <td>
        <div class="col-sm-<?php echo $tiwidth;?>">
            <input
                class="text form-control numeric <?php echo $kpclass;?>"
                type="text"
                name="<?php echo $myfname;?>"
                id="answer<?php echo $myfname; ?>"
                value="<?php echo $dispVal;?>"
                onkeyup="<?php echo $checkconditionFunction; ?>"
                title="<?php eT('Only numbers may be entered in this field.'); ?>"
                <?php echo $maxlength; ?>
                />
        </div>
        <input type="hidden" name="slider_user_no_action_<?php echo $myfname; ?>" id="slider_user_no_action_<?php echo $myfname; ?>" value="<?php echo $dispVal;?>" />
    </td>

    <?php if (!empty($suffix)): ?>
        <td class='align-middle'>
            <label class='no-label'>
                <?php echo $suffix;?>
            </label>
        </td>
    <?php endif; ?>

</tr>
<!-- end of answer_row -->
