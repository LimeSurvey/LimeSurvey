<?php
/**
 * Multiple choice with comments question, item Html
 * @var $liclasses
 * @var $liid
 * @var $kpclass
 * @var $title
 * @var $sDisplayStyle
 * @var $name                       $myfname
 * @var $id                         answer$myfname
 * @var $value                       Y
 * @var $classes                    ''
 * @var $checked                    ''
 * @var $checkconditionFunction     $checkconditionFunction(this.value, this.name, this.type);
 * @var $checkconditionFunctionComment
 * @var $labeltext                  $ansrow['question']
 * @var $javaname                   java$myfname
 * @var $javavalue                  ''
 * @var $checked                    if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname]))  ...
 * @var $inputCommentId             'answer'.$myfname2
 * @var $commentLabelText           gT('Make a comment on your choice here:'); ?>
 * @var $inputCommentName           $myfname2
 * @var $inputCOmmentValue          if (isset($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2])) {$answer_main .= htmlspecialchars($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$myfname2],ENT_QUOTES);}
 */
?>
<!-- answer_row -->
<li class='<?php echo $liclasses;?>'>
    <!-- Checkbox and label -->
    <div class="option col-xs-12 form-group" <?php echo $sDisplayStyle ;?> >
            <input
                    class="checkbox <?php echo $classes; echo $kpclass; ?>"
                    title="<?php echo $title;?>"
                    type="checkbox"
                    name="<?php echo $name; ?>"
                    id="<?php echo $id;?>"
                    value="<?php echo $value?>"
                    <?php echo $checked;?>
                    onclick="<?php echo $checkconditionFunction;?>"
            />

            <label for="<?php echo $id;?>" class="answertext control-label">
                <?php echo $labeltext;?>
            </label>

            <?php if($javainput):?>
                <input
                type='hidden'
                name='<?php echo $javaname?>'
                id='<?php echo $javaname?>'
                value='<?php echo $javavalue;?>'
                <?php echo $checked;?>
                />
            <?php endif;?>
    </div>

    <!-- Comment -->
    <div class="comment col-xs-12 form-group" <?php echo $sDisplayStyle; ?>>
        <label for='<?php echo $inputCommentId;?>' class="answer-comment hide control-label">
            <?php echo $commentLabelText;?>
        </label>

        <input
            class='form-control text <?php echo $kpclass; ?>'
            type='text'
            size='40'
            id='<?php echo $inputCommentId;?>'
            name='<?php echo $inputCommentName; ?>'
            value='<?php echo $inputCOmmentValue; ?>'
            onkeyup='<?php echo $checkconditionFunctionComment;?>'
        />
    </div>
</li>
<!-- end of answer_row -->
