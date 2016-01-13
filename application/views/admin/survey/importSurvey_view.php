<div class='header ui-widget-header'><?php echo $sHeader;?></div>
<div class='messagebox ui-corner-all'>
    <?php
        if ($bFailed){ ?>
        <div class='errorheader'><?php eT("Error");?></div>
        <?php
        if(is_array($sErrorMessage))
        {
            foreach($sErrorMessage as $error)
                echo $error."<br/>";
        }
        else
        {
            echo $sErrorMessage;
        }
        ?>
        <br /><br />
        <input type='submit' value='<?php eT("Main Admin Screen");?>' onclick="window.open('<?php echo $this->createUrl('/admin');?>', '_top')">
        <input type='submit' value='<?php eT("Import again");?>' onclick="window.open('<?php echo $this->createUrl('admin/survey/sa/newsurvey#import');?>', '_top')"><br /><br /></div>
    <?php }
    else
    {?>
    <div class='successheader'><?php eT("Success");?></div>&nbsp;<br />
    <?php eT("File upload succeeded.");?> <?php eT("Reading file..");?><br />
        <br /><div class='successheader'><?php eT("Success");?></div>
        <strong><?php echo $sSummaryHeader; ?></strong><br />

        <ul style="text-align:left;">
            <li><?php eT("Surveys");?>: <?php echo $aImportResults['surveys'];?></li>
            <li><?php eT("Languages");?>: <?php echo $aImportResults['languages'];?></li>
            <li><?php eT("Question groups");?>: <?php echo $aImportResults['groups'];?></li>
            <li><?php eT("Questions");?>: <?php echo $aImportResults['questions'];?></li>
            <li><?php eT("Question attributes");?>: <?php echo $aImportResults['question_attributes'];?></li>
            <li><?php eT("Answers");?>: <?php echo $aImportResults['answers'];?></li>
            <?php if (isset($aImportResults['subquestions']))
                {?>
                <li><?php eT("Subquestions");?>: <?php echo $aImportResults['subquestions'];?></li>
                <?php }
                if (isset($aImportResults['defaultvalues']))
                {?>
                <li><?php eT("Default answers");?>: <?php echo $aImportResults['defaultvalues'];?></li>
                <?php }
                if (isset($aImportResults['conditions']))
                {?>
                <li><?php eT("Condition");?>: <?php echo $aImportResults['conditions'];?></li>
                <?php }
                if (isset($aImportResults['labelsets']))
                {?>
                <li><?php eT("Label sets");?>: <?php echo $aImportResults['labelsets'];?></li>
                <?php }
                if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
                {?>
                <li><?php eT("Not imported label sets");?>: <?php echo $aImportResults['deniedcountls'];?> <?php eT("(Label sets were not imported since you do not have the permission to create new label sets.)"); ?> </li>
                <?php }?>
            <li><?php eT("Assessments");?>: <?php echo $aImportResults['assessments'];?></li>
            <li><?php eT("Quotas");?>: <?php echo $aImportResults['quota'];?> (<?php echo $aImportResults['quotamembers']?> <?php eT("quota members");?> <?php eT("and");?> <?php echo $aImportResults['quotals']?> <?php eT("quota language settings"); ?></li>

        </ul>
        <?php
            if (isset($aImportResults['responses']))
            {?>
            <strong><?php eT("Response import summary"); ?></strong><br />
            <ul>
                <li><?php eT("Responses");?>: <?php echo $aImportResults['responses'];?></li>
                <?php }?>
        </ul>
        <br />
        <?php
            if (count($aImportResults['importwarnings'])>0)
            { ?>
            <div class='warningheader'><?php eT("Warnings");?>:</div>
            <ul style="text-align:left;">
                <?php
                    foreach ($aImportResults['importwarnings'] as $warning)
                    { ?>
                    <li><?php echo $warning; ?></li>
                    <?php
                } ?>
            </ul><br />
            <?php }
            if ($action == 'importsurvey')
            {?>
            <strong><?php eT("Import of survey is completed.");?></strong><br />
            <?php }
            elseif($action == 'copysurvey')
            {?>
            <strong><?php eT("Copy of survey is completed.");?></strong><br />
            <?php } ?>
			<br>
        <input type='submit' value='<?php eT("Go to survey");?>' onclick="window.open('<?php echo $sLink; ?>', '_top')"><br /><br />
    </div><br />
    <?php }?>
