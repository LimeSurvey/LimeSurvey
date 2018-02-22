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
    echo CHtml::htmlButton(gT("Next"),array(
        'type'=>'submit',
        'value'=>$value,
        'name'=>'move',
        'class'=>"$class btn btn-lg btn-primary",
        'accesskey'=>'n'
    ));
?>
<!-- end of views/survey/system/actionButton/moveNext -->
