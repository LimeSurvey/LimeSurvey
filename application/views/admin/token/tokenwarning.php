<div class='menubar'>
	<div class='menubar-title ui-widget-header'>
		<strong><?php echo $clang->gT("Token control");?> </strong> <?php echo htmlspecialchars($thissurvey['surveyls_title']);?>
 </div></div><div class='messagebox ui-corner-all'>
        <div class='warningheader'><?php echo $clang->gT("Warning");?></div>
        <br /><strong><?php echo $clang->gT("Tokens have not been initialised for this survey.");?></strong><br /><br />
        <?php if (bHasSurveyPermission($surveyid, 'surveyactivation','update'))
        {
            echo $clang->gT("If you initialise tokens for this survey then this survey will only be accessible to users who provide a token either manually or by URL.");
            ?><br /><br />

            <?php if ($thissurvey['anonymized'] == 'Y')
            {
                echo $clang->gT("Note: If you turn on the -Anonymized responses- option for this survey then LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.");
                ?><br /><br />
            <?php }
			echo $clang->gT("Do you want to create a token table for this survey?");
			?>
            <br /><br />
            <input type='submit' value='<?php echo $clang->gT("Initialise tokens");?>' onclick="<?php echo get2post($this->createUrl("admin/tokens/sa/index/surveyid/$surveyid")."?action=tokens&amp;sid=$surveyid&amp;createtable=Y");?>" />
            <input type='submit' value='<?php echo $clang->gT("No, thanks.");?>' onclick="window.open('<?php echo$this->createUrl("admin/survey/sa/view/surveyid/$surveyid");?>', '_top')" /></div>
        <?php }
        else
        {
            echo $clang->gT("You don't have the permission to activate tokens.");?>
            <input type='submit' value='<?php echo $clang->gT("Back to main menu");?>' onclick="window.open('<?php echo $this->createUrl("admin/survey/view/surveyid/$surveyid");?>', '_top')" /></div>

        <?php }

        // Do not offer old postgres token tables for restore since these are having an issue with missing index
        if ($tcount>0 && $databasetype!='postgre' && bHasSurveyPermission($surveyid, 'surveyactivation','update'))
        { ?>
            <br /><div class='header ui-widget-header'><?php echo $clang->gT("Restore options");?></div>
            <div class='messagebox ui-corner-all'>
            <form method='post' action='<?php echo site_url("admin/tokens/index/$surveyid");?>'>
            <?php echo $clang->gT("The following old token tables could be restored:");?><br /><br />
            <select size='4' name='oldtable' style='width:250px;'>
            <?php foreach($oldlist as $ol)
            {
                echo "<option>".$ol."</option>\n";
            } ?>
            </select><br /><br />
            <input type='submit' value='<?php echo $clang->gT("Restore");?>' />
            <input type='hidden' name='restoretable' value='Y' />
            <input type='hidden' name='sid' value='$surveyid' />
            </form></div>
        <?php } ?>
<script type="text/javascript">
<!--
	for(i=0; i<document.forms.length; i++)
	{
var el = document.createElement('input');
el.type = 'hidden';
el.name = 'checksessionbypost';
el.value = 'cvtp4rts86';
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