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
<li id="javatbd<?php echo $name; ?>" class='row answer-item checkbox-text-item form-group form-inline noanswer-item clearfix' <?php echo $sDisplayStyle ;?>>

    <!-- Checkbox + label -->
    <div class="form-group text-item other-text-item col-sm-6 col-xs-12">
        <label if="label-<?php echo $name; ?>" for="<?php echo $id;?>" class="label-text control-label"><?php echo $labeltext;?></label>

        <input
           class="other-text form-control input-sm  multipleco-other-topic <?php echo $classes; echo $kpclass;?>"
           type="text"
           name="<?php echo $name; ?>"
           id="<?php echo $id;?>"
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
    </div>

    <!-- Comment -->
    <div class="form-group text-item col-sm-6 col-xs-12 comment-container">
        <input
            class='form-control input-sm <?php echo $kpclass; ?>'
            type='text'
            size='40'
            id='<?php echo $inputCommentId;?>'
            name='<?php echo $inputCommentName; ?>'
            value='<?php echo $inputCOmmentValue; ?>'
            onkeyup='<?php echo $checkconditionFunctionComment;?>'
            aria-labelledby='label-<?php echo $name; ?> <?php echo $name; ?>'
        />
    </td>
</tr>
<!-- end of answer_row -->
