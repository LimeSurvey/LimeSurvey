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
        $uidNames[] = '{ "name": "' . $row['attribute_name'] . '", "index":"' . $row['attribute_id'] . '", "sorttype":"string", "sortable": true, "align":"center"}';
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
    var fullSearchTitle = "<?php $clang->eT("Full search"); ?>";
    var selectTxt="<?php $clang->eT("Select...") ?>";
    var emailTxt="<?php $clang->eT("Email") ?>";
    var firstnameTxt="<?php $clang->eT("First name") ?>";
    var lastnameTxt="<?php $clang->eT("Last name") ?>";
    var blacklistedTxt="<?php $clang->eT("Blacklisted") ?>";
    var surveysTxt="<?php $clang->eT("Survey links") ?>";
    var surveyTxt="<?php $clang->eT("Survey name") ?>";
    var languageTxt="<?php $clang->eT("Language") ?>";
    var owneridTxt="<?php $clang->eT("Owner ID") ?>";
    var ownernameTxt="<?php $clang->eT("Owner name") ?>";
    var equalsTxt="<?php $clang->eT("Equals") ?>";
    var containsTxt="<?php $clang->eT("Contains") ?>";
    var notequalTxt="<?php $clang->eT("Not equal") ?>";
    var notcontainsTxt="<?php $clang->eT("Does not contain") ?>";
    var greaterthanTxt="<?php $clang->eT("Greater than") ?>";
    var lessthanTxt="<?php $clang->eT("Less than") ?>";
    var beginswithTxt="<?php $clang->et("Begins with") ?>";
    var andTxt="<?php $clang->eT("AND") ?>";
    var orTxt="<?php $clang->eT("OR") ?>";
    /* End search form titles */

    /* Colnames and heading for survey links subgrid */
    var linksHeadingTxt="<?php $clang->eT("Participant's survey information")?>";
    var surveyNameColTxt="<?php $clang->eT("Survey name")?>";
    var surveyIdColTxt="<?php $clang->eT("Survey ID") ?>";
    var tokenIdColTxt="<?php $clang->eT("Token ID") ?>";
    var dateAddedColTxt="<?php $clang->eT("Date added") ?>";
    var dateInvitedColTxt="<?php $clang->eT("Last invited") ?>";
    var dateCompletedColTxt="<?php $clang->eT("Submitted") ?>";
    var surveylinkUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/getSurveyInfo_json/pid/"); ?>";

    /* Colnames and heading for attributes subgrid */
    var attributesHeadingTxt="<?php $clang->eT("Participant's attribute information") ?>";
    var actionsColTxt="<?php $clang->eT("Actions") ?>";
    var participantIdColTxt="<?php $clang->eT("Participant ID") ?>";
    var attributeTypeColTxt="<?php $clang->eT("Attribute type") ?>";
    var attributeNameColTxt="<?php $clang->eT("Attribute name") ?>";
    var attributeValueColTxt="<?php $clang->eT("Attribute value") ?>";
    var attributePosValColTxt="<?php $clang->eT("Possible attribute values") ?>";
    var addToSurveyTxt="<?php $clang->eT("Add participants to a survey") ?>";

    var resetBtn = "<?php $clang->eT("Reset"); ?>";
    var exportToCSVTitle = "<?php $clang->eT("Export to CSV"); ?>";
    var noSearchResultsTxt = "<?php $clang->eT("Your search returned no results"); ?>";
    var accessDeniedTxt = "<?php $clang->eT("Access denied"); ?>";
    var closeTxt = "<?php $clang->eT("Close"); ?>";
    var spTitle = "<?php $clang->eT("Sharing participants..."); ?>";
    var spAddBtn = "<?php $clang->eT("Share the selected participants"); ?>";
    var shareParticipantTxt = "<?php $clang->eT("Share participants with other users") ?>";
    var sfNoUser = "<?php $clang->eT("No other user in the system"); ?>";
    var addpartTitle = "<?php $clang->eT("Add participant to survey"); ?>";
    var addAllInViewTxt="<?php $clang->eT("Add all %s participants in your current list to a survey."); ?>";
    var addSelectedItemsTxt="<?php $clang->eT("Add the %s selected participants to a survey.") ?>";
    var addpartErrorMsg = "<?php $clang->eT("No surveys are available. Either you don't have permissions to any surveys or none of your surveys have a token table"); ?>";
    var mapButton = "<?php $clang->eT("Next") ?>";
    var error = "<?php $clang->eT("Error") ?>";
    var addsurvey = "<?php $clang->eT("Add participants to survey") ?>";
    var exportcsv = "<?php $clang->eT("Export CSV") ?>";
    var nooptionselected = "<?php $clang->eT("Please choose either of the options") ?>";
    var removecondition = "<?php $clang->eT("Remove condition") ?>";
    var selectSurvey = "<?php $clang->eT("You must select a survey from the list"); ?>";
    var cancelBtn = "<?php $clang->eT("Cancel") ?>";
    var exportBtn = "<?php $clang->eT("Export") ?>";
    var okBtn = "<?php $clang->eT("OK") ?>";
    var deletefrompanelmsg = "<?php $clang->eT("Select one of the three options") ?>";
    var noRowSelected = "<?php $clang->eT("You have no row selected") ?>";
    var deletefrompanel = "<?php $clang->eT("Delete participant(s) from central participants panel only") ?>";
    var deletefrompanelandtoken = "<?php $clang->eT("Delete participant(s) from central panel and tokens tables") ?>";
    var deletefrompaneltokenandresponse = "<?php $clang->eT("Delete participant(s) from central panel, tokens tables and all associated responses") ?>";
    var deleteMsg = "<br/>"+deletefrompanelmsg+"<br/><br/><center><ol id='selectable' class='selectable' ><li class='ui-widget-content' id='po'>"+deletefrompanel+"</li><li class='ui-widget-content' id='ptt'>"+deletefrompanelandtoken+"</li><li class='ui-widget-content' id='ptta'>"+deletefrompaneltokenandresponse+"</li></ol></center>";
    var searchBtn = "<?php $clang->eT("Search") ?>";
    var shareMsg = "<?php $clang->eT("You can see and edit settings for shared participants in share panel.") ?>"; //PLEASE REVIEW
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/".$urlsearch); ?>";
    var jsonSearchUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/getParticipantsResults_json/search/"); ?>";
    var editUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/editParticipant"); ?>";
    var autowidth = "<?php echo $autowidth ?>";
    var getSearchIDs = "<?php echo Yii::app()->getController()->createUrl("admin/participants/getSearchIDs"); ?>";
    var getaddtosurveymsg = "<?php echo Yii::app()->getController()->createUrl("admin/participants/getaddtosurveymsg"); ?>";
    var minusbutton = "<?php echo Yii::app()->getConfig('adminimageurl') . "deleteanswer.png" ?>";
    var imageurl = "<?php echo Yii::app()->getConfig('adminimageurl') ?>";
    var addbutton = "<?php echo Yii::app()->getConfig('adminimageurl') . "plus.png" ?>";
    var minusbuttonTxt = "<?php $clang->eT("Remove search condition") ?>";
    var addbuttonTxt = "<?php $clang->eT("Add search condition") ?>";
    var delparticipantUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/delParticipant"); ?>";
    var getAttribute_json = "<?php echo Yii::app()->getController()->createUrl("admin/participants/getAttribute_json/pid/"); ?>";
    var exporttocsv = "<?php echo Yii::app()->getController()->createUrl("admin/participants/exporttocsv/id"); ?>";
    var exporttocsvcount = "<?php echo Yii::app()->getController()->createUrl("admin/participants/exporttocsvcount"); ?>";
    var getcpdbAttributes_json = "<?php echo Yii::app()->getController()->createUrl("admin/participants/exporttocsvcount"); ?>";
    var attMapUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/attributeMap"); ?>";
    var editAttributevalue = "<?php echo Yii::app()->getController()->createUrl("admin/participants/editAttributevalue"); ?>";
    var shareUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/shareParticipants"); ?>";
    var surveyUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/addToToken"); ?>";
    var postUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/setSession"); ?>";
    var ajaxUrl = "<?php echo Yii::app()->getConfig('adminimageurl') . "/ajax-loader.gif" ?>";
    var redUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/displayParticipants"); ?>";
    var colNames = '["participant_id","can_edit","<?php $clang->eT("First name") ?>","<?php $clang->eT("Last name") ?>","<?php $clang->eT("Email") ?>","<?php $clang->eT("Blacklisted") ?>","<?php $clang->eT("Surveys") ?>","<?php $clang->eT("Language") ?>","<?php $clang->eT("Owner name") ?>"<?php echo $columnNames; ?>]';
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
<script src="<?php echo Yii::app()->getConfig('generalscripts') . "admin/displayParticipant.js" ?>" type="text/javascript"></script>
<div id ="search" style="display:none">
    <?php
    $optionsearch = array('' => $clang->gT("Select..."),
        'firstname' => $clang->gT("First name"),
        'lastname' => $clang->gT("Last name"),
        'email' => $clang->gT("Email"),
        'blacklisted' => $clang->gT("Blacklisted"),
        'surveys' => $clang->gT("Survey links"),
        'survey' => $clang->gT("Survey name"),
        'language' => $clang->gT("Language"),
        'owner_uid' => $clang->gT("Owner ID"),
        'owner_name' => $clang->gT("Owner name"));
    $optioncontition = array('' =>  $clang->gT("Select..."),
        'equal' =>$clang->gT("Equals"),
        'contains' =>$clang->gT("Contains"),
        'beginswith' =>$clang->gT("Begins with"),
        'notequal' => $clang->gT("Not equal"),
        'notcontains' => $clang->gT("Does not contain"),
        'greaterthan' => $clang->gT("Greater than"),
        'lessthan' => $clang->gT("Less than"));
    if (isset($allattributes) && count($allattributes) > 0) // Add attribute names to select box
    {
        echo "<script type='text/javascript'> optionstring = '";
        foreach ($allattributes as $key => $value)
        {
            $optionsearch[$value['attribute_id']] = $value['attribute_name'];
            echo "<option value=" . $value['attribute_id'] . ">" . $value['attribute_name'] . "</option>";
        }
        echo "';</script>";
    }
    ?>
    <table id='searchtable'>
        <tr>
            <td><?php echo CHtml::dropDownList('field_1', 'id="field_1"', $optionsearch); ?></td>
            <td><?php echo CHtml::dropDownList('condition_1', 'id="condition_1"', $optioncontition); ?></td>
            <td><input type="text" id="conditiontext_1" style="margin-left:10px;" /></td>
            <td><img src=<?php echo Yii::app()->getConfig('adminimageurl') . "plus.png" ?>  id="addbutton" style="margin-bottom:4px" alt='<?php $clang->eT("Add search condition"); ?>'></td>
        </tr>
    </table>
    <br/>


