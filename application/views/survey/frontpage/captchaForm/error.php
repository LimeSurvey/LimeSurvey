<?php
/**
 * error
 * @var string[] $aLoadErrorMsg
 */
?>
<?php
if(!empty($aEnterErrors)){

    Yii::app()->getController()->renderPartial(
        '/survey/system/errorAlert',
        array('aErrors'    => (array)$aEnterErrors)
    );
}
?>
