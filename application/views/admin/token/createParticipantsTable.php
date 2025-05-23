<?php
/**
 * 
 */
?>

<div class="side-body">
    <div class="row welcome survey-action">
        <div class="col-12 content-right">
            <?php echo CHtml::form(
                ["/admin/tokens/sa/startfromscratch/surveyId/" . $oSurvey->sid],
                'post',
                ['id' => 'startfromscratch', 'name' => 'startfromscratch']
            );
            ?>
            <h3 class="lead"><?php eT('Create survey participants list'); ?></h3>
            <p>
                <?php eT('We are about to create a participants list. Do you intend to proceed?'); ?>
                <br />
            </p>
            <a
                class="btn btn-block btn-outline-secondary"
                href="<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/{$iSurveyID}"); ?>"
            ><?php eT('Cancel'); ?></a>
            <button
                class="btn btn-primary"
                type="submit"
                name="createtable"
                value="Y"
            ><?php eT('Create') ?></button>
        </div>
    </div>
</div>