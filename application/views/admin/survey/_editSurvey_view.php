<div class='header ui-widget-header'><?php eT("Edit survey settings");?></div>
<?php
    $data['action'] = $action;
	$yii = Yii::app();
	$controller = $yii->getController();
    $controller->renderPartial('/admin/survey/subview/tab_view',$data);
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

?>
<input type='hidden' id='sid' name='sid' value="<?php echo $esrow['sid'];?>" />
<input type='hidden' name='languageids' id='languageids' value="<?php echo $esrow['additional_languages'];?>" />
<input type='hidden' name='language' value="<?php echo $esrow['language'];?>" />
<?php if (Permission::model()->hasSurveyPermission($surveyid,'surveysettings','update')){?>
    <div class="hidden hide" id="submitsurveybutton">
    <p>
        <?php
            echo CHtml::htmlButton(gT('Save'),array('type'=>'submit','value'=>'update','name'=>'save'));
            echo CHtml::htmlButton(gT('Save and close'),array('type'=>'submit','value'=>$this->createUrl('admin/survey',array('sa'=>'view','surveyid'=>$surveyid)),'name'=>'redirect'));
            if(Permission::model()->hasSurveyPermission($surveyid,'surveylocale','update'))
                echo CHtml::htmlButton(gT('Save & edit survey text elements'),array('type'=>'submit','value'=>$this->createUrl('admin/survey',array('sa'=>'editlocalsettings','surveyid'=>$surveyid)),'name'=>'redirect'));
        ?>
    </p>
    </div>
<?php } ?>

</form>
<?php
    $controller->renderPartial('/admin/survey/subview/tabResourceManagement_view',$data);

?>
</div>

<div data-copy="submitsurveybutton"></div>
<?php $this->renderPartial('survey/subview/addPanelIntegrationParameter_view', array('questions' => $questions)); ?>
