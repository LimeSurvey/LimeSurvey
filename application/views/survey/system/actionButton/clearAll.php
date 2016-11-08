<?php
/**
 * Move next button
 *
 * @var $value
 * @var $class
 */
?>
<!-- views/survey/system/actionButton/moveNext -->
<?php
echo CHtml::openTag("div",array("class"=>"form-inline ls-{$name}-form"));
    echo CHtml::htmlButton(gT("Exit and clear survey"),array(
        'type'=>'submit',
        'id'=>null,
        'value'=>$value,
        'name'=>$name,
        'class'=>"$class btn btn-link",
        'data-confirmedby'=>$confirmedby,
        'title'=>gT("This action need confirmation."),
        'aria-label'=>gT("This action need confirmation with the next checkbox."), /* ? must be reviewed */
    ));
    echo CHtml::openTag("label",array("class"=>"form-group ls-js-hidden"));
        echo CHtml::checkBox($confirmedby,false,array('id'=>null,'value'=>$confirmvalue));
        echo CHtml::tag("span",array('class'=>'control-label'),gT("Are you sure you want to clear all your responses?"));
    echo CHtml::closeTag("label");
echo CHtml::closeTag("div");
?>
<!-- end of views/survey/system/actionButton/moveNext -->
