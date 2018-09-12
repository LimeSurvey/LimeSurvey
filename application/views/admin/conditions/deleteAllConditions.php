<p>
    <?php eT("You are about to delete all conditions on this survey's questions"); ?><?php echo " ($iSurveyID)."; ?>
</p>
<p>
    <?php eT("We recommend that before you proceed, you export the entire survey from the main administration screen."); ?>
</p>
<p>
    <?php eT("Continue?"); ?>
</p>

<button
    class='btn btn-default'
    onclick="window.open('<?php echo $this->createUrl("admin/conditions/sa/index/subaction/resetsurveylogic/surveyid/$iSurveyID")."?ok=Y"; ?>', '_top')"
>
    <?php eT('Yes'); ?>
</button>
<button
    class='btn btn-default'
    onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$iSurveyID"); ?>', '_top')"
>
    <?php eT('Cancel'); ?>
</button>
