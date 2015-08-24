<?php
/* If there are any attributes to display as extra columns in the jqGrid, iterate through them */

/* Build a different colModel for the userid column based on whether or not the user is editable */
/* This can probably be moved into the controller */
if (Yii::app()->getConfig("userideditable") == 'Y')  //Firstly, if the user has edit rights, make the columns editable
{
    $uid = '{ "name":"owner_uid", "index":"owner_uid", "width":150, "sorttype":"int", "sortable": true, "align":"center", "editable":true, "edittype":"select", "editoptions":{ "value":"';
    $i = 0;
    foreach ($names as $row)
    {
        $name[$i] = $row->uid . ":" . $row->full_name;
        $i++;
    }
    $unames = implode(";", $name) . '"}}';
    $uidNames[] = $uid . $unames;
}
else
{
    $uidNames[] = '{ "name":"owner_uid", "index":"owner_uid", "width":150, "sorttype":"int", "sortable": true, "align":"center", "editable":false}';
}
/* Build the options for additional languages */
$j = 1;
$lang = '{ "name":"language", "index":"language", "sorttype":"string", "sortable": true, "align":"center", "editable":true, "edittype":"select", "editoptions":{ "value":"';
$getlangvalues = getLanguageData(false, Yii::app()->session['adminlang']);
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
$langnames = implode(";", $lname) . '"}}';
$langNames[] = $lang . $langnames;
/* Build the columnNames for the extra attributes */
/* and, build the columnModel */
$autowidth='true';
if (isset($attributes) && count($attributes) > 0)
{
    foreach ($attributes as $row)
    {
        $attnames[] = '"' . $row['attribute_name'] . '"';
        $uidNames[] = '{ "name": "' . $row['attribute_name'] . '", "index":"a' . $row['attribute_id'] . '", "sorttype":"string", "sortable": true, "align":"center"}';
    }
    $columnNames = ',' . implode(",", $attnames) . ''; //Add to the end of the standard list of columnNames
    if(count($attributes) > 5) $autowidth='false';
}
else
{
    $columnNames = "";
}
/* Build the javasript variables to pass to the jqGrid */
?>
<script type="text/javascript">
    /* Search form titles */
    var sLoadText = '<?php eT("Loading...",'js');?>';
    var sAddCaption = "<?php eT("Add participant", 'js') ?>";
    var sAddButtonCaption = "<?php eT("Add", 'js') ?>";
    var sDeleteButtonCaption = "<?php eT("Delete", 'js') ?>";
    var sDeleteDialogCaption = "<?php eT("Delete one or more participants...", 'js') ?>";
    var sCancel = "<?php eT("Cancel", 'js') ?>";
    var sSubmit = "<?php eT("Save", 'js') ?>";
    var fullSearchTitle = "<?php eT("Full search"); ?>";
    var selectTxt="<?php eT("Select...") ?>";
    var emailTxt="<?php eT("Email") ?>";
    var firstnameTxt="<?php eT("First name") ?>";
    var lastnameTxt="<?php eT("Last name") ?>";
    var blacklistedTxt="<?php eT("Blacklisted") ?>";
    var surveysTxt="<?php eT("Survey links") ?>";
    var surveyTxt="<?php eT("Survey name") ?>";
    var languageTxt="<?php eT("Language") ?>";
    var owneridTxt="<?php eT("Owner ID") ?>";
    var ownernameTxt="<?php eT("Owner name") ?>";
    var equalsTxt="<?php eT("Equals") ?>";
    var containsTxt="<?php eT("Contains") ?>";
    var notequalTxt="<?php eT("Not equal") ?>";
    var notcontainsTxt="<?php eT("Does not contain") ?>";
    var greaterthanTxt="<?php eT("Greater than") ?>";
    var lessthanTxt="<?php eT("Less than") ?>";
    var beginswithTxt="<?php eT("Begins with") ?>";
    var andTxt="<?php eT("AND") ?>";
    var orTxt="<?php eT("OR") ?>";
    /* End search form titles */

    /* Colnames and heading for survey links subgrid */
    var linksHeadingTxt="<?php eT("Participant's survey information", 'js')?>";
    var surveyNameColTxt="<?php eT("Survey name", 'js')?>";
    var surveyIdColTxt="<?php eT("Survey ID", 'js') ?>";
    var tokenIdColTxt="<?php eT("Token ID", 'js') ?>";
    var dateAddedColTxt="<?php eT("Date added", 'js') ?>";
    var dateInvitedColTxt="<?php eT("Last invited", 'js') ?>";
    var dateCompletedColTxt="<?php eT("Submitted", 'js') ?>";
    var surveylinkUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getSurveyInfo_json/pid/"); ?>";

    /* Colnames and heading for attributes subgrid */
    var attributesHeadingTxt="<?php eT("Participant's attribute information", 'js') ?>";
    var actionsColTxt="<?php eT("Actions", 'js') ?>";
    var participantIdColTxt="<?php eT("Participant ID", 'js') ?>";
    var attributeTypeColTxt="<?php eT("Attribute type", 'js') ?>";
    var attributeIdColTxt="<?php eT("Attribute ID", 'js') ?>";
    var attributeNameColTxt="<?php eT("Attribute name", 'js') ?>";
    var attributeValueColTxt="<?php eT("Attribute value", 'js') ?>";
    var attributePosValColTxt="<?php eT("Possible attribute values", 'js') ?>";
    var addToSurveyTxt="<?php eT("Add participants to a survey", 'js') ?>";
    var createParticipantTxt="<?php eT("Create new participant", 'js') ?>";
    var deleteParticipantTxt="<?php eT("Delete selected participants", 'js') ?>";
    var refreshListTxt="<?php eT("Refresh list", 'js') ?>";
    var pageViewTxt= "<?php eT("Page {0} of {1}", 'js') ?>";
    var viewRecordTxt= '<?php eT("View {0} - {1} of {2}",'js');?>';
    var participantsTxt= '<?php eT("Participants",'js');?>';
    var emptyRecordsTxt= "<?php eT("No participants to view", 'js') ?>";
    var sEditAttributeValueMsg = '<?php eT("Edit attribute value",'js');?>';

    var resetBtn = "<?php eT("Reset", 'js'); ?>";
    var exportToCSVTitle = "<?php eT("Export (filtered) participants to CSV", 'js'); ?>";
    var sSelectUserAlert = "<?php eT("Please select a user first", 'js'); ?>";
    var noSearchResultsTxt = "<?php eT("Your search returned no results", 'js'); ?>";
    var accessDeniedTxt = "<?php eT("Access denied", 'js'); ?>";
    var closeTxt = "<?php eT("Close", 'js'); ?>";
    var spTitle = "<?php eT("Sharing participants...", 'js'); ?>";
    var spAddBtn = "<?php eT("Share the selected participants", 'js'); ?>";
    var shareParticipantTxt = "<?php eT("Share participants with other users", 'js') ?>";
    var sfNoUser = "<?php eT("No other user in the system", 'js'); ?>";
    var addpartTitle = "<?php eT("Add participant to survey", 'js'); ?>";
    var addAllInViewTxt="<?php eT("Add all %s participants in your current list to a survey.", 'js'); ?>";
    var addSelectedItemsTxt="<?php eT("Add the %s selected participants to a survey.", 'js') ?>";
    var addpartErrorMsg = "<?php eT("No surveys are available. Either you don't have permissions to any surveys or none of your surveys have a token table", 'js'); ?>";
    var mapButton = "<?php eT("Next", 'js') ?>";
    var error = "<?php eT("Error", 'js') ?>";
    var sWarningMsg = "<?php eT("Warning", 'js') ?>";
    var sSelectRowMsg = "<?php eT("Please select at least one participant.", 'js') ?>";
    var addsurvey = "<?php eT("Add participants to survey", 'js') ?>";
    var exportcsv = "<?php eT("Export CSV", 'js') ?>";
    var nooptionselected = "<?php eT("Please choose one option.", 'js') ?>";
    var removecondition = "<?php eT("Remove condition", 'js') ?>";
    var selectSurvey = "<?php eT("You must select a survey from the list", 'js'); ?>";
    var cancelBtn = "<?php eT("Cancel", 'js') ?>";
    var okBtn = "<?php eT("OK", 'js') ?>";
    var deletefrompanelmsg = "<?php eT("Please choose one option.", 'js') ?>";
    var noRowSelected = "<?php eT("You have no row selected", 'js') ?>";
    var deleteMsg = '<br/>'+deletefrompanelmsg+'<br/><br/><?php echo str_replace("\n",'',CHtml::radioButtonList('deleteMode','', array('po'=>gT("Delete participant(s) from central participants panel only"),'ptt'=>gT("Delete participant(s) from central panel and tokens tables"),'ptta'=>gT("Delete participant(s) from central panel, tokens tables and all associated responses"))));?>';
    var searchBtn = "<?php eT("Search", 'js') ?>";
    var shareMsg = "<?php eT("You can see and edit settings for shared participants in share panel.", 'js') ?>"; //PLEASE REVIEW
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/".$urlsearch); ?>";
    var jsonSearchUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getParticipantsResults_json"); ?>";
    var editUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/editParticipant"); ?>";
    var autowidth = "<?php echo $autowidth ?>";
    var getSearchIDs = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getSearchIDs"); ?>";
    var getaddtosurveymsg = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getaddtosurveymsg"); ?>";
    var minusbutton = "<?php echo Yii::app()->getConfig('adminimageurl') . "deleteanswer.png" ?>";
    var imageurl = "<?php echo Yii::app()->getConfig('adminimageurl') ?>";
    var addbutton = "<?php echo Yii::app()->getConfig('adminimageurl') . "plus.png" ?>";
    var minusbuttonTxt = "<?php eT("Remove search condition", 'js') ?>";
    var addbuttonTxt = "<?php eT("Add search condition", 'js') ?>";
    var delparticipantUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/delParticipant"); ?>";
    var getAttribute_json = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getAttribute_json/pid/"); ?>";
    var exporttocsvcount = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/exporttocsvcount"); ?>";
    var getcpdbAttributes_json = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/exporttocsvcount"); ?>";
    var attMapUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/attributeMap"); ?>";
    var editAttributevalue = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/editAttributevalue"); ?>";
    var shareUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/shareParticipants"); ?>";
    var postUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/setSession"); ?>";
    var ajaxUrl = "<?php echo Yii::app()->getConfig('adminimageurl') . "/ajax-loader.gif" ?>";
    var redUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/displayParticipants"); ?>";
    var searchconditions = "<?php echo $sSearchCondition; ?>";
    var bEditPermission = <?php echo (Permission::model()->hasGlobalPermission('participantpanel','update'))? 'true' : 'false'; ?>;
    var bDeletePermission = <?php echo (Permission::model()->hasGlobalPermission('participantpanel','delete'))? 'true' : 'false'; ?>;
    var colNames = '["participant_id","can_edit","<?php eT("First name") ?>","<?php eT("Last name") ?>","<?php eT("Email") ?>","<?php eT("Blacklisted") ?>","<?php eT("Surveys") ?>","<?php eT("Language") ?>","<?php eT("Owner name") ?>"<?php echo $columnNames; ?>]';
    var colModels = '[{ "name":"participant_id", "index":"participant_id", "width":100, "align":"center", "sorttype":"int", "sortable": true, "editable":false, "hidden":true},';
    colModels += '{ "name":"can_edit", "index":"can_edit", "width":10, "align":"center", "sorttype":"int", "sortable": true, "editable":false, "hidden":true},';
    colModels += '{ "name":"firstname", "index":"firstname", "sorttype":"string", "sortable": true, "width":120, "align":"center", "editable":true},';
    colModels += '{ "name":"lastname", "index":"lastname", "sorttype":"string", "sortable": true,"width":120, "align":"center", "editable":true},';
    colModels += '{ "name":"email", "index":"email","align":"center","width":300, "sorttype":"string", "sortable": true, "editable":true},';
    colModels += '{ "name":"blacklisted", "index":"blacklisted","align":"center","width":80,"sorttype":"string", "sortable": true, "editable":true, "edittype":"checkbox", "editoptions":{ "value":"Y:N"}},';
    colModels += '{ "name":"survey", "index":"survey","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":false},';

