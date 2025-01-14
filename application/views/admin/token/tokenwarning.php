<?php
/**
 * @var AdminController $this
 * @var Survey $oSurvey
 */
?>
<div class='side-body'>
    <div class="row welcome survey-action">
        <div class="col-12 content-right">
            <div class="card card-primary border-left-danger">
                <p class="lead text-danger">
                    <strong>
                        <?php eT("Survey participants have not been initialised for this survey."); ?>
                    </strong>
                </p>
                <p>
                    <?php
                        if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($oSurvey->sid, 'tokens','create')) {
                            /** eT("If you initialise a survey participants table for this survey then this survey will only be accessible to users who provide an access code either manually or by URL."); **/
                            eT("If you switch to closed-access mode then this survey will only be accessible to users who provide an access code either manually or by URL."); ?>
                            <br /> <br />
                            <?php eT("You can switch back to open-access mode at any time. Navigate to Settings -> Survey participants and click on the red 'Delete participants table' button in the top bar."); ?>
                        <?php ?><br /><br />
                        <br /><br />

                        <?php echo CHtml::form(array("admin/tokens/sa/index/surveyid/{$oSurvey->sid}"), 'post'); ?>
                            <button
                                type="submit"
                                class="btn btn-outline-secondary btn-lg"
                                name="createtable"
                                value="Y"><?php eT("Switch to closed-access mode"); ?>
                            </button>
                            <a
                                href="<?php echo $this->createUrl("surveyAdministration/view/surveyid/$oSurvey->sid"); ?>"
                                class="btn btn-outline-secondary btn-lg">
                                <?php eT("Continue in open-access mode"); ?>
                            </a>
                    <?php echo CHtml::endForm() ?>


                    <?php
                    }else{
                        eT("You don't have the permission to activate participants.");
                    ?>
                    <input type='submit' value='<?php eT("Back to main menu"); ?>' onclick="window.open('<?php echo $this->createUrl("surveyAdministration/view/surveyid/$oSurvey->sid"); ?>', '_top')" /></div>

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
        <div class="col-12 content-right">
            <div class="card card-primary">
                <h2><?php eT("Restore options"); ?></h2>
                <p class="text-info">
                    <?php eT("Please be aware that tables including encryption should not be restored if they have been created in GititSurvey 4 before version 4.6.1")?>
                </p>
                <p class="lead text-success">
                    <strong>
                        <?php eT("The following old survey participants tables could be restored:"); ?>
                    </strong>
                </p>
                <p>
                    <?php echo CHtml::form(array("admin/tokens/sa/index/surveyid/{$oSurvey->sid}"), 'post'); ?>
                        <select size='4' name='oldtable' required>
                            <?php
                                foreach ($oldlist as $ol) {
                                    echo "<option>" . $ol . "</option>\n";
                                }
                            ?>
                        </select><br /><br />
                        <input type='submit' value='<?php eT("Restore"); ?>' class="btn btn-outline-secondary btn-lg"/>
                        <input type='hidden' name='restoretable' value='Y' />
                        <input type='hidden' name='sid' value='<?php echo $oSurvey->sid; ?>' />
                    <?php echo CHtml::endForm() ?>
                </p>
            </div>
        </div>
<?php endif;?>

</div>
</div>
