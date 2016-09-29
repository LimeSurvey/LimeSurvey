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
<li id="javatbd<?php echo $myfname; ?>" class="question-item answer-item text-item form-group row <?php echo $extraclass;?>" <?php echo $sDisplayStyle;?>>
    <?php if ($alert):?>
        <!--  color code missing mandatory questions red -->
        <label class='control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?> label label-danger errormandatory' for="answer<?php echo$myfname;?>">
            <?php echo $question; ?>
        </label>
    <?php else:?>
        <label class='control-label col-xs-12 col-sm-<?php echo $sLabelWidth; ?>' for="answer<?php echo$myfname;?>">
            <?php echo $question; ?>
        </label>
    <?php endif;?>

    <div class="input-group col-xs-12 col-sm-<?php echo $sInputContainerWidth; ?>">
        <?php if($prefix){
            echo CHtml::tag("div",array("class"=>"input-group-addon"),$prefix);
        }?>
        <textarea
            class="form-control <?php echo $kpclass;?>"
            name="<?php echo $myfname;?>"
            id="answer<?php echo $myfname;?>"
            rows="<?php echo $rows;?>"
            <?php if($maxlength): echo "data-{$maxlength}"; endif; ?>
            <?php if($numbersonly): echo "data-number='{$numbersonly}'"; endif; ?>
        ><?php echo $dispVal;?></textarea>
        <?php if($suffix){
            echo CHtml::tag("div",array("class"=>"input-group-addon"),$suffix);
        }?>
    </div>
</li>
<!-- end of answer_row_textarea -->
