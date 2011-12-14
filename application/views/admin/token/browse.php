<?php
$uidNames =array();
/* Build the options for additional languages */
$j=1;
$getlangvalues = getLanguageData();
if(Yii::app()->session['adminlang']!='auto')
{
$lname[0]=Yii::app()->session['adminlang'].":".$getlangvalues[Yii::app()->session['adminlang']]['description'];
}
foreach ($getlangvalues as $keycode => $keydesc) {
                if(Yii::app()->session['adminlang']!=$keycode)
                {
                        $cleanlangdesc = str_replace (";"," -",$keydesc['description']);
                        $lname[$j]=$keycode.":".$cleanlangdesc;
                        $j++;
                }
	}
	$langnames = implode(";",$lname);
/* Build the columnNames for the extra attributes */
/* and, build the columnModel */
$names=GetTokenFieldsAndNames($surveyid,true);
$attributes=GetAttributeFieldNames($surveyid);
    if(count($attributes) > 0)
        {
        foreach($names as $name)
            {
                $attnames[]='"'.$name.'"';
            }
        foreach($attributes as $row)
            {
                $uidNames[]='{ "name":"'.$row.'", "index":"'.$row.'", "sorttype":"string", "sortable": true, "align":"center", "editable":true, "width":75}';
            }
        $columnNames = implode(',',$attnames); //Add to the end of the standard list of columnNames
    }
else
    {
      $columnNames = "";
    }
