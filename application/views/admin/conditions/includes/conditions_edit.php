
	<a href='#' onclick="if ( confirm('<?php echo $clang->gT("Are you sure you want to delete this condition?","js"); ?>')) { $('#editModeTargetVal<?php echo $rows['cid']; ?>').remove(); $('#cquestions<?php echo $rows['cid']; ?>').remove(); document.getElementById('conditionaction<?php echo $rows['cid']; ?>').submit();}"
		title='<?php echo $clang->gTview("Delete this condition"); ?>'>
		<img src='$imageurl/conditions_delete_16.png'  alt='<?php echo $clang->gT("Delete this condition"); ?>' name='DeleteThisCondition'
			title='' />
	</a>
	<a href='#' onclick='document.getElementById("subaction<?php echo $rows['cid']; ?>").value="editthiscondition"; document.getElementById(
		"conditionaction<?php echo $rows['cid']; ?>").submit();'>
		<img src='$imageurl/conditions_edit_16.png'  alt='<?php echo $clang->gT("Edit this condition"); ?>' name='EditThisCondition' /></a>
		<input type='hidden' name='subaction' id='subaction<?php echo $rows['cid']; ?>' value='delete' />
		<input type='hidden' name='cid' value='<?php echo $rows['cid']; ?>' />
		<input type='hidden' name='scenario' value='<?php echo $rows['scenario']; ?>' />
		<!-- <input type='hidden' id='cquestions{$rows['cid']}'  name='cquestions' value='{$rows['cfieldname']}' /> -->
		<input type='hidden' name='method' value='<?php echo $rows['method']; ?>' />
		<input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
		<input type='hidden' name='gid' value='<?php echo $gid; ?>' />
		<input type='hidden' name='qid' value='<?php echo $qid; ?>' />
