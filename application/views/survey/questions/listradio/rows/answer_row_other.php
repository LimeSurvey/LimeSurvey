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
<div id='javatbd<?php echo $myfname; ?>' class='col-xs-12 form-group answer-item radio-item radio' <?php echo $sDisplayStyle; ?> >
    <!-- Checkbox + label -->
    <div class="pull-left othertext-label-checkox-container">
        <input
        class="radio"
        type="radio"
        value="-oth-"
        name="<?php echo $name; ?>"
        id="SOTH<?php echo $name;?>"
        <?php echo $checkedState;?>
        onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
        aria-labelledby="label-SOTH<?php echo $name; ?>"
        />

        <label for="SOTH<?php echo $name; ?>" class="answertext control-label label-radio"></label>

        <!--
             The label text is provided inside a div,
             so final user can add paragraph, div, or whatever he wants in the subquestion text
             This field is related to the input thanks to attribute aria-labelledby
        -->
        <div class="label-text label-clickable" id="label-SOTH<?php echo $name; ?>">
                <?php echo $othertext; ?>&nbsp;
        </div>
    </div>

    <!-- comment -->
    <div class="pull-left ">
        <input
        type="text"
        class="form-control text <?php echo $kpclass; ?> input-sm"
        id="answer<?php echo $name; ?>othertext"
        name="<?php echo $name; ?>other"
        title="<?php eT('Other'); ?>" <?php echo $answer_other;?>
        onkeyup="if($.trim($(this).val())!=''){ $('#SOTH<?php echo $name; ?>').click(); };  <?php echo $oth_checkconditionFunction; ?>"
        />
    </div>
</div>
<!-- end of answer_row_other -->