/* Build the javasript variables to pass to the jqGrid */
?>
<script type="text/javascript">
var mapButton = "<?php echo $clang->gT("Next") ?>";
var error = "<?php echo $clang->gT("Error") ?>";
var removecondition = "<?php echo $clang->gT("Remove condition") ?>";
var cancelBtn = "<?php echo $clang->gT("Cancel") ?>";
var exportBtn = "<?php echo $clang->gT("Export") ?>";
var okBtn = "<?php echo $clang->gT("OK") ?>";
var noRowSelected = "<?php echo $clang->gT("You have no row selected") ?>";
var searchBtn = "<?php echo $clang->gT("Search") ?>";
var shareMsg = "<?php echo $clang->gT("You can see and edit settings for shared participant in share panel.") ?>"; //PLEASE REVIEW
var jsonSearchUrl = "<?php echo Yii::app()->createUrl("admin/tokens/sa/getSearch_json/surveyid/$surveyid/search");?>";
var getSearchIDs = "<?php echo Yii::app()->createUrl("admin/participants/sa/getSearchIDs"); ?>";
var addbutton = "<?php echo Yii::app()->getRequest()->getBaseUrl()."/images/plus.png" ?>";
var minusbutton = "<?php echo Yii::app()->getRequest()->getBaseUrl()."/images/deleteanswer.png" ?>";
var delUrl = "<?php echo Yii::app()->createUrl("admin/tokens/sa/delete/surveyid/".$surveyid);?>";
var cancelBtn = "<?php $clang->eT("Cancel") ?>";
var okBtn = "<?php echo $clang->eT("OK") ?>";
var delmsg = "<?php echo $clang->gT("Are you sure you want to delete this entry?") ?>";
var surveyID = "<?php echo $surveyid; ?>";
var jsonUrl = "<?php echo Yii::app()->createUrl('admin/tokens/sa/getTokens_json/surveyid/'.$surveyid); ?>";
var editUrl = "<?php echo Yii::app()->createUrl('admin/tokens/sa/editToken/surveyid/'.$surveyid); ?>";
var remindurl = "<?php echo Yii::app()->createUrl("admin/tokens/sa/remind/surveyid/{$surveyid}/tids/|");?>";
var invitemsg = "<?php echo $clang->eT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)"); ?>"
var remindmsg = "<?php echo $clang->eT("Send reminder email to the selected entries (if they have already received the invitation email)"); ?>"
var inviteurl = "<?php echo Yii::app()->createUrl("admin/tokens/sa/email/surveyid/{$surveyid}/tids/|");?>";
var searchtypes = ["<?php echo $clang->gT("Equals") ?>","<?php echo $clang->gT("Contains") ?>","<?php echo $clang->gT("Not equal") ?>","<?php echo $clang->gT("Not contains") ?>","<?php echo $clang->gT("Greater than") ?>","<?php echo $clang->gT("Less than") ?>"]
var colNames = ["ID","<?php echo $clang->gT("Action") ?>","<?php echo $clang->gT("First name") ?>","<?php echo $clang->gT("Last name") ?>","<?php echo $clang->gT("Email address") ?>","<?php echo $clang->gT("Email status") ?>","<?php echo $clang->gT("Token") ?>","<?php echo $clang->gT("Language") ?>","<?php echo $clang->gT("Invitation sent?") ?>","<?php echo $clang->gT("Reminder sent?") ?>","<?php echo $clang->gT("Reminder count") ?>","<?php echo $clang->gT("Completed?") ?>","<?php echo $clang->gT("Uses left") ?>","<?php echo $clang->gT("Valid from") ?>","<?php echo $clang->gT("Valid until") ?>",<?php echo $columnNames; ?>];
var colModels = [{ "name":"tid", "index":"tid", "width":20, "align":"center", "sorttype":"int", "sortable": true, "editable":false, "hidden":false},
    { "name":"action", "index":"action", "sorttype":"string", "sortable": false, "width":70, "align":"center", "editable":false},
    { "name":"firstname", "index":"firstname", "sorttype":"string", "sortable": true, "width":100, "align":"center", "editable":true},
    { "name":"lastname", "index":"lastname", "sorttype":"string", "sortable": true,"width":100, "align":"center", "editable":true},
    { "name":"email", "index":"email","align":"center","width":100, "sorttype":"string", "sortable": true, "editable":true},
    { "name":"emailstatus", "index":"emailstatus","align":"center","width":80,"sorttype":"string", "sortable": true, "editable":true, "edittype":"checkbox", "editoptions":{ "value":"OK:N"}},
    { "name":"token", "index":"token","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"language", "index":"language","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true, "edittype":"select", "editoptions":{"value":"<?php echo $langnames; ?>"}},
    { "name":"sent", "index":"sent","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true, "edittype":"checkbox", "editoptions":{ "value":"Y:N"}},
    { "name":"remindersent", "index":"remindersent","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true, "edittype":"checkbox", "editoptions":{ "value":"Y:N"}},
    { "name":"remindercount", "index":"remindercount","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"completed", "index":"completed","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true, "edittype":"checkbox", "editoptions":{ "value":"Y:N"}},
    { "name":"usesleft", "index":"usesleft","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true},
    { "name":"validfrom", "index":"validfrom","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true, "editoptions":{ dataInit:function (elem) {$(elem).datepicker();}}},
    { "name":"validuntil", "index":"validuntil","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":true, "editoptions":{ dataInit:function (elem) {$(elem).datepicker();}}},<?php echo implode(",\n",$uidNames);?>];
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
$optionsearch = array( '' => 'Select One',
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
$optioncontition = array( '' => 'Select One',
                      'equal' => $clang->gT("Equals"),
                      'contains' => $clang->gT("Contains"),
                      'notequal' => $clang->gT("Not equal"),
                      'notcontains' => $clang->gT("Not contains"),
                      'greaterthan' => $clang->gT("Greater than"),
                      'lessthan' => $clang->gT("Less than"));
?>
<table id='searchtable'>
<tr>
<td><?php echo CHtml::dropDownList('field_1','id="field_1"',$optionsearch); ?></td>
<td><?php echo CHtml::dropDownList('condition_1','id="condition_1"',$optioncontition); ?></td>
<td><input type="text" id="conditiontext_1" style="margin-left:10px;" /></td>
<td><img src=<?php echo Yii::app()->getRequest()->getBaseUrl()."/images/plus.png" ?>  id="addbutton" style="margin-bottom:4px"></td>
</tr>
</table>
<br/>


</div>
<br/>
<table id="displaytokens"></table> <div id="pager"></div>
<!--p><input type="button" name="sendinvitations" id="sendinvitations" value="Send Invitations" onclick='window.open("<?php echo Yii::app()->createUrl("admin/tokens/sa/email/surveyid/{$surveyid}/tids/|");?>"+$("#displaytokens").getGridParam("selarrrow").join("|"), "_blank")' /><input type="button" name="sendreminders" id="sendreminders" value="Send Reminders" onclick='window.open("<?php echo Yii::app()->createUrl("admin/tokens/sa/remind/surveyid/{$surveyid}/tids/|");?>"+$("#displaytokens").getGridParam("selarrrow").join("|"), "_blank")' />
</p-->
</table>

<div id="fieldnotselected" title="<?php echo $clang->gT("Error") ?>" style="display:none">
	<p>
		<?php echo $clang->gT("Please select a field"); ?>
	</p>
</div>
<div id="conditionnotselected" title="<?php echo $clang->gT("Error") ?>" style="display:none">
	<p>
		<?php echo $clang->gT("Please select a condition"); ?>
	</p>
</div>
<div id="norowselected" title="<?php echo $clang->gT("Error") ?>" style="display:none">
	<p>
		<?php echo $clang->gT("Please select at least one token"); ?>
	</p>
</div>
<div class="ui-widget ui-helper-hidden" id="client-script-return-msg" style="display:none"></div>