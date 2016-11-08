<?php
/**
 * error
 * @var string[] $aLoadErrorMsg
 */
?>
<?php
echo Yii::app()->getController()->renderPartial(
    '/survey/system/errorAlert',
    array('aErrors'    => $aLoadErrorMsg)
    );
?>
