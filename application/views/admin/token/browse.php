<?php
    $uidNames = array();
    /* Build the options for additional languages */
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
    /* Build the columnNames for the extra attributes */
    /* and, build the columnModel */
    $names = getTokenFieldsAndNames($surveyid, true);
    $attributes = getAttributeFieldNames($surveyid);
    if (count($attributes) > 0)
    {
        foreach ($names as $name)
        {
            $attnames[] = '"' . $name . '"';
        }
        foreach ($attributes as $row)
        {
            $uidNames[] = '{ "name":"' . $row . '", "index":"' . $row . '", "sorttype":"string", "sortable": true, "align":"center", "editable":true, "width":75}';
        }
        $columnNames = implode(',', $attnames); //Add to the end of the standard list of columnNames
    }
    else
    {
        $columnNames = "";
    }
    /* Build the javasript variables to pass to the jqGrid */
?>
<script type="text/javascript">
    var imageurl = "<?php echo Yii::app()->getConfig('adminimageurl'); ?>";
    var mapButton = "<?php $clang->eT("Next") ?>";
    var error = "<?php $clang->eT("Error") ?>";
    var removecondition = "<?php $clang->eT("Remove condition") ?>";
    var cancelBtn = "<?php $clang->eT("Cancel") ?>";
    var exportBtn = "<?php $clang->eT("Export") ?>";
    var okBtn = "<?php $clang->eT("OK") ?>";
    var noRowSelected = "<?php $clang->eT("You have no row selected") ?>";
    var searchBtn = "<?php $clang->eT("Search") ?>";
    var shareMsg = "<?php $clang->eT("You can see and edit settings for shared participants in share panel.") ?>"; //PLEASE REVIEW
    var jsonSearchUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/getSearch_json/surveyid/$surveyid/search"); ?>";
    var getSearchIDs = "<?php echo Yii::app()->getController()->createUrl("admin/participants/getSearchIDs"); ?>";
    var addbutton = "<?php echo Yii::app()->getConfig('adminimageurl')."plus.png" ?>";
    var minusbutton = "<?php echo Yii::app()->getRequest()->getBaseUrl() . "/images/deleteanswer.png" ?>";
    var survey_id = "<?php echo $surveyid; ?>";
    var delUrl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/delete/surveyid/" . $surveyid); ?>";
    var cancelBtn = "<?php $clang->eT("Cancel") ?>";
    var okBtn = "<?php echo $clang->eT("OK") ?>";
    var delmsg = "<?php $clang->eT("Are you sure you want to delete the selected entries?") ?>";
    var surveyID = "<?php echo $surveyid; ?>";
    var jsonUrl = "<?php echo Yii::app()->getController()->createUrl('admin/tokens/getTokens_json/surveyid/' . $surveyid); ?>";
    var postUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/setSession"); ?>";
    var editUrl = "<?php echo Yii::app()->getController()->createUrl('admin/tokens/editToken/surveyid/' . $surveyid); ?>";
    var remindurl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/remind/surveyid/{$surveyid}/tids/|"); ?>";
    var attMapUrl = "<?php echo $this->createUrl("admin/participants/attributeMapToken/sid/");?>";
    var invitemsg = "<?php echo $clang->eT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)"); ?>"
    var remindmsg = "<?php echo $clang->eT("Send reminder email to the selected entries (if they have already received the invitation email)"); ?>"
    var inviteurl = "<?php echo Yii::app()->getController()->createUrl("admin/tokens/email/surveyid/{$surveyid}/tids/|"); ?>";
    var searchtypes = ["<?php $clang->eT("Equals") ?>","<?php $clang->eT("Contains") ?>","<?php $clang->eT("Not equal") ?>","<?php $clang->eT("Not contains") ?>","<?php $clang->eT("Greater than") ?>","<?php $clang->eT("Less than") ?>"]
    var colNames = ["ID","<?php $clang->eT("Action") ?>","<?php $clang->eT("First name") ?>","<?php $clang->eT("Last name") ?>","<?php $clang->eT("Email address") ?>","<?php $clang->eT("Email status") ?>","<?php $clang->eT("Token") ?>","<?php $clang->eT("Language") ?>","<?php $clang->eT("Invitation sent?") ?>","<?php $clang->eT("Reminder sent?") ?>","<?php $clang->eT("Reminder count") ?>","<?php $clang->eT("Completed?") ?>","<?php $clang->eT("Uses left") ?>","<?php $clang->eT("Valid from") ?>","<?php $clang->eT("Valid until") ?>",<?php echo $columnNames; ?>];
    var colModels = [
    { "name":"tid", "index":"tid", "width":30, "align":"center", "sorttype":"int", "sortable": true, "editable":false, "hidden":false},
    { "name":"action", "index":"action", "sorttype":"string", "sortable": false, "width":90, "align":"center", "editable":false},
    { "name":"firstname", "index":"firstname", "sorttype":"string", "sortable": true, "width":100, "align":"center", "editable":true},
    { "name":"lastname", "index":"lastname", "sorttype":"string", "sortable": true,"width":100, "align":"center", "editable":true},
    { "name":"email", "index":"email","align":"center","width":170, "sorttype":"string", "sortable": true, "editable":true},
    { "name":"emailstatus", "index":"emailstatus","align":"center","width":80,"sorttype":"string", "sortable": true, "editable":true},
    { "name":"token", "index":"token","align":"center", "sorttype":"int", "sortable": true,"width":150,"editable":true},
    { "name":"language", "index":"language","align":"center", "sorttype":"int", "sortable": true,"width":100,"editable":true, "edittype":"select", "editoptions":{"value":"<?php echo $langnames; ?>"}},
    { "name":"sent", "index":"sent","align":"center", "sorttype":"int", "sortable": true,"width":130,"editable":true},
    { "name":"remindersent", "index":"remindersent","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"remindercount", "index":"remindercount","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"completed", "index":"completed","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"usesleft", "index":"usesleft","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"validfrom", "index":"validfrom","align":"center", "sorttype":"int", "sortable": true,"width":160,"editable":true},
    { "name":"validuntil", "index":"validuntil","align":"center", "sorttype":"int", "sortable": true,"width":160,"editable":true},
    <?php echo implode(",\n", $uidNames); ?>];
    <!--
    for(i=0; i<document.forms.length; i++)
        {
        var el = document.createElement('input');
        el.type = 'hidden';
        el.name = 'checksessionbypost';
        el.value = 'kb9e2u4s55';
        document.forms[i].appendChild(el);
    }
    function addHiddenElement(theform,thename,thevalue)
    {
        var myel = document.createElement('input');
        myel.type = 'hidden';
        myel.name = thename;
        theform.appendChild(myel);
        myel.value = thevalue;
        return myel;
    }

    function sendPost(myaction,checkcode,arrayparam,arrayval)
    {
        var myform = document.createElement('form');
        document.body.appendChild(myform);
        myform.action =myaction;
        myform.method = 'POST';
        for (i=0;i<arrayparam.length;i++)
            {
            addHiddenElement(myform,arrayparam[i],arrayval[i])
        }
        addHiddenElement(myform,'checksessionbypost',checkcode)
        myform.submit();
    }

    //-->
