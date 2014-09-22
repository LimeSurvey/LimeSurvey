<?php
App()->getClientScript()->registerPackage('jqueryui-timepicker');
?><div id='tokenbar' class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Token control"); ?> </strong> <?php echo htmlspecialchars($thissurvey['surveyls_title']); ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href="<?php echo $this->createUrl("admin/survey/sa/view/surveyid/$surveyid"); ?>"><img src='<?php echo $imageurl; ?>home.png' alt='<?php $clang->eT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <a href="<?php echo $this->createUrl("admin/tokens/sa/index/surveyid/$surveyid"); ?>"><img src='<?php echo $imageurl; ?>summary.png' alt='<?php $clang->eT("Show token summary"); ?>'/></a>
            <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'read')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>document.png' alt='<?php $clang->eT("Display tokens"); ?>' />
                </a>
            <?php } ?>
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'create')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>add.png' title='' alt='<?php $clang->eT("Add new token entry"); ?>' />
                </a>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/adddummies/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>create_dummy_token.png' title='' alt='<?php $clang->eT("Create dummy tokens"); ?>' />
                </a>
            <?php } ?>
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update') || Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')){ ?>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
                <a href="<?php echo $this->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>token_manage.png' title='' alt='<?php $clang->eT("Manage additional attribute fields"); ?>' />
                </a>
            <?php } ?>
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'import')){ ?>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
                <a href="<?php echo $this->createUrl("admin/tokens/sa/import/surveyid/$surveyid") ?>">
                    <img src='<?php echo $imageurl; ?>importcsv.png' title='' alt='<?php $clang->eT("Import tokens from CSV file"); ?>' />
                </a>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/importldap/surveyid/$surveyid") ?>">
                    <img src='<?php echo $imageurl; ?>importldap.png' alt='<?php $clang->eT("Import tokens from LDAP query"); ?>' />
                </a>
            <?php } ?>
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'export')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/exportdialog/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>exportcsv.png' alt='<?php $clang->eT("Export tokens to CSV file"); ?>' />
                </a>
            <?php } ?>
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update')){ ?>
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
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'update')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/bouncesettings/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>bounce_settings.png' alt='<?php $clang->eT("Bounce settings"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <?php } ?>
            <?php if(Permission::model()->hasGlobalPermission('participantpanel','read') && Permission::model()->hasSurveyPermission($surveyid, 'tokens', 'create')){ ?>
                <a href="<?php echo $this->createUrl("admin/participants/sa/displayParticipants"); ?>">
                    <img src='<?php echo $imageurl; ?>cpdb.png' alt='<?php $clang->eT("Central participant database/panel"); ?>' />
                </a>
                <img src='<?php echo $imageurl; ?>separator.gif' class='separator' alt='' />
            <?php } ?>
            <?php if (Permission::model()->hasSurveyPermission($surveyid, 'surveysettings', 'update') || Permission::model()->hasSurveyPermission($surveyid, 'tokens','delete')){ ?>
                <a href="<?php echo $this->createUrl("admin/tokens/sa/kill/surveyid/$surveyid"); ?>">
                    <img src='<?php echo $imageurl; ?>delete.png' alt='<?php $clang->eT("Delete tokens table"); ?>' />
                </a>
            <?php } ?>
        </div>
        <div class='menubar-right'>
            <a href="http://manual.limesurvey.org" target='_blank'>
                <img src='<?php echo $imageurl; ?>showhelp.png' alt='<?php $clang->eT("Show help"); ?>' />
            </a>
        </div>
    </div>
</div>
