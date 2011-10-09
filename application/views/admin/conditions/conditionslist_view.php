<tr><td>
<?php echo $conditionsoutput;?>

<table width='100%' align='center' cellspacing='0' cellpadding='0'>
<tr bgcolor='#E1FFE1'>
<td><table align='center' width='100%' cellspacing='0'><tr>
		    	
<?php if ($subaction== "editconditionsform" || $subaction=='insertcondition' ||
$subaction == "editthiscondition" || $subaction == "delete" ||
$subaction == "updatecondition" || $subaction == "deletescenario" ||
$subaction == "updatescenario" ||
$subaction == "renumberscenarios")  { ?>
    <td align='center' width='90%'><strong><?php echo $onlyshow;?></strong>
    </td>
    <td width='10%' align='right' valign='middle'><form id='deleteallconditions' action='<?php echo site_url("/admin/conditions/deleteallconditions/$surveyid/$gid/$qid/");?>' method='post' name='deleteallconditions' style='margin-bottom:0;'>
    <input type='hidden' name='qid' value='<?php echo $qid;?>' />
    <input type='hidden' name='gid' value='<?php echo $gid;?>' />
    <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
    <input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />

    <?php if ($scenariocount > 0)
    { // show the Delete all conditions for this question button ?>
        <a href='#' onclick="if ( confirm('<?php echo $clang->gT("Are you sure you want to delete all conditions set to the questions you have selected?","js");?>')) { document.getElementById('deleteallconditions').submit();}" title='<?php echo $clang->gTview("Delete all conditions");?>' >
        <img src='<?php echo $imageurl;?>/conditions_deleteall_16.png'  alt='<?php echo $clang->gT("Delete all conditions");?>' name='DeleteAllConditionsImage' /></a>
    <?php }
    if ($scenariocount > 1)
    { // show the renumber scenario button for this question ?>
        <a href='#' onclick="if ( confirm('<?php echo $clang->gT("Are you sure you want to renumber the scenarios with incremented numbers beginning from 1?","js");?>')) { document.getElementById('toplevelsubaction').value='renumberscenarios'; document.getElementById('deleteallconditions').submit();}" title='<?php echo $clang->gTview("Renumber scenario automatically");?>' >
        <img src='$imageurl/scenario_renumber.png'  alt='<?php echo $clang->gT("Renumber scenario automatically");?>' name='renumberscenarios' /></a>
    <?php }
}
else
{ ?>
    <td align='center'><strong><?php echo $onlyshow;?></strong>
    <form id='deleteallconditions' action='<?php echo site_url("/admin/conditions/deleteallconditions/$surveyid/$gid/$qid/");?>' method='post' name='deleteallconditions' style='margin-bottom:0;'>
    <input type='hidden' name='qid' value='<?php echo $qid;?>' />
    <input type='hidden' name='sid' value='<?php echo $surveyid;?>' />
    <input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />
<?php }
?>
</form></td></tr></table>
</td></tr>