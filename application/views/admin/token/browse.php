	<script language='javascript' type='text/javascript'> surveyid = '<?php echo $surveyid; ?>'; </script>
    <script language='javascript' type='text/javascript'> var imgurl = '<?php echo Yii::app()->getConfig('imageurl'); ?>';
    var controllerurl = '<?php echo $this->createUrl("admin/tokens/sa/bounceprocessing/surveyid/$surveyid"); ?>'; </script>

    <div class='menubar'><div class='menubar-title ui-widget-header'><span style='font-weight:bold;'>
    <?php $clang->eT("Data view control");?></span></div>
	<div class='menubar-main'>
    <div class='menubar-left'>
    <?php if (bHasSurveyPermission($surveyid,'tokens','update'))
    {
        if($thissurvey['bounceprocessing']=='N')
        { ?>
            <img src='<?php echo $imageurl;?>/bounce_disabled.png' alt='<?php $clang->eT("You have selected not to use any bounce settings");?>' align='left' />
       <?php }
        else
        { ?>
            <img src='<?php echo $imageurl;?>/bounce.png' id='bounceprocessing' alt='<?php $clang->eT("Bounce processing");?>' align='left' />
      <?php  } ?>
        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' border='0' hspace='0' align='left' />
   <?php  } ?>
    <a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/0/order/$order/searchstring/".urlencode($searchstring));?>'
    title='<?php $clang->eTview("Show start...");?>'>
    <img name='DBeginButton' align='left' src='<?php echo $imageurl;?>/databegin.png' alt='<?php $clang->eT("Show start...");?>' /></a>
    <a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$last/order/$order/searchstring/".urlencode($searchstring));?>'
	title='<?php $clang->eTview("Show previous...");?>'>
	<img name='DBackButton' align='left' src='<?php echo $imageurl;?>/databack.png' alt='<?php $clang->eT("Show previous...");?>' /></a>
	<img src='<?php echo $imageurl;?>/blank.gif' alt='' width='13' height='20' border='0' hspace='0' align='left' />
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$next/order/$order/searchstring/".urlencode($searchstring));?>'
	title='<?php $clang->eTview("Show next...");?>'>
	<img name='DForwardButton' align='left' src='<?php echo $imageurl;?>/dataforward.png' alt='<?php $clang->eT("Show next...");?>' /></a>
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$end/order/$order/searchstring/".urlencode($searchstring));?>'
	title='<?php $clang->eTview("Show last...");?>'>
	<img name='DEndButton' align='left'  src='<?php echo $imageurl;?>/dataend.png' alt='<?php $clang->eT("Show last...");?>' /></a>
	<img src='<?php echo $imageurl;?>/seperator.gif' alt='' border='0' hspace='0' align='left' />
    <form id='tokensearch' method='post' action='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid");?>'>
	<input type='text' name='searchstring' value='<?php echo htmlspecialchars($searchstring,ENT_QUOTES,'utf-8');?>' />
	<input type='submit' value='<?php $clang->eT("Search");?>' />
	<input type='hidden' name='order' value='<?php echo $order;?>' />
	<input type='hidden' name='subaction' value='search' />
	<input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
	</form>
	<form id='tokenrange' method='post' action='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid");?>'>
	<img src='<?php echo $imageurl;?>/seperator.gif' alt='' border='0' />
	<font size='1' face='verdana'>
	&nbsp;<label for='limit'><?php $clang->eT("Records displayed:");?></label> <input type='text' size='4' value='<?php echo $limit;?>' id='limit' name='limit' />
	&nbsp;&nbsp;<label for='start'><?php $clang->eT("Starting from:");?></label> <input type='text' size='4' value='<?php echo $start;?>'  id='start' name='start' />
	&nbsp;<input type='submit' value='<?php $clang->eT("Show");?>' />
	</font>
	<input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
	<input type='hidden' name='action' value='tokens' />
	<input type='hidden' name='subaction' value='browse' />
	<input type='hidden' name='order' value='<?php echo $order;?>' />
	<input type='hidden' name='searchstring' value='<?php echo htmlspecialchars($searchstring,ENT_QUOTES,'utf-8');?>' />
	</form>
	</div></div></div>

	<table class='browsetokens' id='browsetokens' cellpadding='1' cellspacing='1'>
	<tr>
	<th><input type='checkbox' id='tokencheckboxtoggle' /></th>
	<th align='left' >
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/order/tid/searchstring/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ");?>ID' alt='<?php $clang->eT("Sort by: ");?>ID' border='0' align='left' hspace='0' /></a>ID</th>

	<th align='left'  ><?php $clang->eT("Actions");?></th>
	<th align='left'  >
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/order/firstname/searchstring/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("First name");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("First name");?>' border='0' align='left' /></a>
	<?php $clang->eT("First name");?></th>

	<th align='left'><a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/order/lastname/searchstring/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Last name");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Last name");?>' border='0' align='left' /></a>
	<?php $clang->eT("Last name");?></th>

	<th align='left'  ><a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/order/email/searchstring/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Email address");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Email address");?>' border='0' align='left' /></a>
	<?php $clang->eT("Email address");?></th>

	<th align='left'  >
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/order/emailstatus/searchstring/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Email status");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Email status");?>' border='0' align='left' /></a>
	<?php $clang->eT("Email status");?></th>

	<th align='left'  ><a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/order/token/searchstring/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Token");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Token");?>' border='0' align='left' /></a>
	<?php $clang->eT("Token");?></th>

	<th align='left'  >
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/order/language/searchstring/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Language");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Language");?>' border='0' align='left' /></a>
	<?php $clang->eT("Language");?></th>

	<th align='left'  ><a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/order/sent%20desc/searchstring/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Invitation sent?");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Invitation sent?");?>' border='0' align='left' /></a>
	<?php $clang->eT("Invitation sent?");?></th>


	<th align='left'  >
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/remindersent%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Reminder sent?");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Reminder sent?");?>' border='0' align='left' /></a>
	<span><?php $clang->eT("Reminder sent?");?></span></th>

	<th align='left'>
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/remindercount%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Reminder count");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Reminder count");?>' border='0' align='left' /></a>
	<span><?php $clang->eT("Reminder count");?></span></th>

	<th align='left'  ><a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/completed%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Completed?");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Completed?");?>' border='0' align='left' /></a>
	<?php $clang->eT("Completed?");?></th>

	<th align='left'  >
	<a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/usesleft%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Uses left");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Uses left");?>' border='0' align='left' /></a>
	<span><?php $clang->eT("Uses left");?></span></th>

	<th align='left'  ><a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/validfrom%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Valid from");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Valid from");?>' border='0' align='left' /></a>
	<?php $clang->eT("Valid from");?></th>

	<th align='left'  ><a href='<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/validuntil%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php $clang->eT("Sort by: ").$clang->gT("Valid until");?>' alt='<?php $clang->eT("Sort by: ").$clang->gT("Valid until");?>' border='0' align='left' /></a>
	<?php $clang->eT("Valid until");?></th>

	<?php $attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
	foreach ($attrfieldnames as $attr_name=>$attr_translation)
	{
	    echo "<th align='left' >"
	    ."<a href='".$this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid/limit/$limit/start/$start/".$attr_name."/".urlencode($searchstring))."'>"
	    ."<img src='$imageurl/downarrow.png' alt='' title='"
	    .$clang->gT("Sort by: ").htmlspecialchars($attr_translation,ENT_QUOTES,'utf-8')."' border='0' align='left' /></a>".htmlspecialchars($attr_translation,ENT_QUOTES,'utf-8')."</th>\n";
	} ?>
	</tr>

	<?php $tokenfieldorder=array('tid',
                           'firstname',
                           'lastname',
                           'email',
                           'emailstatus',
                           'token',
                           'language',
                           'sent',
                           'remindersent',
                           'remindercount',
                           'completed',
                           'usesleft',
                           'validfrom',
                           'validuntil');
	foreach ($attrfieldnames as $attr_name=>$attr_translation)
	{
	    $tokenfieldorder[]=$attr_name;
	}

	foreach ($bresult as $brow)
	{
	    $brow['token'] = trim($brow['token']);
	    if (trim($brow['validfrom'])!=''){
	        $datetimeobj = new Date_Time_Converter(array($brow['validfrom'] , "Y-m-d H:i:s"));
	        $brow['validfrom']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    };
	    if (trim($brow['validuntil'])!=''){
	        $datetimeobj = new Date_Time_Converter(array($brow['validuntil'] , "Y-m-d H:i:s"));
	        $brow['validuntil']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    };

	    if ($bgc == "evenrow") {$bgc = "oddrow";} else {$bgc = "evenrow";}
		?>
	    <tr class='<?php echo $bgc;?>'>
		<td><input type='checkbox' name='<?php echo $brow['tid'];?>' /></td>

	    <?php foreach ($tokenfieldorder as $tokenfieldname)
	    {

	        if ($tokenfieldname =='email' && $brow['emailstatus'] != 'OK')
	        {
	            if ($brow['emailstatus']!='OptOut')
	            { ?>
	                <td>
	                <a href="#" class='invalidemail' title='<?php $clang->eT('Invalid email address:').htmlspecialchars($brow['emailstatus']);?>' >
	                <?php echo $brow[$tokenfieldname]?></a></td>
	            <?php }
	            else
	            { ?>
	                <td>
	                <a href="#" class='optoutemail' title='<?php $clang->eT('This participant opted out of this survey.');?>' >
	                <?php echo $brow[$tokenfieldname];?></a></td>
	            <?php }
	        }
          else
	        {
	            if  ($tokenfieldname=='tid')
	            { ?>
	                <td><span style='font-weight:bold'><?php echo $brow[$tokenfieldname];?></span></td>
	            <?php }
	            else
	            { ?>
	                <td><?php echo htmlspecialchars($brow[$tokenfieldname]);?></td>
	            <?php }
	        }
	        if ($tokenfieldname=='tid')
	        { ?>
	            <td align='left' style='white-space:nowrap;'>
	            <?php if (bHasSurveyPermission($surveyid, 'tokens','update'))
	            {
	                if (($brow['completed'] == "N" || $brow['completed'] == "") &&$brow['token'])
	                {
	                    $toklang = ($brow['language'] == '') ? $baselanguage : $brow['language']; ?>
	                    <input style='height: 16px; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/do_16.png'  title='<?php $clang->eT("Do survey");?>'  alt= '<?php $clang->eT("Do survey"); ?>' onclick="window.open('<?php echo $this->createUrl("survey/sid/{$surveyid}/lang/{$toklang}/token/{$brow['token']}");?>', '_blank')" />
	                <?php }
	                else
	                { ?>
	                    <img src='<?php echo $imageurl;?>/blank.gif' height='16' alt='' width='16'/>
	                <?php }
	                ?><input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_edit.png'
                       title='<?php $clang->eT("Edit token entry");?>' alt='<?php $clang->eT("Edit token entry");?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/edit/$surveyid/".$brow['tid']);?>', '_top')" />
	            <?php }
                if (bHasSurveyPermission($surveyid, 'tokens','delete'))
                { ?>
                    <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_delete.png'
                    title='<?php $clang->eT("Delete token entry");?>' alt='<?php $clang->eT("Delete token entry");?>' onclick="if (confirm('<?php $clang->eT("Are you sure you want to delete this entry?","js");?> (<?php echo $brow['tid'];?>)')) { <?php echo get2post($this->createUrl("admin/tokens/delete/$surveyid/".$brow['tid']."?order=1"));?>}"  />
                <?php }

	            if ($brow['completed'] != "N" && $brow['completed']!="" && $surveyprivate == "N"  && $thissurvey['active']=='Y')
	            {
	                // Get response Id
	                $this->load->helper("database");
	                $query=db_execute_assoc("SELECT id FROM ".$this->db->dbprefix('survey_'.$surveyid)." WHERE token='{$brow['token']}' ORDER BY id desc");
	                //$result=db_execute_num($query) or safe_die ("<br />Could not find token!<br />\n" .$connect->ErrorMsg());
					//Not working: $query = $this->Surveys_dynamic_model->getSomeRecords(array("id"),$surveyid,array("token"=>$brow['token']),"id desc");
	                $id = reset($query->row_array());
	                // UPDATE button to the tokens display in the MPID Actions column
	                if  ($id)
	                { ?>
	                    <input type='image' src='<?php echo $imageurl;?>/token_viewanswer.png' style='height: 16; width: 16px;' onclick="window.open('<?php echo $this->createUrl("admin/browse/$surveyid/id/".$id);?>', '_top')"
                        title='<?php $clang->eT("View/Update last response");?>' alt='<?php $clang->eT("View/Update last response");?>' />
	                <?php }
	            }
	            elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] == "N" && trim($brow['email'])!='' && bHasSurveyPermission($surveyid, 'tokens','update'))
	            { ?>
	                <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_invite.png'
                    title='<?php $clang->eT("Send invitation email to this entry");?>' alt='<?php $clang->eT("Send invitation email to this entry");?>' onclick="<?php echo get2post($this->createUrl("admin/tokens/email/$surveyid/")."?tid=".$brow['tid']);?>" />
	            <?php }
	            elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] != "N" && trim($brow['email'])!='')  // reminder button
	            { ?>
	                <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_remind.png'
                    title='<?php $clang->eT("Send reminder email to this entry");?>' alt='<?php $clang->eT("Send reminder email to this entry");?>' onclick="<?php echo get2post($this->createUrl("admin/tokens/remind/$surveyid/")."?tid=".$brow['tid']);?>" />
	           <?php } ?>
  	            </td>
	       <?php }
	    } ?>
	    </tr>
	<?php }
	// Multiple item actions
	if (count($bresult) > 0) { ?>
	    <tr class='<?php echo $bgc;?>'>
		<td align='left' style='text-align: left' colspan='<?php echo (count($tokenfieldorder)+1);?>'>
        <?php
        if (bHasSurveyPermission($surveyid, 'tokens','delete'))
        { ?>
            <img src='<?php echo $imageurl;?>/blank.gif' height='16' width='16' alt='' />
            <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_delete.png'
            title='<?php $clang->eT("Delete the selected entries");?>' alt='<?php $clang->eT("Delete the selected entries");?>'
            onclick="if($('#tokenboxeschecked').val()){ if (confirm('<?php $clang->eT("Are you sure you want to delete the selected entries?","js");?>')) { <?php echo get2post($this->createUrl("admin/tokens/delete/$surveyid/")."?action=tokens&amp;sid={$surveyid}&amp;subaction=delete&amp;tokenids=document.getElementById('tokenboxeschecked').value&amp;limit={$limit}&amp;start={$start}&amp;order={$order}");?>}}else{ alert('<?php $clang->eT("No tokens selected",'js');?>');}"  />

        <?php }

        if (bHasSurveyPermission($surveyid, 'tokens','update'))
        { ?>
            &nbsp;
            <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_invite.png'
            title='<?php $clang->eT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)");?>'
            alt='<?php $clang->eT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)");?>'
            onclick="<?php echo get2post($this->createUrl("admin/tokens/sa/email/surveyid/$surveyid/tids")."?tokenids=document.getElementById('tokenboxeschecked').value");?>" />
            &nbsp;
            <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_remind.png'
            title='<?php $clang->eT("Send reminder email to the selected entries (if they have already received the invitation email)");?>'
            alt='<?php $clang->eT("Send reminder email to the selected entries (if they have already received the invitation email)");?>'
            onclick="<?php echo get2post($this->createUrl("admin/tokens/sa/remind/surveyid/$surveyid/tids")."?tokenids=document.getElementById('tokenboxeschecked').value");?>" />
        <?php } ?>
        <input type='hidden' id='tokenboxeschecked' value='' onchange='alert(this.value)' />
	    </td>
	    </tr>
	<?php }
	//End multiple item actions
