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
    <input type='hidden' id='sid' name='sid' value="<?php echo $esrow['sid'];?>" />
    <input type='hidden' name='languageids' id='languageids' value="<?php echo $esrow['additional_languages'];?>" />
    <input type='hidden' name='language' value="<?php echo $esrow['language'];?>" />
</form>
<?php
$this->load->view('admin/survey/subview/tabResourceManagement_view',$data);
?>
</div>

<?php
if (bHasSurveyPermission($surveyid,'surveysettings','update'))
{?>
    <p><button onclick="if (UpdateLanguageIDs(mylangs,'<?php $clang->eT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js");?>')) {$('#addnewsurvey').submit();}" class='standardbtn' ><?php $clang->eT("Save"); ?></button></p>
    <p><button onclick="if (UpdateLanguageIDs(mylangs,'<?php $clang->eT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js");?>')) {document.getElementById('surveysettingsaction').value = 'updatesurveysettingsandeditlocalesettings'; $('addnewsurvey').submit();}" class='standardbtn' ><?php $clang->eT("Save & edit survey text elements");?> >></button></p><?php
}?>
<div id='dlgEditParameter'>
    <div id='dlgForm' class='form30'>
        <ul>
            <li><label for='paramname'><?php $clang->eT('Parameter name:'); ?></label><input name='paramname' id='paramname' type='text' size='20' />
            </li>
            <li><label for='targetquestion'><?php $clang->eT('Target (sub-)question:'); ?></label><select name='targetquestion' id='targetquestion' size='1' />
            <option value=''><?php $clang->eT('(No target question)'); ?></option>
            <?php foreach ($questions as $question){?>
               <option value='<?php echo $question['qid'].'-'.$question['sqid'];?>'><?php echo $question['title'].': '.ellipsize(FlattenText($question['question'],true),43,.70);
               if ($question['sqquestion']!='')
               {
                    echo ' - '.ellipsize(FlattenText($question['sqquestion'],true),30,.75);
               }
               ?></option> <?php
            }?>
            </select>
            </li>
        </ul>
    </div>
<p><button id='btnSave'>Save</button> <button id='btnCancel'>Cancel</button>
</div>
