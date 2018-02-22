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
<li id="javatbd<?php echo $name; ?>" class='row checkbox-text-item form-group other-item clearfix'>

    <!-- Checkbox + label -->
    <div class="col-sm-<?php echo $sLabelWidth; ?> col-xs-12">
        <div class="form-group answer-item text-item other-text-item ls-input-group"><!-- input-group from BS seems OK too -->
            <label for="<?php echo $id;?>" class="label-text control-label ls-input-group-extra" id="label-<?php echo $id;?>" ><?php echo $labeltext;?></label>
            <input
               class="other-text form-control input multipleco-other-topic <?php echo $classes; echo $kpclass;?>"
               type="text"
               name="<?php echo $name; ?>"
               id="<?php echo $id;?>"
               value="<?php  echo $value; ?>"
               data-number=<?php echo $otherNumber;?>
            />
        </div>
    </div>

    <!-- Comment -->
    <div class="col-sm-<?php echo $sInputContainerWidth; ?> col-xs-12 ">
        <div class="form-group answer-item text-item comment-item">
            <input
                class='form-control <?php echo $kpclass; ?>'
                type='text'
                id='<?php echo $inputCommentId;?>'
                name='<?php echo $inputCommentName; ?>'
                value='<?php echo $inputCOmmentValue; ?>'
                aria-labelledby='label-<?php echo $id; ?> <?php echo $id; ?>'
            />
        </div>
    </div>
</li>
<!-- end of answer_row -->
