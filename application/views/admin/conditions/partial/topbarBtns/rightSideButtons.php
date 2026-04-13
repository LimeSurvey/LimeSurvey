<?php
/** @var array $aData */
Yii::app()->getController()->renderPartial(
    '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
    [
        'showBackButton' => $aData['showBackButton'],
        'returnUrl' => $aData['returnUrl']
    ]
);
?>
    </div> <!-- closing the topbar col-md-auto -->
</div> <!-- closing the topbar row -->
<div class="row">
    <div class="col-md-12">
        <?= $aData['questionNavOptions']; ?> <!-- HTML is in views/admin/conditions/includes/navigator.php -->
