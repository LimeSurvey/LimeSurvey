<?php
/**
 * Delete participant list view
 */
?>
<div class="side-body">
    <div class="row welcom survey-action">
        <div class="col-12 content-right">
            <div class="card card-primary border-left-danger">
                <?php echo CHtml::form(
                    array("admin/tokens/sa/kill", 'surveyid' => $surveyid),
                    'post',
                    array('id' => 'deletetokentable', 'name' => 'deletetokentable')
                ); ?>
                <h3 class="lead"><?php eT('Delete survey participant list'); ?></h3>
                <p>
                    <?php eT('Deleting the participant list will switch the survey back to open-access mode.'); ?>
                    <br /> <br />
                    <?php eT('Access codes will no longer be required to access this survey.'); ?>
                    <br /> <br />
                    <?php eT('A backup of this table will be made if you proceed. Your site administrator will be able to access this table.'); ?>
                    <br />
                    <?php echo '(' . $backupTableName . ')'; ?>
                    <br /> <br />
                    <?php eT("You can switch back to closed-access mode at any time. Navigate to Settings --> Survey participants and click on the 'Switch to closed-access mode' button."); ?>
                    <br />
                </p>
                <a
                    class="btn btn-outline-secondary"
                    href="<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/{$iSurveyId}"); ?>"
                ><?php eT('Cancel'); ?></a>
                <button
                    class="btn btn-danger"
                    type='submit'
                    name="ok"
                    value="Y"
                >
                    <?php eT('Delete table'); ?>
                </button>
                </form>
            </div>
        </div>
    </div>
</div>
