<div class='header ui-widget-header'>
	<?php echo $clang->gT("Token summary");?>
</div>
<br />
<table align='center' class='statisticssummary'>
	<tr>
		<th>
		    <?php echo $clang->gT("Total records in this token table");?>
		</th>
		<td>
			<?php echo $queries['tkcount']; ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php echo $clang->gT("Total with no unique Token");?>
		</th>
		<td>
			<?php echo $queries['query1']; ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php echo $clang->gT("Total invitations sent");?>
		</th>
		<td>
			<?php echo $queries['query2']; ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php echo $clang->gT("Total opted out");?>
		</th>
		<td>
			<?php echo $queries['query3']; ?>
		</td>
	</tr>
	<tr>
		<th>
			<?php echo $clang->gT("Total surveys completed");?>
		</th>
		<td>
			<?php echo $queries['query4']; ?>
		</td>
	</tr>
</table>
<br />
<script language='javascript' type='text/javascript'>
	surveyid = '<?php echo $surveyid;?>'
</script>
<?php if (bHasSurveyPermission($surveyid, 'tokens', 'update') || bHasSurveyPermission($surveyid, 'tokens', 'delete')) { ?>
    <div class='header ui-widget-header'><?php echo $clang->gT("Token database administration options");?></div>
    	<div style='width:30%; margin:0 auto;'>
    		<ul>
			    <?php if (bHasSurveyPermission($surveyid, 'tokens', 'update')) { ?>
			        <li><a href='#' onclick="if( confirm('<?php echo $clang->gT("Are you really sure you want to reset all invitation records to NO?","js");?>')) { <?php echo get2post(Yii::app()->baseUrl."?action=tokens&amp;sid=$surveyid&amp;subaction=clearinvites");?>}">
			        	<?php echo $clang->gT("Set all entries to 'No invitation sent'.");?></a></li>
			        <li><a href='#' onclick="if ( confirm('<?php echo $clang->gT("Are you sure you want to delete all unique token strings?","js");?>')) { <?php echo get2post(Yii::app()->baseUrl."?action=tokens&amp;sid=$surveyid&amp;subaction=cleartokens");?>}">
			        	<?php echo $clang->gT("Delete all unique token strings");?></a></li>
				<?php }
			    if (bHasSurveyPermission($surveyid, 'tokens', 'delete')) { ?>
			        <li><a href='#' onclick=" if (confirm('<?php echo $clang->gT("Are you really sure you want to delete ALL token entries?","js");?>')) { <?php echo get2post(Yii::app()->baseUrl."?action=tokens&amp;sid=$surveyid&amp;subaction=deleteall");?>}">
			        	<?php echo $clang->gT("Delete all token entries");?></a></li>
				<?php } ?>
    		</ul>
    	</div>
<?php } ?>