</script>
<div id ="search" style="display:none">
    <?php
        $optionsearch = array('' => 'Select One',
        'firstname' => $clang->gT("First name"),
        'lastname' => $clang->gT("Last name"),
        'email' => $clang->gT("Email address"),
        'emailstatus' => $clang->gT("Email status"),
        'token' => $clang->gT("Token"),
        'language' => $clang->gT("Language"),
        'sent' => $clang->gT("Invitation sent?"),
        'sentreminder' => $clang->gT("Reminder sent?"),
        'remindercount' => $clang->gT("Reminder count"),
        'completed' => $clang->gT("Completed?"),
        'usesleft' => $clang->gT("Uses left"),
        'validfrom' => $clang->gT("Valid from"),
        'validuntil' => $clang->gT("Valid until"));
        $optioncontition = array('' => 'Select One',
        'equal' => $clang->gT("Equals"),
        'contains' => $clang->gT("Contains"),
        'notequal' => $clang->gT("Not equal"),
        'notcontains' => $clang->gT("Not contains"),
        'greaterthan' => $clang->gT("Greater than"),
        'lessthan' => $clang->gT("Less than"));
    ?>
    <table id='searchtable'>
        <tr>
            <td><?php echo CHtml::dropDownList('field_1', 'id="field_1"', $optionsearch); ?></td>
            <td><?php echo CHtml::dropDownList('condition_1', 'id="condition_1"', $optioncontition); ?></td>
            <td><input type="text" id="conditiontext_1" style="margin-left:10px;" /></td>
            <td><img src=<?php echo Yii::app()->getConfig('adminimageurl')."plus.png" ?> alt='<?php $clang->eT("Add another search criteria");?>' id="addbutton" style="margin-bottom:4px"></td>
        </tr>
    </table>
    <br/>


</div>
<br/>
<table id="displaytokens"></table> <div id="pager"></div>
<!--p><input type="button" name="sendinvitations" id="sendinvitations" value="Send Invitations" onclick='window.open("<?php echo Yii::app()->getController()->createUrl("admin/tokens/email/surveyid/{$surveyid}/tids/|"); ?>"+$("#displaytokens").getGridParam("selarrrow").join("|"), "_blank")' /><input type="button" name="sendreminders" id="sendreminders" value="Send Reminders" onclick='window.open("<?php echo Yii::app()->getController()->createUrl("admin/tokens/remind/surveyid/{$surveyid}/tids/|"); ?>"+$("#displaytokens").getGridParam("selarrrow").join("|"), "_blank")' />
</p-->
<p><input type='button' name='addtocpdb' id='addtocpdb' value='<?php $clang->eT("Add participants to central database");?>'/><br />

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
