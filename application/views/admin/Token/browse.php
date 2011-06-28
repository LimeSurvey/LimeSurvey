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
    
    <a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=0&amp;limit=$limit&amp;order=$order&amp;searchstring=<?php echo urlencode($searchstring);?>'
    	title='<?php echo $clang->gTview("Show start...");?>'>
    <img name='DBeginButton' align='left' src='<?php echo $imageurl;?>/databegin.png' alt='<?php echo $clang->gT("Show start...");?>' /></a>
    <a href='$scriptname?action=toknens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit&amp;order=$order&amp;searchstring=<?php echo urlencode($searchstring);?>'
	 title='<?php echo $clang->gTview("Show previous...");?>'>
	<img name='DBackButton' align='left' src='<?php echo $imageurl;?>/databack.png' alt='<?php echo $clang->gT("Show previous...");?>' /></a>
	<img src='<?php echo $imageurl;?>/blank.gif' alt='' width='13' height='20' border='0' hspace='0' align='left' />
	<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit&amp;order=$order&amp;searchstring=<?php echo urlencode($searchstring);?>'
	title='<?php echo $clang->gTview("Show next...");?>'>
	<img name='DForwardButton' align='left' src='<?php echo $imageurl;?>/dataforward.png' alt='<?php echo $clang->gT("Show next...");?>' /></a>
	<a href='$scriptname?action=tokens&amp;subaction=browse&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit&amp;order=$order&amp;searchstring=<?php echo urlencode($searchstring);?>'
	title='<?php echo $clang->gTview("Show last...");?>'>
	<img name='DEndButton' align='left'  src='<?php echo $imageurl;?>/dataend.png' alt='<?php echo $clang->gT("Show last...");?>' /></a>
	<img src='<?php echo $imageurl;?>/seperator.gif' alt='' border='0' hspace='0' align='left' />
	
