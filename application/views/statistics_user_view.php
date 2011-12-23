<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang="<?php echo $surveylanguage; ?>" lang="<?php echo $surveylanguage; ?>" <?php if ($condition) { echo 'dir = "rtl"'; } ?> >
    <head>
        <title>$sitename</title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-\" />
        <link href="<?php echo $thisSurveyCssPath; ?>/template.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <div id='statsContainer'>
            <div id='statsHeader'>
                <div class='statsSurveyTitle'><?php echo $thisSurveyTitle; ?></div>
                <div class='statsNumRecords'><?php echo $clang->gT("Total records in survey")." : $totalrecords"; ?></div>
            </div>
            <?php if (isset($summary) && $summary) { echo $statisticsoutput; } ?><br />
        </div>
                
