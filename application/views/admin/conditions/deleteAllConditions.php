<p>
    <?php eT("You are about to delete all conditions on this survey's questions"); ?><?php echo " ($iSurveyID)."; ?>
</p>
<p>
    <?php eT("We recommend that before you proceed, you export the entire survey from the main administration screen."); ?>
</p>
<p>
    <?php eT("Continue?"); ?>
</p>
<?php echo CHtml::beginForm(array("admin/conditions/sa/index/",'subaction'=>'resetsurveylogic','surveyid'=>$iSurveyID)); ?>
<button
    class='btn btn-danger'
    type = "submit"
    name = "ok"
    value="Y";
>
    <?php eT('Yes'); ?>
</button>
<a
    class='btn btn-default'
    href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$iSurveyID"); ?>"
>
    <?php eT('Cancel'); ?>
</a>
