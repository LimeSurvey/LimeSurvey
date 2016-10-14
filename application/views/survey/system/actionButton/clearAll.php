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
echo CHtml::openTag("div",array("class"=>"form-inline"));
    echo CHtml::htmlButton(gT("Exit and clear survey"),array(
        'type'=>'submit',
        'id'=>false,
        'value'=>$value,
        'name'=>$name,
        'class'=>"$class btn btn-default",
        'data-confirmedby'=>$confirmedby,
        'title'=>gT("This action need confirmation.")
    ));
    echo CHtml::openTag("label",array("class"=>"form-group ls-js-hidden checkbox-item"));
        echo CHtml::checkBox($confirmedby,false,array('value'=>$confirmvalue,'class'=>'checkbox-item'));
        echo gT("Are you sure you want to clear all your responses?");
    echo CHtml::closeTag("label");
echo CHtml::closeTag("div");
?>
<!-- end of views/survey/system/actionButton/moveNext -->
