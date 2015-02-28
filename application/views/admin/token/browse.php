<?php
    // Build the options for additional languages
    $j = 1;
    $getlangvalues = getLanguageData(false,Yii::app()->session['adminlang']);
    if (Yii::app()->session['adminlang'] != 'auto')
    {
        $lname[0] = Yii::app()->session['adminlang'] . ":" . $getlangvalues[Yii::app()->session['adminlang']]['description'];
    }
    foreach ($getlangvalues as $keycode => $keydesc)
    {
        if (Yii::app()->session['adminlang'] != $keycode)
        {
            $cleanlangdesc = str_replace(";", " -", $keydesc['description']);
            $lname[$j] = $keycode . ":" . $cleanlangdesc;
            $j++;
        }
    }
    $langnames = implode(";", $lname);
    // Build the column information : columnname=>Description,search(true/false) (type ?)
    // Don't add id : because we don't really need it. This different from columnNames (no action).
    // TODO: Merge columnNames and aTokenColumns : need more option (name,index,search, type, editable ...)
    $aTokenColumns=getTokenFieldsAndNames($surveyid,false);
    $aNotQuickFilter=array('tid','emailstatus','sent','remindersent','remindercount','completed','usesleft','validfrom','validuntil');
    foreach($aTokenColumns as $aTokenColumn => $aTokenInformation)
    {
        if($aTokenColumn=="tid"){
            $aTokenColumns[$aTokenColumn]['editable']=false;
            $aTokenColumns[$aTokenColumn]['search']=false;
            $aTokenColumns[$aTokenColumn]['add']=false;
        }else{
            $aTokenColumns[$aTokenColumn]['editable']=true;
            $aTokenColumns[$aTokenColumn]['search']=true;
            $aTokenColumns[$aTokenColumn]['add']=true;
        }
        if(in_array($aTokenColumn,$aNotQuickFilter)){
            $aTokenColumns[$aTokenColumn]['quickfilter']=false;
        }else{
            $aTokenColumns[$aTokenColumn]['quickfilter']=true;
        }
    }
    // Build the columnNames for the extra attributes 
    // and, build the columnModel
    $attributes = getTokenFieldsAndNames($surveyid,true);
    $uidNames=$columnNames=$aColumnHeader=array();
    if (count($attributes) > 0)
    {
        foreach ($attributes as $sFieldname=>$aData)
        {
            $customEdit = '';
            if($aData['mandatory'] == 'Y'){
                $customEdit = ', editrules:{custom:true, custom_func:checkMandatoryAttr}';
            }
            $uidNames[] = '{ "name":"' . $sFieldname . '", "index":"' . $sFieldname . '", "sorttype":"string", "sortable": true, "align":"left", "editable":true, "width":75' . $customEdit . '}';
            $aColumnHeaders[]=$aData['description'];
        }
        $columnNames='"'.implode('","',$aColumnHeaders).'"';
    }
    $sJsonColumnInformation=json_encode($aTokenColumns);
    // Build the javasript variables to pass to the jqGrid
