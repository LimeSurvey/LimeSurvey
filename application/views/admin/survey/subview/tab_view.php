<div id='tabs'>
    <ul>
        <li><a href='#general'><?php echo $clang->gT("General"); ?></a></li>
        <li><a href='#presentation'><?php echo $clang->gT("Presentation & navigation"); ?></a></li>
        <li><a href='#publication'><?php echo $clang->gT("Publication & access control"); ?></a></li>
        <li><a href='#notification'><?php echo $clang->gT("Notification & data management"); ?></a></li>
        <li><a href='#tokens'><?php echo $clang->gT("Tokens"); ?></a></li>
        <?php if ($action == "newsurvey") { ?>
        <li><a href='#import'><?php echo $clang->gT("Import"); ?></a></li>
        <li><a href='#copy'><?php echo $clang->gT("Copy"); ?></a></li>
        <?php }
        elseif ($action == "editsurveysettings") { ?>
        <li><a href='#panelintegration'><?php echo $clang->gT("Panel integration"); ?></a></li>
        <li><a href='#resources'><?php echo $clang->gT("Resources"); ?></a></li>
        <?php } ?>
    </ul>
    <form class='form30' name='addnewsurvey' id='addnewsurvey' action='<?php if ($action == "newsurvey") echo $this->createUrl("admin/survey/insert"); if ($action == "editsurveysettings") echo $this->createUrl("admin/database/index/updatesurveysettings"); ?>' method='post' >
