	<div class='menubar'><div class='menubar-title ui-widget-header'><span style='font-weight:bold;'>
    <?php echo $clang->gT("Data view control");?></span></div>
	<div class='menubar-main'>
    <div class='menubar-left'>
    <?php if (bHasSurveyPermission($surveyid,'tokens','update'))
    {
        if($thissurvey['bounceprocessing']=='N')
        { ?>
            <img src='<?php echo $imageurl;?>/bounce_disabled.png' alt='<?php echo $clang->gT("You have selected not to use any bounce settings");?>' align='left' />
       <?php }
        else
        { ?>
            <img src='<?php echo $imageurl;?>/bounce.png' id='bounceprocessing' alt='<?php echo $clang->gT("Bounce processing");?>' align='left' />
      <?php  } ?>
        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' border='0' hspace='0' align='left' />
   <?php  } ?>
    
    <a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/0/$order/".urlencode($searchstring));?>'
    	title='<?php echo $clang->gTview("Show start...");?>'>
    <img name='DBeginButton' align='left' src='<?php echo $imageurl;?>/databegin.png' alt='<?php echo $clang->gT("Show start...");?>' /></a>
    <a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$last/$order/".urlencode($searchstring));?>'
	 title='<?php echo $clang->gTview("Show previous...");?>'>
	<img name='DBackButton' align='left' src='<?php echo $imageurl;?>/databack.png' alt='<?php echo $clang->gT("Show previous...");?>' /></a>
	<img src='<?php echo $imageurl;?>/blank.gif' alt='' width='13' height='20' border='0' hspace='0' align='left' />
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$next/$order/".urlencode($searchstring));?>'
	title='<?php echo $clang->gTview("Show next...");?>'>
	<img name='DForwardButton' align='left' src='<?php echo $imageurl;?>/dataforward.png' alt='<?php echo $clang->gT("Show next...");?>' /></a>
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$end/$order/".urlencode($searchstring));?>'
	title='<?php echo $clang->gTview("Show last...");?>'>
	<img name='DEndButton' align='left'  src='<?php echo $imageurl;?>/dataend.png' alt='<?php echo $clang->gT("Show last...");?>' /></a>
	<img src='<?php echo $imageurl;?>/seperator.gif' alt='' border='0' hspace='0' align='left' />
	

