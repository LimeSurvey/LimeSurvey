<div class='menubar'>
<div class='menubar-title ui-widget-header'>
<strong><?php echo $title;?></strong>: (<?php echo $thissurvey['name'];?>)
</div>
<div class='menubar-main'>
<div class='menubar-left'>
<a href='<?php echo $this->createUrl("admin/survey/view/$surveyid");?>' title="<?php echo $clang->gTview("Return to survey administration");?>">
<img name='Administration' src='<?php echo $imageurl;?>/home.png' title='' alt='<?php echo $clang->gT("Return to survey administration");?>' /></a>
<img src='<?php echo $imageurl;?>/blank.gif' alt='' width='11' />
<img src='<?php echo $imageurl;?>/seperator.gif' alt='' />

<?php if (bHasSurveyPermission($surveyid,'responses','read')) { ?>
    <a href='<?php echo $this->createUrl("admin/browse/surveyid/$surveyid");?>' title="<?php echo $clang->gTview("Show summary information");?>">
    <img name='SurveySummary' src='<?php echo $imageurl;?>/summary.png' title='' alt='<?php echo $clang->gT("Show summary information");?>' /></a>
    <?php if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0) { ?>
        <a href='<?php echo $this->createUrl("admin/browse/sid/$surveyid/action/all");?>' title="<?php echo $clang->gTview("Display Responses");?>">
        <img name='ViewAll' src='<?php echo $imageurl;?>/document.png' title='' alt='<?php echo $clang->gT("Display Responses");?>' /></a>
    <?php } else { ?>
        <a href="<?php echo $this->createUrl("admin/browse/sid/$surveyid/action/all");?>" accesskey='b' id='browseresponses' title="<?php echo $clang->gTview("Display Responses");?>" >
        <img src='<?php echo $imageurl;?>/document.png' alt='<?php echo $clang->gT("Display Responses");?>' name='ViewAll' /></a>

        <div class="langpopup" id="browselangpopup"><?php echo $clang->gT("Please select a language:");?><ul>
        <?php foreach ($tmp_survlangs as $tmp_lang) { ?>
            <li><a href="<?php echo $this->createUrl("admin/browse/sid/$surveyid/action/all/start/0/limit/50/order/asc/browselang/$tmp_lang");?>" accesskey='b'><?php echo getLanguageNameFromCode($tmp_lang,false);?></a></li>
        <?php } ?>
        </ul></div>
    <?php } ?>
    <a href='<?php echo $this->createUrl("admin/browse/sid/$surveyid/action/all/start/0/limit/50/order/desc");?>' title="<?php echo $clang->gTview("Display Last 50 Responses");?>" >
    <img name='ViewLast' src='<?php echo $imageurl;?>/viewlast.png' alt='<?php echo $clang->gT("Display Last 50 Responses");?>' /></a>
