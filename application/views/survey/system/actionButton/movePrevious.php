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
    echo CHtml::htmlButton(gT("Previous"),array(
        'type'=>'submit',
        'value'=>$value,
        'name'=>'move',
        'class'=>"$class btn btn-lg btn-outline-secondary",
        'accesskey'=>'p'
    ));
?>
<!-- end of views/survey/system/actionButton/moveSubmit -->