<?php
$colModels = "colModels += '" . implode(",';\n colModels += '", $langNames) . ",";
$colModels .= implode(",';\n colModels += '", $uidNames) . "]';";
echo $colModels;
?>
</script>
<script src="<?php echo Yii::app()->getConfig('generalscripts') . "admin/participantdisplay.js" ?>" type="text/javascript"></script>
<div id ="search" style="display:none">
    <?php
    $optionsearch = array('' => gT("Select..."),
        'firstname' => gT("First name"),
        'lastname' => gT("Last name"),
        'email' => gT("Email"),
        'blacklisted' => gT("Blacklisted"),
        'surveys' => gT("Survey links"),
        'survey' => gT("Survey name"),
        'language' => gT("Language"),
        'owner_uid' => gT("Owner ID"),
        'owner_name' => gT("Owner name"));
    $optioncontition = array('' =>  gT("Select..."),
        'equal' =>gT("Equals"),
        'contains' =>gT("Contains"),
        'beginswith' =>gT("Begins with"),
        'notequal' => gT("Not equal"),
        'notcontains' => gT("Does not contain"),
        'greaterthan' => gT("Greater than"),
        'lessthan' => gT("Less than"));
    if (isset($allattributes) && count($allattributes) > 0) // Add attribute names to select box
    {
        echo "<script type='text/javascript'> optionstring = '";
        foreach ($allattributes as $key => $value)
        {
            $optionsearch[$value['attribute_id']] = $value['defaultname'];
            echo "<option value=" . $value['attribute_id'] . ">" . $value['defaultname'] . "</option>";
        }
        echo "';</script>";
    }
    ?>
    <table id='searchtable'>
        <tr>
            <td><?php echo CHtml::dropDownList('field_1', 'id="field_1"', $optionsearch); ?></td>
            <td><?php echo CHtml::dropDownList('condition_1', 'id="condition_1"', $optioncontition); ?></td>
            <td><input type="text" id="conditiontext_1" style="margin-left:10px;" /></td>
            <td><img src=<?php echo Yii::app()->getConfig('adminimageurl') . "plus.png" ?>  id="addbutton" style="margin-bottom:4px" alt='<?php eT("Add search condition"); ?>'></td>
        </tr>
    </table>
    <br/>


