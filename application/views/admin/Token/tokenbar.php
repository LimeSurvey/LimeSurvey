<div class='menubar'>
	<div class='menubar-title ui-widget-header'>
		<strong><?php echo $clang->gT("Token control");?> </strong> <?php echo htmlspecialchars($thissurvey['surveyls_title']);?>
	</div>
	<div class='menubar-main'>
    	<div class='menubar-left'>
    		<a href="#" onclick="window.open('<?php echo site_url("admin");?>', '_top')" title='<?php echo $clang->gTview("Return to survey administration");?>'>
    			<img name='HomeButton' src='<?php echo $imageurl;?>/home.png' alt='<?php echo $clang->gT("Return to survey administration");?>' />
    		</a>
		    <img src='<?php echo $imageurl;?>/blank.gif' alt='' width='11' />
		    <img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
		    <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/index/$surveyid");?>', '_top')" title='<?php echo $clang->gTview("Show token summary");?>' >
    			<img name='SummaryButton' src='<?php echo $imageurl;?>/summary.png' alt='<?php echo $clang->gT("Show token summary");?>' />
    		</a>
    		<img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
    		<a href="#" onclick="window.open('<?php echo site_url("admin/tokens/browse/$surveyid");?>', '_top')"
    			title='<?php echo $clang->gTview("Display tokens");?>' >
    			<img name='ViewAllButton' src='<?php echo $imageurl;?>/document.png' alt='<?php echo $clang->gT("Display tokens");?>' />
    		</a>
		    <?php if (bHasSurveyPermission($surveyid, 'tokens','create')) { ?>
		        <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/addnew/$surveyid");?>', '_top')"
		        	title='<?php echo $clang->gTview("Add new token entry");?>' >
		        	<img name='AddNewButton' src='<?php echo $imageurl;?>/add.png' title='' alt='<?php echo $clang->gT("Add new token entry");?>' />
		        </a>
		        <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/adddummys/$surveyid");?>', '_top')"
		        	title='<?php echo $clang->gTview("Add dummy tokens");?>' >
		        	<img name='AddNewDummyButton' src='<?php echo $imageurl;?>/create_dummy_token.png' title='' alt='<?php echo $clang->gT("Add dummy tokens");?>' />
		        </a>
		
		    <?php }
		    if (bHasSurveyPermission($surveyid, 'tokens','update'))
		    { ?>
		        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
		        <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/managetokenattributes/$surveyid");?>', '_top')"
		        	title='<?php echo $clang->gTview("Manage additional attribute fields");?>'>
		        	<img name='ManageAttributesButton' src='<?php echo $imageurl;?>/token_manage.png' title='' alt='<?php echo $clang->gT("Manage additional attribute fields");?>' />
		        </a>
		    <?php }
		    if (bHasSurveyPermission($surveyid, 'tokens','import'))
		    { ?>
		        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
		        <a href="#" onclick="window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=import', '_top')"
		        	title='<?php echo $clang->gTview("Import tokens from CSV file");?>'>
		        	<img name='ImportButton' src='<?php echo $imageurl;?>/importcsv.png' title='' alt='<?php echo $clang->gT("Import tokens from CSV file");?>' />
		        </a>
		        <a href="#" onclick="window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=importldap', '_top')"
		        	title='<?php echo $clang->gTview("Import tokens from LDAP query");?>'>
		        	<img name='ImportLdapButton' src='<?php echo $imageurl;?>/importldap.png' alt='<?php echo $clang->gT("Import tokens from LDAP query");?>' />
		        	</a>
		    <?php }
		    if (bHasSurveyPermission($surveyid, 'tokens','export'))
		    { ?>
		        <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/exportdialog/$surveyid");?>', '_top')"
		        	title='<?php echo $clang->gTview("Export tokens to CSV file");?>'>
			    	<img name='ExportButton' src='<?php echo $imageurl;?>/exportcsv.png' alt='<?php echo $clang->gT("Export tokens to CSV file");?>' />
			    </a>
		    <?php }
		    if (bHasSurveyPermission($surveyid, 'tokens','update'))
		    { ?>
		        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
		        <a href='<?php echo site_url("admin/emailtemplates/index/$surveyid");?>' title='<?php echo $clang->gTview("Edit email templates");?>'>
		        	<img name='EmailTemplatesButton' src='<?php echo $imageurl;?>/emailtemplates.png' alt='<?php echo $clang->gT("Edit email templates");?>' />
		        </a>
		        <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/email/$surveyid");?>', '_top')" 
		        	title='<?php echo $clang->gTview("Send email invitation");?>'>
		        	<img name='InviteButton' src='<?php echo $imageurl;?>/invite.png' alt='<?php echo $clang->gT("Send email invitation");?>' />
		        </a>
		        <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/remind/$surveyid");?>', '_top')" 
		        	title='<?php echo $clang->gTview("Send email reminder");?>'>
		        	<img name='RemindButton' src='<?php echo $imageurl;?>/remind.png' alt='<?php echo $clang->gT("Send email reminder");?>' />
		        </a>
		        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
		        <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/tokenify/$surveyid");?>', '_top')"
		        	title='<?php echo $clang->gTview("Generate tokens");?>'>
		        	<img name='TokenifyButton' src='<?php echo $imageurl;?>/tokenify.png' alt='<?php echo $clang->gT("Generate tokens");?>' />
		        </a>
		        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
		    <?php }
		    if (bHasSurveyPermission($surveyid, 'surveyactivation','update'))
		    { ?>
		        <a href="#" onclick="window.open('<?php echo site_url("admin/tokens/kill/$surveyid");?>', '_top')" 
		        	title='<?php echo $clang->gTview("Drop tokens table");?>' >
		        	<img name='DeleteTokensButton' src='<?php echo $imageurl;?>/delete.png' alt='<?php echo $clang->gT("Drop tokens table");?>' />
		        </a>
		        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
		    <?php }
		    if (bHasSurveyPermission($surveyid, 'tokens','update'))
		    { ?>
		        <a href="#" onclick="window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=bouncesettings', '_top')" 
		        	title='<?php echo $clang->gTview("Bounce processing settings");?>' >
		        	<img name='BounceSettings' src='<?php echo $imageurl;?>/bounce_settings.png' alt='<?php echo $clang->gT("Bounce settings");?>' />
		        </a>
		    <?php } ?>
                        <img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
                        <?php $participantpanel = array(
                        'src' => 'images/tokens.png',
                        'alt' => 'Central Participant Panel',
                        'title' => 'Central Participant Panel',
                          );
                        echo anchor('admin/participants/index',img($participantpanel));
                        ?>
    	</div>
    	<div class='menubar-right'><a href="#" onclick="showhelp('show')" title='<?php echo $clang->gTview("Show help");?>'>
		    <img src='<?php echo $imageurl;?>/showhelp.png' align='right' alt='<?php echo $clang->gT("Show help");?>' /></a>
		</div>
	</div>
</div>
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