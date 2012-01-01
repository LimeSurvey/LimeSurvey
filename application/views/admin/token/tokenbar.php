<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Token control"); ?> </strong> <?php echo htmlspecialchars($thissurvey['surveyls_title']); ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>" title='<?php $clang->eTview("Return to survey administration"); ?>'>
                <img name='HomeButton' src='<?php echo $imageurl; ?>/home.png' alt='<?php $clang->eT("Return to survey administration"); ?>' />
            </a>
            <img src='<?php echo $imageurl; ?>/blank.gif' alt='' width='11' />
            <img src='<?php echo $imageurl; ?>/seperator.gif' alt='' />
            <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/$surveyid"); ?>', '_top')" title='<?php $clang->eTview("Show token summary"); ?>' >
                <img name='SummaryButton' src='<?php echo $imageurl; ?>/summary.png' alt='<?php $clang->eT("Show token summary"); ?>' />
            </a>
            <img src='<?php echo $imageurl; ?>/seperator.gif' alt='' />
            <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/sa/index/surveyid/$surveyid"); ?>', '_top')"
               title='<?php $clang->eTview("Display tokens"); ?>' >
                <img name='ViewAllButton' src='<?php echo $imageurl; ?>/document.png' alt='<?php $clang->eT("Display tokens"); ?>' />
            </a>
            <?php if (bHasSurveyPermission($surveyid, 'tokens', 'create'))
            { ?>
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Add new token entry"); ?>' >
                    <img name='AddNewButton' src='<?php echo $imageurl; ?>/add.png' title='' alt='<?php $clang->eT("Add new token entry"); ?>' />
                </a>
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/adddummies/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Add dummy tokens"); ?>' >
                    <img name='AddNewDummyButton' src='<?php echo $imageurl; ?>/create_dummy_token.png' title='' alt='<?php $clang->eT("Add dummy tokens"); ?>' />
                </a>

            <?php
            }
            if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
            {
                ?>
                <img src='<?php echo $imageurl; ?>/seperator.gif' alt='' />
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Manage additional attribute fields"); ?>'>
                    <img name='ManageAttributesButton' src='<?php echo $imageurl; ?>/token_manage.png' title='' alt='<?php $clang->eT("Manage additional attribute fields"); ?>' />
                </a>
<?php
}
if (bHasSurveyPermission($surveyid, 'tokens', 'import'))
{
    ?>
                <img src='<?php echo $imageurl; ?>/seperator.gif' alt='' />
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/import/surveyid/$surveyid") ?>', '_top')"
                   title='<?php $clang->eTview("Import tokens from CSV file"); ?>'>
                    <img name='ImportButton' src='<?php echo $imageurl; ?>/importcsv.png' title='' alt='<?php $clang->eT("Import tokens from CSV file"); ?>' />
                </a>
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/importldap/surveyid/$surveyid") ?>', '_top')"
                   title='<?php $clang->eTview("Import tokens from LDAP query"); ?>'>
                    <img name='ImportLdapButton' src='<?php echo $imageurl; ?>/importldap.png' alt='<?php $clang->eT("Import tokens from LDAP query"); ?>' />
                </a>
<?php
}
if (bHasSurveyPermission($surveyid, 'tokens', 'export'))
{
    ?>
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/exportdialog/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Export tokens to CSV file"); ?>'>
                    <img name='ExportButton' src='<?php echo $imageurl; ?>/exportcsv.png' alt='<?php $clang->eT("Export tokens to CSV file"); ?>' />
                </a>
<?php
}
if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
{
    ?>
                <img src='<?php echo $imageurl; ?>/seperator.gif' alt='' />
                <a href='<?php echo $this->createUrl("admin/emailtemplates/sa/index/surveyid/$surveyid"); ?>' title='<?php $clang->eTview("Edit email templates"); ?>'>
                    <img name='EmailTemplatesButton' src='<?php echo $imageurl; ?>/emailtemplates.png' alt='<?php $clang->eT("Edit email templates"); ?>' />
                </a>
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/email/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Send email invitation"); ?>'>
                    <img name='InviteButton' src='<?php echo $imageurl; ?>/invite.png' alt='<?php $clang->eT("Send email invitation"); ?>' />
                </a>
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/email/action/remind/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Send email reminder"); ?>'>
                    <img name='RemindButton' src='<?php echo $imageurl; ?>/remind.png' alt='<?php $clang->eT("Send email reminder"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>/seperator.gif' alt='' />
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/tokenify/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Generate tokens"); ?>'>
                    <img name='TokenifyButton' src='<?php echo $imageurl; ?>/tokenify.png' alt='<?php $clang->eT("Generate tokens"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>/seperator.gif' alt='' />
            <?php
            }
            if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
            {
                ?>
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/bouncesettings/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Bounce processing settings"); ?>' >
                    <img name='BounceSettings' src='<?php echo $imageurl; ?>/bounce_settings.png' alt='<?php $clang->eT("Bounce settings"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>/seperator.gif' alt='' />
            <?php
            }
            if (bHasSurveyPermission($surveyid, 'surveyactivation', 'update'))
            {
                ?>
                <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/kill/surveyid/$surveyid"); ?>', '_top')"
                   title='<?php $clang->eTview("Drop tokens table"); ?>' >
                    <img name='DeleteParticipantTable' src='<?php echo $imageurl; ?>/delete.png' alt='<?php $clang->eT("Drop tokens table"); ?>' />
                </a>
            <?php } ?>
        </div>
        <div class='menubar-right'><a href="#" onclick="showhelp('show')" title='<?php $clang->eTview("Show help"); ?>'>
                <img src='<?php echo $imageurl; ?>/showhelp.png' align='right' alt='<?php $clang->eT("Show help"); ?>' /></a>
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
