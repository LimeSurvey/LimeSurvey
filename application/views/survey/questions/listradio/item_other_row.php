<?php
/**
 * List radio Html : item 'other' row
 *
 * @var $ia
 * @var $answer_other
 * @var $sDisplayStyle
 * @var $sDisable
 * @var $myfname
 * @var $nbColLabelXs
 * @var $nbColLabelLg
 * @var $othertext
 * @var $nbColInputLg
 * @var $nbColInputXs
 * @var $checkedState
 * @var $kpclass
 * @var $sValue
 * @var $oth_checkconditionFunction
 * @var $checkconditionFunction
 * @var $sValueHidden
 * @var $wrapper
 */
?>
<div class="col-sm-12">
        <div id='javatbd<?php echo $myfname; ?>' class='form-group answer-item radio-item other-item other' <?php echo $sDisplayStyle; ?> >
            <label for="SOTH<?php echo $ia[1]; ?>" class="answertext control-label label-radio">
                <input
                class="radio"
                type="radio"
                value="-oth-"
                name="<?php echo $ia[1]; ?>"
                id="SOTH<?php echo $ia[1];?>"
                <?php echo $checkedState;?>
                onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
                />
                <span>
                    <?php echo $othertext; ?>
                </span>
            </label>

            <input
            type="text"
            class="text <?php echo $kpclass; ?>"
            id="answer<?php echo $ia[1]; ?>othertext"
            name="<?php echo $ia[1]; ?>other"
            title="<?php eT('Other'); ?>" <?php echo $answer_other;?>
            onkeyup="if($.trim($(this).val())!=''){ $('#SOTH<?php echo $ia[1]; ?>').click(); };  <?php echo $oth_checkconditionFunction; ?>"
            />
        </div> <!-- Form group ; item row -->
</div>
