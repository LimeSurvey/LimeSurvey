<div class='header ui-widget-header'><?php echo $clang->gT("Copy survey"); ?></div>
    <div class='messagebox ui-corner-all'>

        <?php if (isset($aImportResults['error']) && $aImportResults['error']!=false)
        { ?>
            <div class='warningheader'><?php echo $clang->gT("Error"); ?></div><br />
             <?php echo $aImportResults['error']; ?><br /><br />
            <input type='submit' value='<?php echo $clang->gT("Main Admin Screen"); ?>' onclick="window.open('<?php echo site_url('admin'); ?>', '_top')" />
            <?php $importerror = true;
        }
        
        if (!$importerror)
        { ?>
            <div class='successheader'><?php echo $clang->gT("Success"); ?></div><br /><br />
            
            <strong><?php echo $clang->gT("Survey import summary"); ?></strong><br />        
            
            
            <ul style="text-align:left;">
            <li><?php echo $clang->gT("Surveys"); ?>: <?php echo $aImportResults['surveys']; ?></li>
            <li><?php echo $clang->gT("Languages"); ?>: <?php echo $aImportResults['languages']; ?></li>
            <li><?php echo $clang->gT("Question groups"); ?>: <?php echo $aImportResults['groups']; ?></li>
            <li><?php echo $clang->gT("Questions"); ?>: <?php echo $aImportResults['questions']; ?></li>
            <li><?php echo $clang->gT("Answers"); ?>: <?php echo $aImportResults['answers']; ?></li>
            <?php if (isset($aImportResults['subquestions']))
            { ?>
                <li><?php echo $clang->gT("Subquestions"); ?>: <?php echo $aImportResults['subquestions']; ?></li>
            <?php }
            if (isset($aImportResults['defaultvalues']))
            { ?>
                <li><?php echo $clang->gT("Default answers"); ?>: <?php echo $aImportResults['defaultvalues']; ?></li>     
            <?php }
            if (isset($aImportResults['conditions']))
            { ?>
                <li><?php echo $clang->gT("Conditions"); ?>: <?php echo $aImportResults['conditions']; ?></li>
            <?php }
            if (isset($aImportResults['labelsets']))
            { ?>
                <li><?php echo $clang->gT("Label sets"); ?>: <?php echo $aImportResults['labelsets']; ?></li>
            <?php }
            if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
            { ?>
                <li><?php echo $clang->gT("Not imported label sets"); ?>: <?php echo $aImportResults['deniedcountls']; echo $clang->gT("(Label sets were not imported since you do not have the permission to create new label sets.)"); ?></li>
            <?php } ?>
            <li><?php echo $clang->gT("Question attributes"); ?>: <?php echo $aImportResults['question_attributes']; ?></li>
            <li><?php echo $clang->gT("Assessments"); ?>: <?php echo $aImportResults['assessments']; ?></li>
            <li><?php echo $clang->gT("Quotas"); ?>: <?php echo $aImportResults['quota']; ?> (<?php echo $aImportResults['quotamembers']; echo $clang->gT("quota members"); echo $clang->gT("and"); echo $aImportResults['quotals']; echo $clang->gT("quota language settings"); ?>)</li></ul><br />
            
            <?php if (count($aImportResults['importwarnings'])>0) 
            { ?>
                <div class='warningheader'><?php echo $clang->gT("Warnings"); ?>:</div>
                <ul style="text-align:left;">
                <?php foreach ($aImportResults['importwarnings'] as $warning)
                { ?>
                    <li><?php echo $warning; ?></li>
                <?php } ?>
                </ul><br />
            <?php } ?>
            
            <strong><?php echo $clang->gT("Copy of survey is completed."); ?></strong>
            <a href='<?php echo site_url("admin/survey/view/".$aImportResults['newsid']); ?>'>
            <?php echo $clang->gT("Go to survey"); ?></a>
    <?php } ?>
</div>