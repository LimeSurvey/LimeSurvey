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
    echo CHtml::htmlButton(gT("Load unfinished survey"),array(
        'type'=>'submit',
        'value'=>$value,
        'name'=>$name,
        'class'=>"$class btn btn-default",
    ));
?>
<!-- end of views/survey/system/actionButton/moveNext -->
