<div class='header ui-widget-header'><?php eT("Copy survey"); ?></div>
    <div class='messagebox ui-corner-all'>

        <?php if (isset($aImportResults['error']) && $aImportResults['error']!=false)
        { ?>
            <div class='warningheader'><?php eT("Error"); ?></div><br />
             <?php echo $aImportResults['error']; ?><br /><br />
            <input type='submit' value='<?php eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $this->createUrl('admin'); ?>', '_top')" />
            <?php $importerror = true;
        }
        
        if (!$importerror)
        { ?>
            <div class='successheader'><?php eT("Success"); ?></div><br /><br />
            
            <strong><?php eT("Survey import summary"); ?></strong><br />        
            
            
            <ul style="text-align:left;">
            <li><?php eT("Surveys"); ?>: <?php echo $aImportResults['surveys']; ?></li>
            <li><?php eT("Languages"); ?>: <?php echo $aImportResults['languages']; ?></li>
            <li><?php eT("Question groups"); ?>: <?php echo $aImportResults['groups']; ?></li>
            <li><?php eT("Questions"); ?>: <?php echo $aImportResults['questions']; ?></li>
            <li><?php eT("Answers"); ?>: <?php echo $aImportResults['answers']; ?></li>
            <?php if (isset($aImportResults['subquestions']))
            { ?>
                <li><?php eT("Subquestions"); ?>: <?php echo $aImportResults['subquestions']; ?></li>
            <?php }
            if (isset($aImportResults['defaultvalues']))
            { ?>
                <li><?php eT("Default answers"); ?>: <?php echo $aImportResults['defaultvalues']; ?></li>     
            <?php }
            if (isset($aImportResults['conditions']))
            { ?>
                <li><?php eT("Condition"); ?>: <?php echo $aImportResults['conditions']; ?></li>
            <?php }
            if (isset($aImportResults['labelsets']))
            { ?>
                <li><?php eT("Label sets"); ?>: <?php echo $aImportResults['labelsets']; ?></li>
            <?php }
            if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
            { ?>
                <li><?php eT("Not imported label sets"); ?>: <?php echo $aImportResults['deniedcountls']; eT("(Label sets were not imported since you do not have the permission to create new label sets.)"); ?></li>
            <?php } ?>
            <li><?php eT("Question attributes"); ?>: <?php echo $aImportResults['question_attributes']; ?></li>
            <li><?php eT("Assessments"); ?>: <?php echo $aImportResults['assessments']; ?></li>
            <li><?php eT("Quotas"); ?>: <?php echo $aImportResults['quota']; ?> (<?php echo $aImportResults['quotamembers']; eT("quota members"); eT("and"); echo $aImportResults['quotals']; eT("quota language settings"); ?>)</li></ul><br />
            
            <?php if (count($aImportResults['importwarnings'])>0) 
            { ?>
                <div class='warningheader'><?php eT("Warnings"); ?>:</div>
                <ul style="text-align:left;">
                <?php foreach ($aImportResults['importwarnings'] as $warning)
                { ?>
                    <li><?php echo $warning; ?></li>
                <?php } ?>
                </ul><br />
            <?php } ?>
            
            <strong><?php eT("Copy of survey is completed."); ?></strong>
            <a href='<?php echo $this->createUrl("admin/survey/sa/view/".$aImportResults['newsid']); ?>'>
            <?php eT("Go to survey"); ?></a>
    <?php } ?>
</div>