</div>
<br/>
<table id="displayparticipants"></table> 
<div id="pager"></div>
<div id="fieldnotselected" title="<?php eT("Error") ?>" style="display:none">
    <p>
<?php eT("Please select a field"); ?>
    </p>
</div>
<div id="conditionnotselected" title="<?php eT("Error") ?>" style="display:none">
    <p>
<?php eT("Please select a condition"); ?>
    </p>
</div>
<div id="norowselected" title="<?php eT("Error") ?>" style="display:none">
    <p>
<?php eT("Please select at least one participant"); ?>
    </p>
</div>
<div id="shareform" title="<?php eT("Share") ?>" style="display:none">
  <div class='popupgroup'>
    <p>
<?php eT("User with whom the participants are to be shared"); ?></p>
    <p>
        <?php
        $options[''] = gT("Select...");
        foreach ($names as $row)
        {
            if (!(Yii::app()->session['loginID'] == $row['uid']))
            {
                $options[$row['uid']] = $row['full_name'];
            }
        }
        echo CHtml::dropDownList('shareuser', 'id="shareuser"', $options);
        ?>
    </p>
  </div>
  <div class='popupgroup'>
    <p>
<?php eT("Allow this user to edit these participants"); ?>
    </p>
    <p><?php
$data = array(
    'id' => 'can_edit',
    'value' => 'TRUE',
    'style' => 'margin:10px',
);
echo CHtml::checkBox('can_edit', TRUE, $data);
?><input type="hidden" name="can_edit" id="can_edit" value='TRUE'>
    </p>
  </div>
