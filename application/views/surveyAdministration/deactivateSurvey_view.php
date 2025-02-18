
<div class='side-body'>
<?php if (isset($step1)) {

    $this->renderPartial('stopSurvey_view', [
        'surveyid' => $surveyid,
        'dbprefix' => $dbprefix,
        'date'     => $date,
        'sNewSurveyTableName' => $sNewSurveyTableName ?? null
    ]);
} elseif (isset($nostep)) { //todo: why no step? at least a message would be nice...?>

<?php } else { ?>

<?php
    $this->renderPartial('afterDeactivateSurvey_view', [
        'surveyid' => $surveyid,
        'toldtable' => $toldtable ?? null,
        'tnewtable' => $tnewtable ?? null,
        'sNewSurveyTableName' => $sNewSurveyTableName ?? null
    ]);

} ?>

    </div>
