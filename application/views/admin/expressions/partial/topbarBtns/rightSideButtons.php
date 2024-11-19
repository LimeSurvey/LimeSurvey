<?php
/** @var array $aData */
Yii::app()->getController()->renderPartial(
    '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
    [
        'showBackButton' => $aData['topBar']['showBackButton'],
        'returnUrl' => $aData['topBar']['returnUrl']
    ]
);
?>