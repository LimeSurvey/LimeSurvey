
<a
    class="btn btn-default"
    data-target="#confirmation-modal"
    data-toggle="tooltip"
    data-title="<?php eT("Delete condition"); ?>"
    data-message="<?php eT("Are you sure you want to delete this condition?"); ?>"
    onclick="$.bsconfirm('<?php eT("Are you sure you want to delete this condition?"); ?>', {'confirm_ok': '<?=gT("Yes")?>', 'confirm_cancel': '<?=gT("No")?>'}, function() {
            $('#editModeTargetVal<?php echo $rows['cid']; ?>').remove();
            $('#cquestions<?php echo $rows['cid']; ?>').remove();
            document.getElementById('conditionaction<?php echo $rows['cid']; ?>').submit();
        });"
    >
    <span class="fa fa-trash text-danger"></span>
</a>

<a
    class="btn btn-default"
    data-toggle="tooltip"
    data-title="<?php eT("Edit condition"); ?>"
    onclick='document.getElementById("subaction<?php echo $rows['cid']; ?>").value="editthiscondition"; document.getElementById("conditionaction<?php echo $rows['cid']; ?>").submit();'
>
    <span class="fa fa-pencil"></span>
</a>

<input type='hidden' name='subaction' id='subaction<?php echo $rows['cid']; ?>' value='delete' />
<input type='hidden' name='cid' value='<?php echo $rows['cid']; ?>' />
<input type='hidden' name='scenario' value='<?php echo $rows['scenario']; ?>' />
<!-- <input type='hidden' id='cquestions{$rows['cid']}'  name='cquestions' value='{$rows['cfieldname']}' /> -->
<input type='hidden' name='method' value='<?php echo $rows['method']; ?>' />
<input type='hidden' name='sid' value='<?php echo $surveyid; ?>' />
<input type='hidden' name='gid' value='<?php echo $gid; ?>' />
<input type='hidden' name='qid' value='<?php echo $qid; ?>' />
