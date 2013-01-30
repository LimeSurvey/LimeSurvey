<div id='tokenbar' class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Token control"); ?> </strong> <?php echo htmlspecialchars($thissurvey['surveyls_title']); ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>"><img src='<?php echo $imageurl; ?>home.png' alt='<?php $clang->eT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <a href="<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/$surveyid"); ?>"><img src='<?php echo $imageurl; ?>summary.png' alt='<?php $clang->eT("Show token summary"); ?>'/></a>
            <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <?php if (hasSurveyPermission($surveyid, 'tokens', 'read')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>document.png' alt='<?php $clang->eT("Display tokens"); ?>' />
                </a>
            <?php } ?>
            <?php if (hasSurveyPermission($surveyid, 'tokens', 'create')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>add.png' title='' alt='<?php $clang->eT("Add new token entry"); ?>' />
                </a>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/adddummies/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>create_dummy_token.png' title='' alt='<?php $clang->eT("Create dummy tokens"); ?>' />
                </a>
            <?php } ?>
            <?php if (hasSurveyPermission($surveyid, 'tokens', 'update') || hasSurveyPermission($iSurveyID, 'surveysettings', 'update')){ ?>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
                <a href="<?php echo $this->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>token_manage.png' title='' alt='<?php $clang->eT("Manage additional attribute fields"); ?>' />
                </a>
            <?php } ?>
            <?php if (hasSurveyPermission($surveyid, 'tokens', 'import')){ ?>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
                <a href="<?php echo $this->createUrl("admin/tokens/sa/import/surveyid/$surveyid") ?>">
                    <img src='<?php echo $imageurl; ?>importcsv.png' title='' alt='<?php $clang->eT("Import tokens from CSV file"); ?>' />
                </a>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/importldap/surveyid/$surveyid") ?>">
                    <img src='<?php echo $imageurl; ?>importldap.png' alt='<?php $clang->eT("Import tokens from LDAP query"); ?>' />
                </a>
            <?php } ?>
            <?php if (hasSurveyPermission($surveyid, 'tokens', 'export')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/exportdialog/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>exportcsv.png' alt='<?php $clang->eT("Export tokens to CSV file"); ?>' />
                </a>
            <?php } ?>
            <?php if (hasSurveyPermission($surveyid, 'tokens', 'update')){ ?>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
                <a href='<?php echo $this->createUrl("admin/emailtemplates/sa/index/surveyid/$surveyid"); ?>'>
                    <img src='<?php echo $imageurl; ?>emailtemplates.png' alt='<?php $clang->eT("Edit email templates"); ?>' />
                </a>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/email/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>invite.png' alt='<?php $clang->eT("Send email invitation"); ?>' />
                </a>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/email/action/remind/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>remind.png' alt='<?php $clang->eT("Send email reminder"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
                <a href="<?php echo $this->createUrl("admin/tokens/sa/tokenify/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>tokenify.png' alt='<?php $clang->eT("Generate tokens"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <?php } ?>
            <?php if (hasSurveyPermission($surveyid, 'tokens', 'update')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/bouncesettings/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>bounce_settings.png' alt='<?php $clang->eT("Bounce settings"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <?php } ?>
            <?php if(Yii::app()->session['USER_RIGHT_PARTICIPANT_PANEL'] == 1 && hasSurveyPermission($surveyid, 'tokens', 'create')){ ?>
                <a href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>">
                    <img src='<?php echo $imageurl; ?>cpdb.png' alt='<?php $clang->eT("Central participant database/panel"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <?php } ?>
            <?php if (hasSurveyPermission($surveyid, 'surveysettings', 'update') || HasSurveyPermission($surveyid, 'tokens','delete')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/kill/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>delete.png' alt='<?php $clang->eT("Delete tokens table"); ?>' />
                </a>
            <?php } ?>
        </div>
        <div class='menubar-right'><a href="#" onclick="showhelp('show')">
                <img src='<?php echo $imageurl; ?>showhelp.png' alt='<?php $clang->eT("Show help"); ?>' /></a>
        </div>
    </div>
</div>
<script type="text/javascript">
    <!--

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
        myform.submit();
    }

    //-->
</script>
