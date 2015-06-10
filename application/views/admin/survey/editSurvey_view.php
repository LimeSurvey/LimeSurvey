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
<div id='dlgEditParameter'>
    <div id='dlgForm' class='form30'>
        <ul>
            <li><label for='paramname'><?php eT('Parameter name:'); ?></label><input name='paramname' id='paramname' type='text' size='20' />
            </li>
            <li><label for='targetquestion'><?php eT('Target (sub-)question:'); ?></label><select name='targetquestion' id='targetquestion' size='1'>
                    <option value=''><?php eT('(No target question)'); ?></option>
                    <?php foreach ($questions as $question){?>
                        <option value='<?php echo $question['qid'].'-'.$question['sqid'];?>'><?php echo $question['title'].': '.ellipsize(flattenText($question['question'],true,true),43,.70);
                                if ($question['sqquestion']!='')
                                {
                                    echo ' - '.ellipsize(flattenText($question['sqquestion'],true,true),30,.75);
                                }
                        ?></option> <?php
                    }?>
                </select>
            </li>
        </ul>
    </div>
    <p><button id='btnSaveParams'><?php eT('Save'); ?></button> <button id='btnCancelParams'><?php eT('Cancel'); ?></button> </p>
</div>
