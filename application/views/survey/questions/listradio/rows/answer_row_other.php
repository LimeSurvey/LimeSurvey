<?php
/**
 * List radio Html : item 'other' row
 *
 * @var $name
 * @var $answer_other
 * @var $sDisplayStyle
 * @var $sDisable
 * @var $myfname
 * @var $othertext
 * @var $checkedState
 * @var $kpclass
 * @var $sValue
 * @var $oth_checkconditionFunction
 * @var $checkconditionFunction
 */
?>

<!-- answer_row_other -->
<div id='javatbd<?php echo $myfname; ?>' class='form-group answer-item radio-item other-item other' <?php echo $sDisplayStyle; ?> >
    <label for="SOTH<?php echo $name; ?>" class="answertext control-label label-radio">
        <input
        class="radio"
        type="radio"
        value="-oth-"
        name="<?php echo $name; ?>"
        id="SOTH<?php echo $name;?>"
        <?php echo $checkedState;?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
        />
        <span>
            <?php echo $othertext; ?>
        </span>
    </label>

    <input
    type="text"
    class="form-control text <?php echo $kpclass; ?>"
    id="answer<?php echo $name; ?>othertext"
    name="<?php echo $name; ?>other"
    title="<?php eT('Other'); ?>" <?php echo $answer_other;?>
    onkeyup="if($.trim($(this).val())!=''){ $('#SOTH<?php echo $name; ?>').click(); };  <?php echo $oth_checkconditionFunction; ?>"
    />
</div>
<!-- end of answer_row_other -->