</div>
<!--<div id="addsurvey" title="addsurvey" style="display:none">-->

<!-- Add To Survey Popup Window -->
<div class="ui-widget ui-helper-hidden" id="client-script-return-msg" style="display:none">
    <?php echo CHtml::form(array("admin/participants/sa/attributeMap"), 'post', array('id'=>'addsurvey','name'=>'addsurvey')); ?>
        <input type="hidden" name="participant_id" id="participant_id" value=""></input>
        <input type="hidden" name="count" id="count" value=""></input>
        <fieldset class='popupgroup'>
            <legend><?php eT("Participants") ?></legend>
            <div id='allinview' style='display: none'><?php eT("Add all participants in your current list to a survey.") ?></div>
            <div id='selecteditems' style='display: none'><?php eT("Add the selected participants to a survey.") ?></div>
            <br />
        </fieldset>
        <fieldset class='popupgroup'>
		  <legend>
            <?php eT("Survey"); ?>
          </legend>
          <p>
            <?php
            if (!empty($tokensurveynames))
            {
                //$option[''] = gT("Select...");
                foreach ($tokensurveynames as $row)
                {
                    $option[$row['surveyls_survey_id']] = $row['surveyls_title'];
                }
                echo CHtml::listBox('survey_id', 'id="survey_id"', $option, array('style'=>'width: 400px; border: 0px; cursor: pointer', 'size'=>10));
            }
            ?>
          </p><br />
        </fieldset>
        <fieldset class='popupgroup'>
          <legend>
            <?php eT("Options") ?>
          </legend>
            <?php
            $data = array(
                'id' => 'redirect',
                'value' => 'TRUE',
                'style' => 'margin:10px',
            );

            echo CHtml::checkBox('redirect', TRUE, $data);
            ?>
            <label for='redirect'><?php eT("Display survey tokens after adding?"); ?></label>
        </fieldset>
    </form>
</div>
<div id="notauthorised" title="notauthorised" style="display:none">
    <p>
<?php eT("You do not have the permission to edit this participant."); ?></p>

</div>



