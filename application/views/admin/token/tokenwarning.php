<?php
/**
 * @var AdminController $this
 * @var Survey $oSurvey
 */
?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row welcome survey-action">
        <div class="col-sm-12 content-right">
            <div class="jumbotron message-box message-box-error">
                <p class="lead text-warning">
                    <strong>
                        <?php eT("Survey participants have not been initialised for this survey."); ?>
                    </strong>
                </p>
                <p>

                    <?php
                        if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($oSurvey->sid, 'tokens','create')){
                            eT("If you initialise a survey participants table for this survey then this survey will only be accessible to users who provide a token either manually or by URL.");
                        ?><br /><br />

                        <?php
                            if ($oSurvey->isAnonymized) {
                                eT("Note: If you turn on the -Anonymized responses- option for this survey then LimeSurvey will mark participants who complete the survey only with a 'Y' instead of date/time to ensure the anonymity of your participants.");
                            ?><br /><br />
                            <?php
                            }
                            eT("Do you want to create a survey participant table for this survey?");
                        ?>
                        <br /><br />

                        <?php echo CHtml::form(array("admin/tokens/sa/index/surveyid/{$oSurvey->sid}"), 'post'); ?>
                            <button type="submit" class="btn btn-default  btn-lg"  name="createtable" value="Y"><?php eT("Initialise participant table"); ?></button>
                            <a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$oSurvey->sid"); ?>" class="btn btn-default  btn-lg"><?php eT("No, thanks."); ?></a>
                    <?php echo CHtml::endForm() ?>


                    <?php
                    }else{
                        eT("You don't have the permission to activate tokens.");
                    ?>
                    <input type='submit' value='<?php eT("Back to main menu"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$oSurvey->sid"); ?>', '_top')" /></div>

                    <?php
                    }
                    ?>
                </p>
            </div>
        </div>

<?php
// Do not offer old postgres survey participants tables for restore since these are having an issue with missing index
if ($tcount > 0 && (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($oSurvey->sid, 'tokens','create'))):
?>
        <div class="col-sm-12 content-right">
            <div class="jumbotron message-box">
                <h2><?php eT("Restore options"); ?></h2>
                <p class="lead text-success">
                    <strong>
                        <?php eT("The following old survey participants tables could be restored:"); ?>
                    </strong>
                </p>
                <p>
                    <?php echo CHtml::form(array("admin/tokens/sa/index/surveyid/{$oSurvey->sid}"), 'post'); ?>
                        <select size='4' name='oldtable'>
                            <?php
                                foreach ($oldlist as $ol) {
                                    echo "<option>" . $ol . "</option>\n";
                                }
                            ?>
                        </select><br /><br />
                        <input type='submit' value='<?php eT("Restore"); ?>' class="btn btn-default btn-lg"/>
                        <input type='hidden' name='restoretable' value='Y' />
                        <input type='hidden' name='sid' value='<?php echo $oSurvey->sid; ?>' />
                    <?php echo CHtml::endForm() ?>
                </p>
            </div>
        </div>
<?php endif;?>

</div>
</div>
