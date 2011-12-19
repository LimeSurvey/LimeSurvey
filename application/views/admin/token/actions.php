<!--<?php
	if (bHasSurveyPermission($surveyid, 'tokens','delete'))
	{ 
?>
		<a href="#" alt="<?php $clang->eT("Delete the selected entries");?>" 
		   	onclick="if (confirm('<?php $clang->eT("Are you sure you want to delete the selected entries?","js");?>')) { <?php echo get2post($this->createUrl("admin/tokens/delete/$surveyid/")."?action=tokens&amp;sid={$surveyid}&amp;subaction=delete&amp;tokenids=$id&amp;limit={$limit}&amp;start={$start}&amp;order={$order}");?>}" title="<?php $clang->eT("Delete the selected entries");?>">
		   <img name='DeleteTokenBtn' align='left' src='<?php echo $imageurl;?>/token_delete.png' alt='<?php $clang->eT("Delete the selected entries");?>' /></a>
	   	</a>
	   	
		<img src='<?php echo $imageurl;?>/blank.gif' height='16' width='16' alt='' />
		<input style='height: 16; width: 16px; font-size: 8; font-family: verdana; display: inline;' type='image' src='<?php echo $imageurl;?>/token_delete.png'
		title='<?php $clang->eT("Delete the selected entries");?>' alt='<?php $clang->eT("Delete the selected entries");?>'
		onclick="if (confirm('<?php $clang->eT("Are you sure you want to delete the selected entries?","js");?>')) { <?php echo get2post($this->createUrl("admin/tokens/delete/$surveyid/")."?action=tokens&amp;sid={$surveyid}&amp;subaction=delete&amp;tokenids=$id&amp;limit={$limit}&amp;start={$start}&amp;order={$order}");?>}"  />
	<?php }
	
	if (bHasSurveyPermission($surveyid, 'tokens','update'))
	{ ?>
		<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_invite.png'
		title='<?php $clang->eT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)");?>'
		alt='<?php $clang->eT("Send invitation emails to the selected entries (if they have not yet been sent an invitation email)");?>'
		onclick="<?php echo get2post($this->createUrl("admin/tokens/sa/email/surveyid/$surveyid/tids")."?tokenids=$id");?>" />
		<input style='height: 16; width: 16px; font-size: 8; font-family: verdana' type='image' src='<?php echo $imageurl;?>/token_remind.png'
		title='<?php $clang->eT("Send reminder email to the selected entries (if they have already received the invitation email)");?>'
		alt='<?php $clang->eT("Send reminder email to the selected entries (if they have already received the invitation email)");?>'
		onclick="<?php echo get2post($this->createUrl("admin/tokens/sa/remind/surveyid/$surveyid/tids")."?tokenids=$id");?>" />
<?php } ?> -->

<a class="ui-icon ui-icon-pencil" onclick="if (confirm('<?php $clang->eT("Are you sure you want to delete the selected entries?","js");?>')) { <?php echo get2post($this->createUrl("admin/tokens/delete/$surveyid/")."?action=tokens&amp;sid={$surveyid}&amp;subaction=delete&amp;tokenids=$id&amp;limit={$limit}&amp;start={$start}&amp;order={$order}");?>}" title="<?php $clang->eT("Delete the selected entries");?>"></a>
<a class="ui-icon"></a>