?>
	</table>

    <div id='dialog-modal'></div>

<!-- Code for central Participants database -->
    <p><input type='button' name='addtocpdb' id='addtocpdb' value='<?php $clang->eT("Add participants to central database");?>'/><br />
<!-- End of Code for central Participants database -->
<script type="text/javascript">
var cancelBtn = "<?php $clang->eT("Cancel") ?>";
var okBtn = "<?php $clang->eT("OK") ?>";
var survey_id = "<?php echo $surveyid; ?>";
var addtocpdbUrl = "<?php echo $this->createUrl("admin/participants/sa/addToCentral"); ?>";
var addpartAddBtn = "<?php $clang->eT("Add to CPDB") ?>";
var addpartTitle = "<?php $clang->eT('Add participant to CPDB'); ?>";
var attMapUrl = "<?php echo $this->createUrl("admin/participants/sa/attributeMapToken/sid/");?>";
var postUrl = "<?php echo $this->createUrl("admin/participants/sa/setSession"); ?>";
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
<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getConfig('generalscripts')."jquery/css/jquery.multiselect.css" ?>" />
<script src="<?php echo Yii::app()->getConfig('adminscripts')."tokentocpdb.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('generalscripts')."jquery/jquery.multiselect.min.js" ?>" type="text/javascript"></script>
<div id="norowselected" title="<?php $clang->eT("Error") ?>" style="display:none">
            <p>
                <?php $clang->eT("Please select at least one participant to be added"); ?>
            </p>
        </div>
<?php $ajaxloader = array('src' => Yii::app()->baseUrl.'/images/ajax-loader.gif',
                          'alt' => 'Ajax Loader',
                          'title' => 'Ajax Loader'); ?>
 <div id="processing" title="<?php $clang->eT("Processing .....") ?>" style="display:none">
<?php echo '<img src="', $ajaxloader['src'], '" alt="', $ajaxloader['alt'], '" title="', $ajaxloader['title'], '" />' ?>
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