<?php }
if (bHasSurveyPermission($surveyid,'responses','create')) { ?>
    <a href='<?php echo $this->createUrl("admin/dataentry/view/surveyid/$surveyid");?>' title="<?php echo $clang->gTview("Dataentry Screen for Survey");?>" >
    <img name='DataEntry' src='<?php echo $imageurl;?>/dataentry.png' alt='<?php echo $clang->gT("Dataentry Screen for Survey");?>' /></a>
<?php }
if (bHasSurveyPermission($surveyid,'statistics','read')) { ?>
    <a href='<?php echo $this->createUrl("admin/statistics/surveyid/$surveyid");?>' title="<?php echo $clang->gTview("Get statistics from these responses");?>">
    <img name='Statistics' src='<?php echo $imageurl;?>/statistics.png' alt='<?php echo $clang->gT("Get statistics from these responses");?>' /></a>
    <?php if ($thissurvey['savetimings']=="Y") { ?>
        <a href='<?php echo $this->createUrl("admin/browse/surveyid/$surveyid/subaction/time");?>' title="<?php echo $clang->gTview("Get time statistics from these responses");?>" >
        <img name='timeStatistics' src='<?php echo $imageurl;?>/timeStatistics.png' alt='<?php echo $clang->gT("Get time statistics from these responses");?>' /></a>
    <?php }
} ?>
<img src='<?php echo $imageurl;?>/seperator.gif' alt='' />
<?php if (bHasSurveyPermission($surveyid,'responses','export')) { ?>
    <a href='<?php echo $this->createUrl("admin/export/sa/exportresults/surveyid/$surveyid");?>' title="<?php echo $clang->gTview("Export results to application");?>">
    <img name='Export' src='<?php echo $imageurl;?>/export.png' alt='<?php echo $clang->gT("Export results to application");?>' /></a>

    <a href='<?php echo $this->createUrl("admin/export/sa/exportspss/sid/$surveyid");?>' title="<?php echo $clang->gTview("Export results to a SPSS/PASW command file");?>">
    <img src='<?php echo $imageurl;?>/exportspss.png' alt="<?php echo $clang->gT("Export results to a SPSS/PASW command file");?>" /></a>

    <a href='<?php echo $this->createUrl("admin/export/sa/exportr/sid/$surveyid");?>' title="<?php echo $clang->gTview("Export results to a R data file");?>" >
    <img src='<?php echo $imageurl;?>/exportr.png' alt='<?php echo $clang->gT("Export results to a R data file");?>' /></a>
<?php }
if (bHasSurveyPermission($surveyid,'responses','create'))
{ ?>
    <a href='<?php echo $this->createUrl("admin/dataentry/import/$surveyid");?>' title="<?php echo $clang->gTview("Import responses from a deactivated survey table");?>">
    <img name='ImportOldResponses' src='<?php echo $imageurl;?>/importold.png' alt='<?php echo $clang->gT("Import responses from a deactivated survey table");?>' /></a>
<?php } ?>
<img src='<?php echo $imageurl;?>/seperator.gif' alt='' />

<?php if (bHasSurveyPermission($surveyid,'responses','read')) { ?>
    <a href='<?php echo $this->createUrl("admin/saved/sa/view/surveyid/$surveyid");?>' title="<?php echo $clang->gTview("View Saved but not submitted Responses");?>" >
    <img src='<?php echo $imageurl;?>/saved.png' title='' alt='<?php echo $clang->gT("View Saved but not submitted Responses");?>' name='BrowseSaved' /></a>
<?php }
if (bHasSurveyPermission($surveyid,'responses','import'))  { ?>
    <a href='<?php echo $this->createUrl("admin/dataentry/sa/vvimport/surveyid/$surveyid");?>' title="<?php echo $clang->gTview("Import a VV survey file");?>" >
    <img src='<?php echo $imageurl;?>/importvv.png' alt='<?php echo $clang->gT("Import a VV survey file");?>' /></a>
<?php }
if (bHasSurveyPermission($surveyid,'responses','export'))  { ?>
    <a href='<?php echo $this->createUrl("admin/export/sa/vvexport/surveyid/$surveyid");?>' title="<?php echo $clang->gTview("Export a VV survey file");?>" >
    <img src='<?php echo $imageurl;?>/exportvv.png' title='' alt='<?php echo $clang->gT("Export a VV survey file");?>' /></a>
<?php }
if (bHasSurveyPermission($surveyid,'responses','delete') && $thissurvey['anonymized'] == 'N' && $thissurvey['tokenanswerspersistence'] == 'Y') { ?>
    <a href='<?php echo $this->createUrl("admin/iteratesurvey/surveyid/$surveyid");?>' title="<?php echo $clang->gTview("Iterate survey");?>" >
    <img src='<?php echo $imageurl;?>/iterate.png' title='' alt='<?php echo $clang->gT("Iterate survey");?>' /></a>
<?php } ?>
</div>
</div>
</div>
