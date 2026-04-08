<?php
/** @var int $surveyid */
/** @var string $toldtable */
/** @var string $tnewtable */
/** @var string $sNewSurveyTableName */
?>

<div class="row ">
    <div class="col-8 content-right">
        <div class="card card-primary border-left-success">
            <div class="card-header ">
                <h2>
                    <?php printf(gT("Your survey (%d) was deactivated."), $surveyid); ?>
                </h2>
            </div>
            <div class="card-body d-flex">
                <ul>
                    <li><?php eT("Responses are no longer available in LimeSurvey."); ?></li>
                    <li><?php printf(gT("The responses table has been renamed to: %s"), "<b>" . $sNewSurveyTableName . "</b>"); ?></li>
                    <?php if (isset($toldtable) && $toldtable) { ?>
                    <li><?php printf(gT("The participant list associated with this survey has been renamed to: %s "), $tnewtable);?></li>
                    <?php } ?>
                    <?php if (isset($sNewTimingsTableName)) { ?>
                        <li><?php printf(gT("The response timings table has been renamed to: %s"), $sNewTimingsTableName);?></li>
                    <?php }?>
                    <li><?php eT("You should note the name(s) of the table(s) in case you need to access this information later."); ?>
                        <br>
                        <?php eT("If you deactivated this survey in error, it is possible to restore this data easily if you do not make any changes to the survey structure."); ?>
                    </li>
                </ul>
            </div>
            <div class="card-footer d-flex">
                    <a href="<?php echo Yii::app()->createUrl('surveyAdministration/view/surveyid/' . $surveyid); ?>"
                       class="btn btn-outline-secondary ">
                        <?php eT('Close'); ?>
                    </a>
            </div>
        </div>
    </div>
</div>
