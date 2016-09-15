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
<tr class='<?php echo $liclasses;?>'>

    <!-- Checkbox + label -->

    <td class=""  <?php echo $sDisplayStyle ;?>>

        <label for="<?php echo $id;?>" class="answertext control-label"></label>

        <!--
             The label text is provided inside a div,
             so final user can add paragraph, div, or whatever he wants in the subquestion text
             This field is related to the input thanks to attribute aria-labelledby
        -->
        <div class="label-text label-clickable" id="label-<?php echo $id;?>">
            <?php echo $labeltext;?>
        </div>

        <input
           class="text other form-control input-sm  multipleco-other-topic <?php echo $classes; echo $kpclass;?>"

           type="text"
           name="<?php echo $name; ?>"
           id="<?php echo $id;?>"
           title="<?php eT('Other'); ?>"
           size="10"
           onkeyup="<?php echo $checkconditionFunction;?>"
           value="<?php  echo $value; ?>"
        />

        <?php if($javainput):?>
            <input
            type='hidden'
            name='<?php echo $javaname?>'
            id='<?php echo $javaname?>'
            value='<?php echo $javavalue;?>'
            <?php echo $checked;?>
            />
        <?php endif;?>
    </td>

    <!-- Comment -->
    <td <?php echo $sDisplayStyle; ?> class="comment-container">
        <label for='<?php echo $inputCommentId;?>' class="answer-comment hide control-label">
            <?php echo $commentLabelText;?>
        </label>

        <input
            class='form-control text input-sm <?php echo $kpclass; ?>'

            type='text'
            size='40'
            id='<?php echo $inputCommentId;?>'
            name='<?php echo $inputCommentName; ?>'
            value='<?php echo $inputCOmmentValue; ?>'
            onkeyup='<?php echo $checkconditionFunctionComment;?>'
        />
    </td>
</tr>
<!-- end of answer_row -->
