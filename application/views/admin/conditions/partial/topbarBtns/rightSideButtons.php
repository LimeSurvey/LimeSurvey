<?php
/** @var array $aData */
$this->render(
    '/surveyAdministration/partial/topbar/surveyTopbarRight_view',
    [
        'showBackButton' => $aData['showBackButton'],
        'showSaveButton' => $aData['showSaveButton'],
        'returnUrl' => $aData['returnUrl']
    ]
);
?>
    </div> <!-- closing the topbar col-md-auto -->
</div> <!-- closing the topbar row -->
<div class="row">
    <div class="col-md-12">
        <?= $aData['questionNavOptions']; ?> <!-- HTML is in views/admin/conditions/includes/navigator.php -->