<form id='tokensearch' method='post' action='<?php echo site_url("admin/tokens/browse/$surveyid");?>'>
	<input type='text' name='searchstring' value='<?php echo htmlspecialchars($searchstring,ENT_QUOTES,'utf-8');?>' />
	<input type='submit' value='<?php echo $clang->gT("Search");?>' />
	<input type='hidden' name='order' value='<?php echo $order;?>' />
	<input type='hidden' name='subaction' value='search' />
	<input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
	</form>
	<form id='tokenrange' method='post' action='<?php echo site_url("admin/tokens/browse/$surveyid");?>'>
	<img src='<?php echo $imageurl;?>/seperator.gif' alt='' border='0' />
	<font size='1' face='verdana'>
	&nbsp;<label for='limit'><?php echo $clang->gT("Records displayed:");?></label> <input type='text' size='4' value='<?php echo $limit;?>' id='limit' name='limit' />
	&nbsp;&nbsp;<label for='start'><?php echo $clang->gT("Starting from:");?></label> <input type='text' size='4' value='<?php echo $start;?>'  id='start' name='start' />
	&nbsp;<input type='submit' value='<?php echo $clang->gT("Show");?>' />
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
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/tid/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ");?>ID' alt='<?php echo $clang->gT("Sort by: ");?>ID' border='0' align='left' hspace='0' /></a>ID</th>

	<th align='left'  ><?php echo $clang->gT("Actions");?></th>
	<th align='left'  >
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/firstname/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("First name");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("First name");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("First name");?></th>

	<th align='left'><a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/lastname/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Last name");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Last name");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Last name");?></th>

	<th align='left'  ><a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/email/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Email address");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Email address");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Email address");?></th>

	<th align='left'  >
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/emailstatus/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Email status");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Email status");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Email status");?></th>

	<th align='left'  ><a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/token/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Token");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Token");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Token");?></th>

	<th align='left'  >
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/language/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Language");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Language");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Language");?></th>

	<th align='left'  ><a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/sent%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Invitation sent?");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Invitation sent?");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Invitation sent?");?></th>


	<th align='left'  >
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/remindersent%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Reminder sent?");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Reminder sent?");?>' border='0' align='left' /></a>
	<span><?php echo $clang->gT("Reminder sent?");?></span></th>

	<th align='left'>
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/remindercount%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Reminder count");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Reminder count");?>' border='0' align='left' /></a>
	<span><?php echo $clang->gT("Reminder count");?></span></th>

	<th align='left'  ><a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/completed%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Completed?");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Completed?");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Completed?");?></th>

	<th align='left'  >
	<a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/usesleft%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Uses left");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Uses left");?>' border='0' align='left' /></a>
	<span><?php echo $clang->gT("Uses left");?></span></th>

	<th align='left'  ><a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/validfrom%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Valid from");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Valid from");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Valid from");?></th>

	<th align='left'  ><a href='<?php echo site_url("admin/tokens/browse/$surveyid/$limit/$start/validuntil%20desc/".urlencode($searchstring));?>'>
	<img src='<?php echo $imageurl;?>/downarrow.png' title='<?php echo $clang->gT("Sort by: ").$clang->gT("Valid until");?>' alt='<?php echo $clang->gT("Sort by: ").$clang->gT("Valid until");?>' border='0' align='left' /></a>
	<?php echo $clang->gT("Valid until");?></th>

	<?php $attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
	foreach ($attrfieldnames as $attr_name=>$attr_translation)
	{
	    echo "<th align='left' >"
	    ."<a href='".site_url("admin/tokens/browse/$surveyid/$limit/$start/".$attr_name."/".urlencode($searchstring))."'>"
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

	foreach ($bresult->result_array() as $brow)
	{
	    $brow['token'] = trim($brow['token']);
	    if (trim($brow['validfrom'])!=''){
	        $datetimeobj = new Date_Time_Converter($brow['validfrom'] , "Y-m-d H:i:s");
	        $brow['validfrom']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
	    };
	    if (trim($brow['validuntil'])!=''){
	        $datetimeobj = new Date_Time_Converter($brow['validuntil'] , "Y-m-d H:i:s");
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
	                <a href="#" class='invalidemail' title='<?php echo $clang->gT('Invalid email address:').htmlspecialchars($brow['emailstatus']);?>' >
	                <?php echo $brow[$tokenfieldname]?></a></td>
	            <?php }
	            else
	            { ?>
	                <td>
	                <a href="#" class='optoutemail' title='<?php echo $clang->gT('This participant opted out of this survey.');?>' >
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
	                    <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/do_16.png' title='<?php
	                    echo $clang->gT("Do Survey");?>' alt='<?php echo $clang->gT("Do Survey");?>' onclick="window.open('{$publicurl}/index.php?sid={$surveyid}&amp;lang={$toklang}&amp;token=trim($brow['token'])', '_blank')\" />
	                <?php }
	                else
	                { ?>
	                    <img src='<?php echo $imageurl;?>/blank.gif' height='16' alt='' width='16'/>
	                <?php }
	                ?><input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_edit.png' title='<?php
	                echo $clang->gT("Edit token entry");?>' alt='<?php echo $clang->gT("Edit token entry");?>' onclick="window.open('<?php echo site_url("admin/tokens/edit/$surveyid/".$brow['tid']);?>', '_top')" />
	            <?php }
                if (bHasSurveyPermission($surveyid, 'tokens','delete'))
                { ?>
                    <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_delete.png' title='<?php
                    echo $clang->gT("Delete token entry");?>' alt='<?php echo $clang->gT("Delete token entry");?>' onclick="if (confirm('<?php echo $clang->gT("Are you sure you want to delete this entry?","js");?> (<?php echo $brow['tid'];?>)')) {<?php echo get2post(site_url("admin/tokens/delete/$surveyid/".$brow['tid']."?order=1"));?>}"  />
                <?php }
                
	            if ($brow['completed'] != "N" && $brow['completed']!="" && $surveyprivate == "N"  && $thissurvey['active']=='Y')
	            { 
	                // Get response Id
	                //$query="SELECT id FROM ".db_table_name('survey_'.$surveyid)." WHERE token='{$brow['token']}' ORDER BY id desc";
	                //$result=db_execute_num($query) or safe_die ("<br />Could not find token!<br />\n" .$connect->ErrorMsg());
					$this->load->model("survey_dynamic_model");
					$query = $this->survey_dynamic_model->getSomeRecords(array("id"),$surveyid,array("token"=>$brow['token']),"id desc");
	                list($id) = $query->row_array();
	                // UPDATE button to the tokens display in the MPID Actions column
	                if  ($id)
	                { ?>
	                    <input type='image' src='<?php echo $imageurl;?>/token_viewanswer.png' style='height: 16; width: 16px;' onclick="window.open('$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id=$id', '_top')" title='<?php
	                    echo $clang->gT("View/Update last response");?>' alt='<?php echo $clang->gT("View/Update last response");?>' />
	                <?php } 
	            }
	            elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] == "N" && trim($brow['email'])!='' && bHasSurveyPermission($surveyid, 'tokens','update'))
	            { ?> 
	                <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_invite.png' title='<?php
	                echo $clang->gT("Send invitation email to this entry");?>' alt='<?php echo $clang->gT("Send invitation email to this entry");?>' onclick="<?php echo get2post(site_url("admin/tokens/email/$surveyid/")."?tid=".$brow['tid']);?>" />
	            <?php }
	            elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] != "N" && trim($brow['email'])!='')  // reminder button
	            { ?>
	                <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_remind.png' title='<?php
	                echo $clang->gT("Send reminder email to this entry");?>' alt='<?php echo $clang->gT("Send reminder email to this entry");?>' onclick="<?php echo get2post(site_url("admin/tokens/remind/$surveyid/")."?tid=".$brow['tid']);?>" />
	           <?php } ?>
  	            </td>
	       <?php }
	    } ?>
	    </tr>
	<?php }
	// Multiple item actions
	if ($bresult->num_rows() > 0) { ?>
	    <tr class='<?php echo $bgc;?>'>
		<td align='left' style='text-align: left' colspan='<?php echo (count($tokenfieldorder)+1);?>'>
        <?php
        if (bHasSurveyPermission($surveyid, 'tokens','delete'))
        { ?>
            <img src='<?php echo $imageurl;?>/blank.gif' height='16' width='16' alt='' />
            <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_delete.png' title='<?php
            echo $clang->gT("Delete the selected entries");?>' alt='<?php echo $clang->gT("Delete the selected entries");?>' onclick="if($('#tokenboxeschecked').val()){if (confirm('<?php echo $clang->gT("Are you sure you want to delete the selected entries?","js");?>')) {<?php
            echo get2post(site_url("admin/tokens/delete/$surveyid/")."?action=tokens&amp;sid={$surveyid}&amp;subaction=delete&amp;tokenids=document.getElementById('tokenboxeschecked').value&amp;limit={$limit}&amp;start={$start}&amp;order={$order}");?>}}else{alert('<?php echo $clang->gT("No tokens selected");?>');}"  />
            
        <?php }
        
        if (bHasSurveyPermission($surveyid, 'tokens','update'))
        { ?>
            &nbsp;
            <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_invite.png' title='<?php
            echo $clang->gT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)");
            ?>' alt='<?php
            echo $clang->gT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)");
            ?>' onclick="<?php echo get2post(site_url("admin/tokens/email/$surveyid/tids")."?tokenids=document.getElementById('tokenboxeschecked').value");?>" />
            &nbsp;
            <input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_remind.png' title='<?php
            echo $clang->gT("Send reminder email to the selected entries (if they have already received the invitation email)");
            ?>' alt='<?php
            echo $clang->gT("Send reminder email to the selected entries (if they have already received the invitation email)");
            ?>' onclick="<?php echo get2post(site_url("admin/tokens/remind/$surveyid/tids")."?tokenids=document.getElementById('tokenboxeschecked').value");?>" />
        <?php } ?>
        <input type='hidden' id='tokenboxeschecked' value='' onchange='alert(this.value)' />
	    </td>
	    </tr>
	<?php }
	//End multiple item actions
?>
	</table><br />
<script type="text/javascript">
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