?>
<script type="text/javascript">
    var sAddParticipantToCPDBText = '<?php $clang->eT("Add participants to central database",'js');?>';
    var sLoadText = '<?php $clang->eT("Loading...",'js');?>';
    var sSelectRowMsg = "<?php $clang->eT("Please select at least one participant.", 'js') ?>";
    var sWarningMsg = "<?php $clang->eT("Warning", 'js') ?>";
    var sRecordText = '<?php $clang->eT("View {0} - {1} of {2}",'js');?>';
    var sPageText = '<?php $clang->eT("Page {0} of {1}",'js');?>';
    var imageurl = "<?php echo Yii::app()->getConfig('adminimageurl'); ?>";
    var mapButton = "<?php $clang->eT("Next") ?>";
    var error = "<?php $clang->eT("Error") ?>";
    var removecondition = "<?php $clang->eT("Remove condition") ?>";
    var cancelBtn = "<?php $clang->eT("Cancel") ?>";
    var exportBtn = "<?php $clang->eT("Export") ?>";
    var okBtn = "<?php $clang->eT("OK") ?>";
    var resetBtn = "<?php $clang->eT("Reset") ?>";
    var noRowSelected = "<?php $clang->eT("You have no row selected") ?>";
    var searchBtn = "<?php $clang->eT("Search") ?>";
    var shareMsg = "<?php $clang->eT("You can see and edit settings for shared participants in share panel.") ?>"; //PLEASE REVIEW
    var jsonSearchUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/getSearch_json/surveyid/{$surveyid}/search"); ?>";
    var getSearchIDs = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getSearchIDs"); ?>";
    var addbutton = "<?php echo Yii::app()->getConfig('adminimageurl')."plus.png" ?>";
    var minusbutton = "<?php echo Yii::app()->getConfig('adminimageurl') . "deleteanswer.png" ?>";
    var survey_id = "<?php echo $surveyid; ?>";
    var delUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/delete/surveyid/{$surveyid}"); ?>";
    var saveBtn = "<?php $clang->eT("Save changes") ?>";
    var okBtn = "<?php echo $clang->eT("OK") ?>";
    var delmsg = "<?php $clang->eT("Are you sure you want to delete the selected entries?") ?>";
    var surveyID = "<?php echo $surveyid; ?>";
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/getTokens_json/surveyid/{$surveyid}"); ?>";
    var postUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/setSession"); ?>";
    var editUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/editToken/surveyid/{$surveyid}"); ?>";
    var sEmptyRecords ='<?php $clang->eT("Participant table is empty.",'js');?>';
    var sCaption ='';
    var sDelTitle = '<?php $clang->eT("Delete selected participant(s) from this survey",'js');?>';
    var sRefreshTitle ='<?php $clang->eT("Reload participant list",'js');?>';
    var noSearchResultsTxt = '<?php $clang->eT("No survey participants matching the search criteria",'js');?>';
    var sFind= '<?php $clang->eT("Filter",'js');?>';
    var remindurl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/email/action/remind/surveyid/{$surveyid}"); ?>";
    var attMapUrl = "<?php echo $this->createUrl("admin/participants/sa/attributeMapToken/sid/");?>";
    var invitemsg = "<?php echo $clang->eT("Send an invitation email to the selected entries (if they have not yet been sent an invitation email)"); ?>"
    var remindmsg = "<?php echo $clang->eT("Send a reminder email to the selected entries (if they have already received the invitation email)"); ?>"
    var inviteurl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/email/action/invite/surveyid/{$surveyid}"); ?>";
    var sSummary =  '<?php $clang->eT("Summary",'js');?>';
    var showDelButton = <?php echo $showDelButton; ?>;
    var showBounceButton = <?php echo $showBounceButton; ?>;
    var showInviteButton = <?php echo $showInviteButton; ?>;
    var showRemindButton = <?php echo $showRemindButton; ?>;
    <?php if (!Permission::model()->hasGlobalPermission('participantpanel','read')){?>
    var bParticipantPanelPermission=false;
    <?php 
    } else {?>
    var bParticipantPanelPermission=true;
    var viewParticipantsLink = "<?php $clang->eT("View participants of this survey in the central participant database panel") ?>";
    <?php } ?>
    var sBounceProcessing = "<?php $clang->eT("Start bounce processing") ?>";
    var sBounceProcessingURL = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/bounceprocessing/surveyid/{$surveyid}"); ?>";
    var participantlinkUrl="<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/displayParticipants"); ?>";
    var andTxt="<?php $clang->eT("AND") ?>";
    var orTxt="<?php $clang->eT("OR") ?>";
    var searchtypes = ["<?php $clang->eT("Equals") ?>","<?php $clang->eT("Contains") ?>","<?php $clang->eT("Not equal") ?>","<?php $clang->eT("Not contains") ?>","<?php $clang->eT("Greater than") ?>","<?php $clang->eT("Less than") ?>"];
    var colNames = ["ID","<?php $clang->eT("Action") ?>","<?php $clang->eT("First name") ?>","<?php $clang->eT("Last name") ?>","<?php $clang->eT("Email address") ?>","<?php $clang->eT("Email status") ?>","<?php $clang->eT("Token") ?>","<?php $clang->eT("Language") ?>","<?php $clang->eT("Invitation sent?") ?>","<?php $clang->eT("Reminder sent?") ?>","<?php $clang->eT("Reminder count") ?>","<?php $clang->eT("Completed?") ?>","<?php $clang->eT("Uses left") ?>","<?php $clang->eT("Valid from") ?>","<?php $clang->eT("Valid until") ?>"<?php if (count($columnNames)) echo ','.$columnNames; ?>];
    var colModels = [
    { "name":"tid", "index":"tid", "width":30, "align":"center", "sorttype":"int", "sortable": true, "editable":false, "hidden":false},
    { "name":"action", "index":"action", "sorttype":"string", "sortable": false, "width":120, "align":"center", "editable":false},
    { "name":"firstname", "index":"firstname", "sorttype":"string", "sortable": true, "width":100, "align":"left", "editable":true},
    { "name":"lastname", "index":"lastname", "sorttype":"string", "sortable": true,"width":100, "align":"left", "editable":true},
    { "name":"email", "index":"email","align":"left","width":170, "sorttype":"string", "sortable": true, "editable":true},
    { "name":"emailstatus", "index":"emailstatus","align":"left","width":80,"sorttype":"string", "sortable": true, "editable":true},
    { "name":"token", "index":"token","align":"left", "sorttype":"int", "sortable": true,"width":150,"editable":true},
    { "name":"language", "index":"language","align":"left", "sorttype":"int", "sortable": true,"width":100,"editable":true, "formatter":'select', "edittype":"select", "editoptions":{"value":"<?php echo $langnames; ?>"}},
    { "name":"sent", "index":"sent","align":"left", "sorttype":"int", "sortable": true,"width":130,"editable":true},
    { "name":"remindersent", "index":"remindersent","align":"left", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"remindercount", "index":"remindercount","align":"right", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"completed", "index":"completed","align":"left", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"usesleft", "index":"usesleft","align":"right", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"validfrom", "index":"validfrom","align":"left", "sorttype":"int", "sortable": true,"width":160,"editable":true},
    { "name":"validuntil", "index":"validuntil","align":"left", "sorttype":"int", "sortable": true,"width":160,"editable":true}
    <?php if (count($uidNames)) echo ','.implode(",\n", $uidNames); ?>];
    var colInformation=<?php echo $sJsonColumnInformation ?>

    function checkMandatoryAttr(value, colname)  {
        if (value  == '') 
            return [false, '<?php $clang->eT("Please enter a value for: ") ?>'+colname];
        else 
            return [true,''];
    }
