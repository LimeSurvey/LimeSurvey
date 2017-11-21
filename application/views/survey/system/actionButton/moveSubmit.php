<?php
/**
 * Move next button
 *
 * @var $sMoveValue
 * @var $sClass
 */
?>
<!-- views/survey/system/actionButton/moveSubmit -->
<?php
    echo CHtml::htmlButton(gT("Submit"),array(
        'type'=>'submit',
        'value'=>$value,
        'name'=>'move',
        'class'=>"$class btn btn-lg btn-primary",
        'accesskey'=>'l'
    ));
?>
<!-- end of views/survey/system/actionButton/moveSubmit -->
