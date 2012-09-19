<script type="text/javascript">
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sURLParameters = '';
    var sAddParam = '';
</script>
<div id='tabs'>
    <ul>
        <li><a href='#general'><?php $clang->eT("General"); ?></a></li>
        <li><a href='#presentation'><?php $clang->eT("Presentation & navigation"); ?></a></li>
        <li><a href='#publication'><?php $clang->eT("Publication & access control"); ?></a></li>
        <li><a href='#notification'><?php $clang->eT("Notification & data management"); ?></a></li>
        <li><a href='#tokens'><?php $clang->eT("Tokens"); ?></a></li>
        <?php if ($action == "newsurvey") { ?>
        <li><a href='#import'><?php $clang->eT("Import"); ?></a></li>
        <li><a href='#copy'><?php $clang->eT("Copy"); ?></a></li>
        <?php }
        elseif ($action == "editsurveysettings") { ?>
        <li><a href='#panelintegration'><?php $clang->eT("Panel integration"); ?></a></li>
        <li><a href='#resources'><?php $clang->eT("Resources"); ?></a></li>
        <?php } ?>
    </ul>
    <form class='form30' name='addnewsurvey' id='addnewsurvey' action='<?php if ($action == "newsurvey") echo $this->createUrl("admin/survey/insert"); if ($action == "editsurveysettings") echo $this->createUrl("admin/database/index/updatesurveysettings"); ?>' method='post' >
