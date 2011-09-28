<div class='header ui-widget-header'><?php $clang->eT("Edit survey settings");?></div>
<?php
$data['clang'] = $clang;
$data['action'] = $action;
$this->load->view('admin/survey/subview/tab_view',$data);
$this->load->view('admin/survey/subview/tabGeneralEditSurvey_view',$data);
$this->load->view('admin/survey/subview/tabPresentation_view',$data);
$this->load->view('admin/survey/subview/tabPublication_view',$data);
$this->load->view('admin/survey/subview/tabNotification_view',$data);
$this->load->view('admin/survey/subview/tabTokens_view',$data);
$this->load->view('admin/survey/subview/tabPanelIntegration_view',$data);
?>
<input type='hidden' id='surveysettingsaction' name='action' value='updatesurveysettings' />
<input type='hidden' name='sid' value="<?php echo $esrow['sid'];?>" />
<input type='hidden' name='languageids' id='languageids' value="<?php echo $esrow['additional_languages'];?>" />
<input type='hidden' name='language' value="<?php echo $esrow['language'];?>" />
</form>
<?php
$this->load->view('admin/survey/subview/tabResourceManagement_view',$data);
?>
</div>

<?php
        $cond = "if (UpdateLanguageIDs(mylangs,'" . $clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js") . "'))";
        if (bHasSurveyPermission($surveyid,'surveysettings','update'))
        {
            echo "<p><button onclick=\"$cond {document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
            echo "<p><button onclick=\"$cond {document.getElementById('surveysettingsaction').value = 'updatesurveysettingsandeditlocalesettings'; document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save & edit survey text elements") . " >></button></p>\n";
        }
?>