<?php
/**
 * error
 *
 */
?>
<?php
echo Yii::app()->getController()->renderPartial(
    '/survey/system/errorAlert',
    array('aErrors'    => $aSaveErrors)
    );
?>
