<?php
    // Build the options for additional languages
    $aLanguageNames=array();
    foreach ($aLanguages as $sCode => $sName)
    {
        $aLanguageNames[] = $sCode . ":" . str_replace(";", " -", $sName);
    }
    $aLanguageNames = implode(";", $aLanguageNames);
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
            $uidNames[] = '{ "name":"' . $sFieldname . '", "index":"' . $sFieldname . '", "sorttype":"string", "sortable": true, "align":"left", "editable":true, "width":100' . $customEdit . '}';
            $aColumnHeaders[]=$aData['description'];
        }
        $columnNames='"'.implode('","',$aColumnHeaders).'"';
    }
    $sJsonColumnInformation=json_encode($aTokenColumns);
    // Build the javasript variables to pass to the jqGrid
?>
<script type="text/javascript">
    var sAddParticipantToCPDBText = '<?php eT("Add participants to central database",'js');?>';
    var sLoadText = '<?php eT("Loading...",'js');?>';
    var sSelectRowMsg = "<?php eT("Please select at least one participant.", 'js') ?>";
    var sWarningMsg = "<?php eT("Warning", 'js') ?>";
    var sRecordText = '<?php eT("View {0} - {1} of {2}",'js');?>';
    var sPageText = '<?php eT("Page {0} of {1}",'js');?>';
    var imageurl = "<?php echo Yii::app()->getConfig('adminimageurl'); ?>";
    var mapButton = "<?php eT("Next") ?>";
    var error = "<?php eT("Error") ?>";
    var removecondition = "<?php eT("Remove condition") ?>";
    var cancelBtn = "<?php eT("Cancel") ?>";
    var exportBtn = "<?php eT("Export") ?>";
    var okBtn = "<?php eT("OK") ?>";
    var resetBtn = "<?php eT("Reset") ?>";
    var noRowSelected = "<?php eT("You have no row selected") ?>";
    var searchBtn = "<?php eT("Search") ?>";
    var shareMsg = "<?php eT("You can see and edit settings for shared participants in share panel.") ?>"; //PLEASE REVIEW
    var jsonSearchUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/getSearch_json/surveyid/{$surveyid}/search"); ?>";
    var getSearchIDs = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getSearchIDs"); ?>";
    var addbutton = "<?php echo Yii::app()->getConfig('adminimageurl')."plus.png" ?>";
    var minusbutton = "<?php echo Yii::app()->getConfig('adminimageurl') . "deleteanswer.png" ?>";
    var survey_id = "<?php echo $surveyid; ?>";
    var delUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/delete/surveyid/{$surveyid}"); ?>";
    var saveBtn = "<?php eT("Save changes") ?>";
    var okBtn = "<?php echo eT("OK") ?>";
    var delmsg = "<?php eT("Are you sure you want to delete the selected entries?") ?>";
    var surveyID = "<?php echo $surveyid; ?>";
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/getTokens_json/surveyid/{$surveyid}"); ?>";
    var postUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/setSession"); ?>";
    var editUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/editToken/surveyid/{$surveyid}"); ?>";
    var sEmptyRecords ='<?php eT("Participant table is empty.",'js');?>';
    var sCaption ='';
    var sDelTitle = '<?php eT("Delete selected participant(s) from this survey",'js');?>';
    var sRefreshTitle ='<?php eT("Reload participant list",'js');?>';
    var noSearchResultsTxt = '<?php eT("No survey participants matching the search criteria",'js');?>';
    var sFind= '<?php eT("Filter",'js');?>';
    var remindurl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/email/action/remind/surveyid/{$surveyid}"); ?>";
    var attMapUrl = "<?php echo $this->createUrl("admin/participants/sa/attributeMapToken/sid/");?>";
    var invitemsg = "<?php echo eT("Send an invitation email to the selected entries (if they have not yet been sent an invitation email)"); ?>"
    var remindmsg = "<?php echo eT("Send a reminder email to the selected entries (if they have already received the invitation email)"); ?>"
    var inviteurl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/email/action/invite/surveyid/{$surveyid}"); ?>";
    var sSummary =  '<?php eT("Summary",'js');?>';
    var showDelButton = <?php echo $showDelButton; ?>;
    var showBounceButton = <?php echo $showBounceButton; ?>;
    var showInviteButton = <?php echo $showInviteButton; ?>;
    var showRemindButton = <?php echo $showRemindButton; ?>;
    var sDelete = "<?php eT('Delete this search criteria'); ?>";
    var sAdd = "<?php eT("Add another search criteria"); ?>";
    <?php if (!Permission::model()->hasGlobalPermission('participantpanel','read')){?>
    var bParticipantPanelPermission=false;
    <?php
    } else {?>
    var bParticipantPanelPermission=true;
    var viewParticipantsLink = "<?php eT("View participants of this survey in the central participant database panel") ?>";
    <?php } ?>
    var sBounceProcessing = "<?php eT("Start bounce processing") ?>";
    var sBounceProcessingURL = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/sa/bounceprocessing/surveyid/{$surveyid}"); ?>";
    var participantlinkUrl="<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/displayParticipants"); ?>";
    var andTxt="<?php eT("AND") ?>";
    var orTxt="<?php eT("OR") ?>";
    var searchtypes = ["<?php eT("Equals") ?>","<?php eT("Contains") ?>","<?php eT("Not equal") ?>","<?php eT("Not contains") ?>","<?php eT("Greater than") ?>","<?php eT("Less than") ?>"];
    var colNames = ["ID","<?php eT("Action") ?>","<?php eT("First name") ?>","<?php eT("Last name") ?>","<?php eT("Email address") ?>","<?php eT("Email status") ?>","<?php eT("Token") ?>","<?php eT("Language") ?>","<?php eT("Invitation sent?") ?>","<?php eT("Reminder sent?") ?>","<?php eT("Reminder count") ?>","<?php eT("Completed?") ?>","<?php eT("Uses left") ?>","<?php eT("Valid from") ?>","<?php eT("Valid until") ?>"<?php if (count($columnNames)) echo ','.$columnNames; ?>];
    var colModels = [
    { "name":"tid", "index":"tid", "width":30, "align":"center", "sorttype":"int", "sortable": true, "editable":false, "hidden":false},
    { "name":"action", "index":"action", "sorttype":"string", "sortable": false, "width":120, "align":"center", "editable":false},
    { "name":"firstname", "index":"firstname", "sorttype":"string", "sortable": true, "width":100, "align":"left", "editable":true},
    { "name":"lastname", "index":"lastname", "sorttype":"string", "sortable": true,"width":100, "align":"left", "editable":true},
    { "name":"email", "index":"email","align":"left","width":170, "sorttype":"string", "sortable": true, "editable":true},
    { "name":"emailstatus", "index":"emailstatus","align":"left","width": 80,"sorttype":"string", "sortable": true, "editable":true},
    { "name":"token", "index":"token","align":"left", "sorttype":"int", "sortable": true,"width":150,"editable":true},
    { "name":"language", "index":"language","align":"left", "sorttype":"int", "sortable": true,"width":100,"editable":true, "formatter":'select', "edittype":"select", "editoptions":{"value":"<?php echo $aLanguageNames; ?>"}},
    { "name":"sent", "index":"sent","align":"left", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"remindersent", "index":"remindersent","align":"left", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"remindercount", "index":"remindercount","align":"right", "sorttype":"int", "sortable": true,"width":80,"editable":true, "classes": "jqgrid-tokens-number-padding"},
    { "name":"completed", "index":"completed","align":"left", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"usesleft", "index":"usesleft","align":"right", "sorttype":"int", "sortable": true,"width":80,"editable":true, "classes": "jqgrid-tokens-number-padding"},
    { "name":"validfrom", "index":"validfrom","align":"left", "sorttype":"int", "sortable": true,"width":160,"editable":true},
    { "name":"validuntil", "index":"validuntil","align":"left", "sorttype":"int", "sortable": true,"width":160,"editable":true}
    <?php if (count($uidNames)) echo ','.implode(",\n", $uidNames); ?>];
    var colInformation=<?php echo $sJsonColumnInformation ?>

    function checkMandatoryAttr(value, colname)  {
        if (value  == '')
            return [false, '<?php eT("Please enter a value for: ") ?>'+colname]; // See http://phpjs.org/functions/sprintf/
        else
            return [true,''];
    }
