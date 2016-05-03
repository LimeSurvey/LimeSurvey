<?php
    $data['action'] = $action;
	$yii = Yii::app();
	$controller = $yii->getController();
?>

<div id='edit-survey-text-element' class='side-body <?php echo getSideBodyClass(false); ?>'>
	<h3><?php eT("Edit survey settings");?></h3>

            <?php $controller->renderPartial('/admin/survey/subview/tab_view',$data); ?>
            <?php
                if ($action == "editsurveysettings")
                {
                    //
                    $sURL = 'admin/survey/sa/editsurveysettings/surveyid/'. $esrow['sid'];
                    $sURL="admin/database/index/updatesurveysettings";
                }
                else
                {
                    $sURL="admin/survey/sa/insert";
                }
            ?>
            <?php echo CHtml::form(array($sURL), 'post', array('id'=>'addnewsurvey', 'name'=>'addnewsurvey', 'class'=>'form30')); ?>

	<div class="row">
		<div class="col-lg-12 content-right">
		    <div class="tab-content">
			<?php

			    $controller->renderPartial('/admin/survey/subview/tabGeneralEditSurvey_view',$data);
			    if (isset($pluginSettings))
			    {
			        $controller->renderPartial('/admin/survey/subview/tabPluginSettings_view',$data);
			    }
			    $controller->renderPartial('/admin/survey/subview/tabPresentation_view',$data);
			    $controller->renderPartial('/admin/survey/subview/tabPublication_view',$data);
			    $controller->renderPartial('/admin/survey/subview/tabNotification_view',$data);
			    $controller->renderPartial('/admin/survey/subview/tabTokens_view',$data);
			    $controller->renderPartial('/admin/survey/subview/tabPanelIntegration_view',$data);
				$controller->renderPartial('/admin/survey/subview/tabResourceManagement_view',$data);
			?>
			</div>

			<input type='hidden' id='sid' name='sid' value="<?php echo $esrow['sid'];?>" />
			<input type='hidden' name='languageids' id='languageids' value="<?php echo $esrow['additional_languages'];?>" />
			<input type='hidden' name='language' value="<?php echo $esrow['language'];?>" />

			<?php if (Permission::model()->hasSurveyPermission($surveyid,'surveysettings','update')){?>
			    <div class="hidden hide" id="submitsurveybutton">
			    <p><button type="submit" name="action" value='updatesurveysettings'><?php eT("Save"); ?></button></p>
			    <?php if(Permission::model()->hasSurveyPermission($surveyid,'surveylocale','update')) { ?>
			        <p><button type="submit" name="action" value='updatesurveysettingsandeditlocalesettings'><?php eT("Save & edit survey text elements");?></button></p>
			    <?php } ?>
			    </div>
			<?php } ?>

			<div data-copy="submitsurveybutton"></div>
            <?php $this->renderPartial('survey/subview/addPanelIntegrationParameter_view', array('questions' => $questions)); ?>

			</form>

		</div>
	</div>
</div>

<script>
	function ConfirmLanguageChange(confirmtxt)
{
    mylangs=$('#oldlanguages').val();
    if (mylangs)
        {
        if (checklangs(mylangs))
            {
            return true;
        } else
            {
            return confirm(confirmtxt);
        }
    }
}
</script>