</div>
<br/>
<table id="displayparticipants"></table> <div id="pager"></div>
<p><input type="button" name="addtosurvey" id="addtosurvey" value="<?php $clang->eT("Add to survey") ?>" />
</p>

<div id="fieldnotselected" title="<?php $clang->eT("Error") ?>" style="display:none">
    <p>
<?php $clang->eT("Please select a field"); ?>
    </p>
</div>
<div id="conditionnotselected" title="<?php $clang->eT("Error") ?>" style="display:none">
    <p>
<?php $clang->eT("Please select a condition"); ?>
    </p>
</div>
<div id="norowselected" title="<?php $clang->eT("Error") ?>" style="display:none">
    <p>
<?php $clang->eT("Please select at least one participant"); ?>
    </p>
</div>
<div id="shareform" title="<?php $clang->eT("Share") ?>" style="display:none">
  <div class='popupgroup'>
    <p>
<?php $clang->eT("User with whom the participants are to be shared"); ?></p>
    <p>
        <?php
        $options[''] = $clang->gT("Select...");
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
<?php $clang->eT("Allow this user to edit these participants"); ?>
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
    <form action="<?php echo Yii::app()->getController()->createUrl("admin/participants/attributeMap"); ?>" name="addsurvey" id="addsurvey" method="POST">
        <input type="hidden" name="participant_id" id="participant_id" value=""></input>
        <input type="hidden" name="count" id="count" value=""></input>
        <div class='popupgroup'>
            <h4>Participants</h4>
            <div id='allinview' style='display: none'><?php $clang->eT("Add all participants in your current list to a survey.") ?></div>
            <div id='selecteditems' style='display: none'><?php $clang->eT("Add the selected participants to a survey.") ?></div>
            <br />
        </div>
        <div class='popupgroup'>
		  <h4>
            <?php $clang->eT("Survey"); ?>
          </h4>
          <p>
            <?php
            if (!empty($tokensurveynames))
            {
                //$option[''] = $clang->gT("Select...");
                foreach ($tokensurveynames as $row)
                {
                    $option[$row['surveyls_survey_id']] = $row['surveyls_title'];
                }
                echo CHtml::listBox('survey_id', 'id="survey_id"', $option, array('style'=>'width: 350px; border: 0px; font-size: 1.2em; cursor: pointer', 'size'=>7));
            }
            ?>
          </p><br />
        </div>
        <div class='popupgroup'>
          <h4>
            <?php $clang->eT("Options") ?>
          </h4>
          <p>
            <?php
            $data = array(
                'id' => 'redirect',
                'value' => 'TRUE',
                'style' => 'margin:10px',
            );

            echo CHtml::checkBox('redirect', TRUE, $data);
            ?>
            <label for='redirect'><?php $clang->eT("Display survey tokens after adding?"); ?></label>
          </p>
        </div>
    </form>
</div>
<div id="notauthorised" title="notauthorised" style="display:none">
    <p>
<?php $clang->eT("This is a shared participant and you are not authorised to edit it"); ?></p>

</div>
<div id="exportcsv" title="exportcsv" style="display:none">
        <h4><?php $clang->eT("Attributes to export:"); ?></h4>
        <p>
            <select id="attributes" name="attributes" multiple="multiple" style='width: 350px' size=7>
                <?php
                foreach ($allattributes as $value)
                {
                    echo "<option value=" . $value['attribute_id'] . ">" . $value['attribute_name'] . "</option>\n";
                }
                ?>
            </select>
        </p>
</div>