<?php 
/*
$tokenoutput .="\t<form id='tokensearch' method='post' action='$scriptname?action=tokens'>\n"
	."<input type='text' name='searchstring' value='".htmlspecialchars($searchstring,ENT_QUOTES,'utf-8')."' />\n"
	."<input type='submit' value='".$clang->gT("Search")."' />\n"
	."\t<input type='hidden' name='order' value='$order' />\n"
	."\t<input type='hidden' name='subaction' value='search' />\n"
	."\t<input type='hidden' name='sid' value='$surveyid' />\n"
	."\t</form>\n"
	."<form id='tokenrange' action='{$scriptname}'>\n"
	."<img src='$imageurl/seperator.gif' alt='' border='0' />\n"
	."<font size='1' face='verdana'>"
	."&nbsp;<label for='limit'>".$clang->gT("Records displayed:")."</label> <input type='text' size='4' value='$limit' id='limit' name='limit' />"
	."&nbsp;&nbsp;<label for='start'>".$clang->gT("Starting from:")."</label> <input type='text' size='4' value='$start'  id='start' name='start' />"
	."&nbsp;<input type='submit' value='".$clang->gT("Show")."' />\n"
	."</font>\n"
	."<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='action' value='tokens' />\n"
	."<input type='hidden' name='subaction' value='browse' />\n"
	."<input type='hidden' name='order' value='$order' />\n"
	."<input type='hidden' name='searchstring' value='".htmlspecialchars($searchstring,ENT_QUOTES,'utf-8')."' />\n"
	."</form>\n";
	$bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
	if ($searchstring)
	{
        $sSearch=db_quote($searchstring);
	    $bquery .= " WHERE firstname LIKE '%{$sSearch}%' "
	    . "OR lastname LIKE '%{$sSearch}%' "
	    . "OR email LIKE '%{$sSearch}%' "
	    . "OR emailstatus LIKE '%{$sSearch}%' "
	    . "OR token LIKE '%{$sSearch}%'";
	}
	if (!isset($order) || !$order) {$bquery .= " ORDER BY tid";}
	else {$bquery .= " ORDER BY $order"; }

	$bresult = db_select_limit_assoc($bquery, $limit, $start) or safe_die ($clang->gT("Error").": $bquery<br />".$connect->ErrorMsg());
	$bgc="";

	$tokenoutput .= "</div></div></div>\n";

	$tokenoutput .= "<table class='browsetokens' id='browsetokens' cellpadding='1' cellspacing='1'>\n";
	//COLUMN HEADINGS
	$tokenoutput .= "\t<tr>\n"
	."<th><input type='checkbox' id='tokencheckboxtoggle' /></th>\n"   //Checkbox

	."<th align='left' >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=tid&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	."ID' alt='"
	.$clang->gT("Sort by: ")
	."ID' border='0' align='left' hspace='0' /></a>"."ID</th>\n" // ID

	."<th align='left'  >".$clang->gT("Actions")."</th>\n"  //Actions
	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=firstname&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("First name")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("First name")
	."' border='0' align='left' /></a>".$clang->gT("First name")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=lastname&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Last name")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Last name")
	."' border='0' align='left' /></a>".$clang->gT("Last name")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=email&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Email address")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Email address")
	."' border='0' align='left' /></a>".$clang->gT("Email address")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=emailstatus%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Email status")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Email status")
	."' border='0' align='left' /></a>".$clang->gT("Email status")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=token&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Token")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Token")
	."' border='0' align='left' /></a>".$clang->gT("Token")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=language&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Language")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Language")
	."' border='0' align='left' /></a>".$clang->gT("Language")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=sent%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Invitation sent?")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Invitation sent?")
	."' border='0' align='left' /></a>".$clang->gT("Invitation sent?")."</th>\n"


	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=remindersent%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Reminder sent?")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Reminder sent?")
	."' border='0' align='left' /></a><span>".$clang->gT("Reminder sent?")."</span></th>\n"

	."<th align='left'>"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=remindercount%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Reminder count")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Reminder count")
	."' border='0' align='left' /></a><span>".$clang->gT("Reminder count")."</span></th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=completed%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Completed?")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Completed?")
	."' border='0' align='left' /></a>".$clang->gT("Completed?")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=usesleft%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Uses left")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Uses left")
	."' border='0' align='left' /></a><span>".$clang->gT("Uses left")."</span></th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=validfrom%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid from")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid from")
	."' border='0' align='left' /></a>".$clang->gT("Valid from")."</th>\n"

	."<th align='left'  >"
	."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=validuntil%20desc&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	."<img src='$imageurl/downarrow.png' title='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid until")
	."' alt='"
	.$clang->gT("Sort by: ")
	.$clang->gT("Valid until")
	."' border='0' align='left' /></a>".$clang->gT("Valid until")."</th>\n";

	$attrfieldnames=GetTokenFieldsAndNames($surveyid,true);
	foreach ($attrfieldnames as $attr_name=>$attr_translation)
	{
	    $tokenoutput .= "<th align='left' >"
	    ."<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=browse&amp;order=$attr_name&amp;start=$start&amp;limit=$limit&amp;searchstring=".urlencode($searchstring)."'>"
	    ."<img src='$imageurl/downarrow.png' alt='' title='"
	    .$clang->gT("Sort by: ").htmlspecialchars($attr_translation,ENT_QUOTES,'utf-8')."' border='0' align='left' /></a>".htmlspecialchars($attr_translation,ENT_QUOTES,'utf-8')."</th>\n";
	}
	$tokenoutput .="\t</tr>\n";

	$tokenfieldorder=array('tid',
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

	while ($brow = $bresult->FetchRow())
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
	    $tokenoutput .= "\t<tr class='$bgc'>\n";

	    $tokenoutput .= "<td><input type='checkbox' name='".$brow['tid']."' /></td>\n";

	    foreach ($tokenfieldorder as $tokenfieldname)
	    {

	        if ($tokenfieldname =='email' && $brow['emailstatus'] != 'OK')
	        {
	            if ($brow['emailstatus']!='OptOut')
	            {
	                $tokenoutput .= "<td>"
	                ."<a href=\"#\" class='invalidemail' title='".$clang->gT('Invalid email address:').htmlspecialchars($brow['emailstatus'])."' >"
	                ."$brow[$tokenfieldname]</a></td>\n";
	            }
	            else
	            {
	                $tokenoutput .= "<td>"
	                ."<a href=\"#\" class='optoutemail' title='".$clang->gT('This participant opted out of this survey.')."' >"
	                ."$brow[$tokenfieldname]</a></td>\n";
	            }
	        }

//	        elseif ($tokenfieldname != 'emailstatus')
          else
	        {
	            if  ($tokenfieldname=='tid')
	            {
	                $tokenoutput.="<td><span style='font-weight:bold'>".$brow[$tokenfieldname]."</span></td>";
	            }
	            else
	            {
	                $tokenoutput .= '<td>'.htmlspecialchars($brow[$tokenfieldname])."</td>\n";
	            }
	        }
	        if ($tokenfieldname=='tid')
	        {
	            $tokenoutput .= "<td align='left' style='white-space:nowrap;'>\n";
	            if (bHasSurveyPermission($surveyid, 'tokens','update'))
	            {
	                if (($brow['completed'] == "N" || $brow['completed'] == "") &&$brow['token'])
	                {
	                    $toklang = ($brow['language'] == '') ? $baselanguage : $brow['language'];
	                    $tokenoutput .= "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='$imageurl/do_16.png' title='"
	                    .$clang->gT("Do Survey")
	                    ."' alt='"
	                    .$clang->gT("Do Survey")
	                    ."' onclick=\"window.open('{$publicurl}/index.php?sid={$surveyid}&amp;lang={$toklang}&amp;token=".trim($brow['token'])."', '_blank')\" />\n";
	                }
	                else
	                {
	                    $tokenoutput .= "<img src='{$imageurl}/blank.gif' height='16' alt='' width='16'/>";
	                }
	                $tokenoutput .="<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_edit.png' title='"
	                .$clang->gT("Edit token entry")
	                ."' alt='"
	                .$clang->gT("Edit token entry")
	                ."' onclick=\"window.open('{$scriptname}?action=tokens&amp;sid={$surveyid}&amp;subaction=edit&amp;tid=".$brow['tid']."&amp;start={$start}&amp;limit={$limit}&amp;order={$order}', '_top')\" /> ";
	            }
                if (bHasSurveyPermission($surveyid, 'tokens','delete'))
                {
                    $tokenoutput .="<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_delete.png' title='"
                    .$clang->gT("Delete token entry")
                    ."' alt='"
                    .$clang->gT("Delete token entry")
                    ."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")." (".$brow['tid'].")')) {".get2post("$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=delete&amp;tid=".$brow['tid']."&amp;limit=$limit&amp;start=$start&amp;order=$order")."}\"  />";
                }
                
	            if ($brow['completed'] != "N" && $brow['completed']!="" && $surveyprivate == "N"  && $thissurvey['active']=='Y')
	            {
	                // Get response Id
	                $query="SELECT id FROM ".db_table_name('survey_'.$surveyid)." WHERE token='{$brow['token']}' ORDER BY id desc";
	                $result=db_execute_num($query) or safe_die ("<br />Could not find token!<br />\n" .$connect->ErrorMsg());
	                list($id) = $result->FetchRow();

	                // UPDATE button to the tokens display in the MPID Actions column
	                if  ($id)
	                {
	                    $tokenoutput .= "<input type='image' src='{$imageurl}/token_viewanswer.png' style='height: 16; width: 16px;' onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id=$id', '_top')\" title='"
	                    .$clang->gT("View/Update last response")
	                    ."' alt='"
	                    .$clang->gT("View/Update last response")
	                    ."' />\n";
	                }
	            }
	            elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] == "N" && trim($brow['email'])!='' && bHasSurveyPermission($surveyid, 'tokens','update'))
	            {
	                $tokenoutput .= "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_invite.png' title='"
	                .$clang->gT("Send invitation email to this entry")
	                ."' alt='"
	                .$clang->gT("Send invitation email to this entry")
	                ."' onclick=\"window.open('{$scriptname}?action=tokens&amp;sid={$surveyid}&amp;subaction=email&amp;tid=".$brow['tid']."', '_top')\" />";
	            }
	            elseif ($brow['completed'] == "N" && $brow['token'] && $brow['sent'] != "N" && trim($brow['email'])!='')  // reminder button
	            {
	                $tokenoutput .= "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_remind.png' title='"
	                .$clang->gT("Send reminder email to this entry")
	                ."' alt='"
	                .$clang->gT("Send reminder email to this entry")
	                ."' onclick=\"window.open('{$scriptname}?sid={$surveyid}&amp;action=tokens&amp;subaction=remind&amp;tid={$brow['tid']}', '_top')\" />";
	            }
  	            $tokenoutput .= "\n</td>\n";
	        }
	    }
	    $tokenoutput .= "\t</tr>\n";
	}

	// Multiple item actions
	if ($bresult->rowCount() > 0) {
	    $tokenoutput .= "<tr class='{$bgc}'>\n"
	    . "<td align='left' style='text-align: left' colspan='".(count($tokenfieldorder)+1)."'>";
        
        if (bHasSurveyPermission($surveyid, 'tokens','delete'))
        {
            $tokenoutput .= "<img src='{$imageurl}/blank.gif' height='16' width='16' alt='' />"
            . "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_delete.png' title='"
            .$clang->gT("Delete the selected entries")
            ."' alt='"
            .$clang->gT("Delete the selected entries")
            ."' onclick=\"if($('#tokenboxeschecked').val()){if (confirm('"
            .$clang->gT("Are you sure you want to delete the selected entries?","js")
            ."')) {".get2post("{$scriptname}?action=tokens&amp;sid={$surveyid}&amp;subaction=delete&amp;tids=document.getElementById('tokenboxeschecked').value&amp;limit={$limit}&amp;start={$start}&amp;order={$order}")."}}else{alert('".$clang->gT("No tokens selected")."');}\"  />";
            
        }
        
        if (bHasSurveyPermission($surveyid, 'tokens','update'))
        {
            $tokenoutput .= "&nbsp;"
            . "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_invite.png' title='"
            .$clang->gT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)")
            ."' alt='"
            .$clang->gT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)")
            ."' onclick=\"window.open('{$scriptname}?action=tokens&amp;sid={$surveyid}&amp;subaction=email&amp;tids='+document.getElementById('tokenboxeschecked').value, '_top')\" />"
            . "&nbsp;"
            . "<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='{$imageurl}/token_remind.png' title='"
            .$clang->gT("Send reminder email to the selected entries (if they have already received the invitation email)")
            ."' alt='"
            .$clang->gT("Send reminder email to the selected entries (if they have already received the invitation email)")
            ."' onclick=\"window.open('{$scriptname}?sid={$surveyid}&amp;action=tokens&amp;subaction=remind&amp;tids='+document.getElementById('tokenboxeschecked').value, '_top')\" />";
        }
        $tokenoutput .= "<input type='hidden' id='tokenboxeschecked' value='' onchange='alert(this.value)' />\n";
	    $tokenoutput .= "</td>\n"
	    . "</tr>\n";
	}
	//End multiple item actions

	$tokenoutput .= "</table>\n<br />\n";

		
		
		
		
	*/	
		
		
		?>