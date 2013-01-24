<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <div class='menubar-title-left'>
            <strong><?php $clang->eT("Administration");?></strong>
            <?php
                if(Yii::app()->session['loginID'])
                { ?>
                --  <?php $clang->eT("Logged in as:");?><strong>
                    <a href="<?php echo $this->createUrl("/admin/user/sa/personalsettings"); ?>">
                        <?php echo Yii::app()->session['user'];?> <img src='<?php echo $sImageURL;?>profile_edit.png' alt='<?php $clang->eT("Edit your personal preferences");?>' /></a>
                </strong>
                <?php } ?>
        </div>
        <?php
            if($showupdate)
            { ?>
            <div class='menubar-title-right'><a href='<?php echo $this->createUrl("admin/globalsettings");?>'><?php echo sprintf($clang->gT('Update available: %s'),$updateversion."($updatebuild)");?></a></div>
            <?php } ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href="<?php echo $this->createUrl("/admin/survey/sa/index"); ?>">
                <img src='<?php echo $sImageURL;?>home.png' alt='<?php $clang->eT("Default administration page");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/></a>

            <img src='<?php echo $sImageURL;?>blank.gif' alt='' width='11' />
            <img src='<?php echo $sImageURL;?>separator.gif' id='separator1' class='separator' alt='' />

            <a href="<?php echo $this->createUrl("admin/user/sa/index"); ?>">
                <img src='<?php echo $sImageURL;?>security.png' alt='<?php $clang->eT("Manage survey administrators");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/></a>
            <?php
                if(Yii::app()->session['USER_RIGHT_CREATE_USER'])
                {?>
                <a href="<?php echo $this->createUrl("admin/usergroups/sa/index"); ?>">
                <img src='<?php echo $sImageURL;?>usergroup.png' alt='<?php $clang->eT("Create/edit user groups");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/></a>
            <?php
                }
                if(Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1)
                { ?>
                <a href="<?php echo $this->createUrl("admin/globalsettings"); ?>">
                    <img src='<?php echo $sImageURL;?>global.png' alt='<?php $clang->eT("Global settings");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/></a>
                <img src='<?php echo $sImageURL;?>separator.gif' class='separator' alt='' />
                <?php }
                if(Yii::app()->session['USER_RIGHT_CONFIGURATOR'] == 1)
                { ?>
                <a href="<?php echo $this->createUrl("admin/checkintegrity"); ?>">
                    <img src='<?php echo $sImageURL;?>checkdb.png' alt='<?php $clang->eT("Check Data Integrity");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/></a>
                <?php
                }
                if(Yii::app()->session['USER_RIGHT_SUPERADMIN'] == 1)
                {

                    if (in_array(Yii::app()->db->getDriverName(), array('mysql', 'mysqli')) || Yii::app()->getConfig('demoMode') == true)
                    {

                    ?>

                    <a href="<?php echo $this->createUrl("admin/dumpdb"); ?>" >
                        <img src='<?php echo $sImageURL;?>backup.png' alt='<?php $clang->eT("Backup Entire Database");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/>
                    </a>

                    <?php } else { ?>
                    <img src='<?php echo $sImageURL; ?>backup_disabled.png' alt='<?php $clang->eT("The database export is only available for MySQL databases. For other database types please use the according backup mechanism to create a database dump."); ?>' />
                    <?php } ?>

                <img src='<?php echo $sImageURL; ?>separator.gif' class='separator' alt='' />

                <?php
                }
                if(Yii::app()->session['USER_RIGHT_MANAGE_LABEL'] == 1)
                {
                ?>

                <a href="<?php echo $this->createUrl("admin/labels/sa/view"); ?>" >
                    <img src='<?php echo $sImageURL;?>labels.png'  alt='<?php $clang->eT("Edit label sets");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/></a>
                <img src='<?php echo $sImageURL;?>separator.gif' class='separator' alt='' />
                <?php }
                if(Yii::app()->session['USER_RIGHT_MANAGE_TEMPLATE'] == 1)
                { ?>
                <a href="<?php echo $this->createUrl("admin/templates/sa/view"); ?>">
                    <img src='<?php echo $sImageURL;?>templates.png' alt='<?php $clang->eT("Template Editor");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/></a>
                <?php } ?>
            <img src='<?php echo $sImageURL;?>separator.gif' class='separator' alt='' />
            <?php
                if(Yii::app()->session['USER_RIGHT_PARTICIPANT_PANEL'] == 1)
                { 	 ?>
                <a href="<?php echo $this->createUrl("admin/participants/sa/index"); ?>" >
                    <img src='<?php echo $sImageURL;?>cpdb.png' alt='<?php $clang->eT("Central participant database/panel");?>' width='<?php echo $iconsize;?>' height='<?php echo $iconsize;?>'/></a>
                <?php } ?>
        </div>
        <div class='menubar-right'>
            <label for='surveylist'><?php $clang->eT("Surveys:");?></label>
            <select id='surveylist' name='surveylist' onchange="if (this.options[this.selectedIndex].value!='') {window.open('<?php echo $this->createUrl("/admin/survey/sa/view/surveyid/"); ?>/'+this.options[this.selectedIndex].value,'_top')} else {window.open('<?php echo $this->createUrl("/admin/survey/sa/index/");?>','_top')}">
                <?php echo getSurveyList(false, $surveyid); ?>
            </select>
            <a href="<?php echo $this->createUrl("admin/survey/sa/index"); ?>">
                <img src='<?php echo $sImageURL;?>surveylist.png' alt='<?php $clang->eT("Detailed list of surveys");?>' />
            </a>

            <?php
                if(Yii::app()->session['USER_RIGHT_CREATE_SURVEY'] == 1)
                { ?>

                <a href="<?php echo $this->createUrl("admin/survey/sa/newsurvey"); ?>">
                    <img src='<?php echo $sImageURL;?>add.png' alt='<?php $clang->eT("Create, import, or copy a survey");?>' /></a>
                <?php } ?>


            <img id='separator2' src='<?php echo $sImageURL;?>separator.gif' class='separator' alt='' />
            <a href="<?php echo $this->createUrl("admin/authentication/sa/logout"); ?>" >
                <img src='<?php echo $sImageURL;?>logout.png' alt='<?php $clang->eT("Logout");?>' /></a>

            <a href="http://docs.limesurvey.org" target="_blank">
                <img src='<?php echo $sImageURL;?>showhelp.png' alt='<?php $clang->eT("LimeSurvey online manual");?>' /></a>
        </div>
    </div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>
