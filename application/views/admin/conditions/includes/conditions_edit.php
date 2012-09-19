
<a href='#' onclick="if ( confirm('<?php $clang->eT("Are you sure you want to delete this condition?","js"); ?>')) { $('#editModeTargetVal<?php echo $rows['cid']; ?>').remove(); $('#cquestions<?php echo $rows['cid']; ?>').remove(); document.getElementById('conditionaction<?php echo $rows['cid']; ?>').submit();}">
    <img src='<?php echo $sImageURL;?>conditions_delete_16.png'  alt='<?php $clang->eT("Delete this condition"); ?>'/>
</a>
<a href='#' onclick='document.getElementById("subaction<?php echo $rows['cid']; ?>").value="editthiscondition"; document.getElementById("conditionaction<?php echo $rows['cid']; ?>").submit();'>
    <img src='<?php echo $sImageURL;?>conditions_edit_16.png'  alt='<?php $clang->eT("Edit this condition"); ?>'/></a>
		<input type='hidden' name='subaction' id='subaction<?php echo $rows['cid']; ?>' value='delete' />
		<input type='hidden' name='cid' value='<?php echo $rows['cid']; ?>' />
		<input type='hidden' name='scenario' value='<?php echo $rows['scenario']; ?>' />
		<!-- <input type='hidden' id='cquestions{$rows['cid']}'  name='cquestions' value='{$rows['cfieldname']}' /> -->
		<input type='hidden' name='method' value='<?php echo $rows['method']; ?>' />
		<input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
		<input type='hidden' name='gid' value='<?php echo $gid; ?>' />
		<input type='hidden' name='qid' value='<?php echo $qid; ?>' />