</script>
<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Survey participants",'js'); ?></strong></div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <img src='<?php echo $sImageURL; ?>databegin.png' alt='<?php $clang->eT("Show start..."); ?>' class="gridcontrol disabled databegin" />
            <img src='<?php echo $sImageURL; ?>databack.png' alt='<?php $clang->eT("Show previous.."); ?>' class="gridcontrol disabled databack" />
            <img src='<?php echo $sImageURL; ?>blank.gif' width='13' height='20' alt='' />
            <img src='<?php echo $sImageURL; ?>dataforward.png' alt='<?php $clang->eT("Show next.."); ?>' class="gridcontrol disabled dataforward" />
            <img src='<?php echo $sImageURL; ?>dataend.png' alt='<?php $clang->eT("Show last.."); ?>' class="gridcontrol disabled dataend" />
            <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />
            <div id='tokensearch' class='form-menubar'><label for='searchstring'><?php $clang->eT("Filter by") ?></label><input type='text' name='searchstring' id='searchstring' class='gridsearch' value="" /></div>
        </div>
    </div>
</div>
<?php
    // Add some script for gridsearch
    App()->getClientScript()->registerPackage('jquery-bindWithDelay');
    App()->getClientScript()->registerPackage('jqgrid.addons');
?>
<table id="displaytokens"></table>
<div id="pager"></div>

<div id ="search" style="display:none">
    <?php
        $aOptionSearch = array('' => $clang->gT('Select...'));
        foreach($aTokenColumns as $sTokenColumn => $aTokenInformation)
        {
            if($aTokenInformation['search'])
            {
                $aOptionSearch[$sTokenColumn]=$aTokenInformation['description'];
            }
        }
        $aOptionCondition = array('' => $clang->gT('Select...'),
        'equal' => $clang->gT("Equals"),
        'contains' => $clang->gT("Contains"),
        'notequal' => $clang->gT("Not equal"),
        'notcontains' => $clang->gT("Not contains"),
        'greaterthan' => $clang->gT("Greater than"),
        'lessthan' => $clang->gT("Less than"));
    ?>
    <table id='searchtable'>
        <tr>
            <td><?php echo CHtml::dropDownList('field_1', 'id="field_1"', $aOptionSearch); ?></td>
            <td><?php echo CHtml::dropDownList('condition_1', 'id="condition_1"', $aOptionCondition); ?></td>
            <td><input type="text" id="conditiontext_1" style="margin-left:10px;" /></td>
            <td><img src=<?php echo Yii::app()->getConfig('adminimageurl')."plus.png" ?> alt='<?php $clang->eT("Add another search criteria");?>' class="addcondition-button" style="margin-bottom:4px"></td>
        </tr>
    </table>
</div>

<?php if (Permission::model()->hasGlobalPermission('participantpanel','read')) { ?>
    <div id="addcpdb" title="addsurvey" style="display:none">
        <p><?php $clang->eT("Please select the attributes that are to be added to the central database"); ?></p>
        <p>
            <select id="attributeid" name="attributeid" multiple="multiple">
                <?php
                    if(!empty($attrfieldnames))
                    {
                        foreach($attrfieldnames as $key=>$value)
                        {
                            echo "<option value='".$key."'>".$value."</option>";
                        }
                    }

                ?>
            </select>
        </p>

    </div>
<?php } ?>
</div>


<div id="fieldnotselected" title="<?php $clang->eT("Error") ?>" style="display:none">
    <p>
        <?php $clang->eT("Please select a field."); ?>
    </p>
</div>
<div id="conditionnotselected" title="<?php $clang->eT("Error") ?>" style="display:none">
    <p>
        <?php $clang->eT("Please select a condition."); ?>
    </p>
</div>
<div id="norowselected" title="<?php $clang->eT("Error") ?>" style="display:none">
    <p>
        <?php $clang->eT("Please select at least one participant."); ?>
    </p>
</div>
<div class="ui-widget ui-helper-hidden" id="client-script-return-msg" style="display:none"></div>
<div>
<div id ='dialog-modal'></div>