</script>


<div class="side-body">
	<h3><?php eT("Survey participants",'js'); ?></h3>

    <div class='scrolling-wrapper'
        <div  class="row">
            <div class="col-lg-12" style="margin-top: 1em;">
                <?php
                    // Add some script for gridsearch
                    App()->getClientScript()->registerPackage('jquery-bindWithDelay');
                    App()->getClientScript()->registerPackage('jqgrid.addons');
                ?>
                <table id="displaytokens"></table>
                <div id="pager"></div>

                <div id="search">
                    <?php
                        $aOptionSearch = array('' => gT('Select...','unescaped'));
                        foreach($aTokenColumns as $sTokenColumn => $aTokenInformation)
                        {
                            if($aTokenInformation['search'])
                            {
                                $aOptionSearch[$sTokenColumn]=$aTokenInformation['description'];
                            }
                        }
                        $aOptionCondition = array('' => gT('Select...','unescaped'),
                        'equal' => gT("Equals",'unescaped'),
                        'contains' => gT("Contains",'unescaped'),
                        'notequal' => gT("Not equal",'unescaped'),
                        'notcontains' => gT("Not contains",'unescaped'),
                        'greaterthan' => gT("Greater than",'unescaped'),
                        'lessthan' => gT("Less than",'unescaped'));
                    ?>
                    <table id='searchtable'>
                        <tr>
                            <td><?php echo CHtml::dropDownList('field_1', 'id="field_1"', $aOptionSearch, array('class' => 'form-control')); ?></td>
                            <td><?php echo CHtml::dropDownList('condition_1', 'id="condition_1"', $aOptionCondition, array('class' => 'form-control')); ?></td>
                            <td><input class='form-control' type="text" id="conditiontext_1" /></td>
                            <td>
                                <span data-toggle='tooltip' title='<?php eT("Add another search criteria");?>' class="ui-pg-button addcondition-button icon-add text-success" style="">
                            </td>
                        </tr>
                    </table>
                </div>

                <?php if (Permission::model()->hasGlobalPermission('participantpanel','read')) { ?>
                    <div id="addcpdb" title="addsurvey" style="display:none">
                        <p><?php eT("Please select the attributes that are to be added to the central database"); ?></p>
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

                <div id="fieldnotselected" title="<?php eT("Error") ?>" style="display:none">
                    <p>
                        <?php eT("Please select a field."); ?>
                    </p>
                </div>
                <div id="conditionnotselected" title="<?php eT("Error") ?>" style="display:none">
                    <p>
                        <?php eT("Please select a condition."); ?>
                    </p>
                </div>
                <div id="norowselected" title="<?php eT("Error") ?>" style="display:none">
                    <p>
                        <?php eT("Please select at least one participant."); ?>
                    </p>
                </div>
                <div class="ui-widget ui-helper-hidden" id="client-script-return-msg" style="display:none"></div>
                <div>
                <div id ='dialog-modal'></div>
            </div>
            </div>
        </div>
    </div>
</div>
