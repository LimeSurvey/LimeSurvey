<script type="text/javascript">
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '<?php $clang->eT("If you are using token functions or notifications emails you need to set an administrator email address.",'js'); ?>' 
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
        
        <?php
            if (isset($pluginSettings))
            {
                echo "<li><a href='#pluginsettings'>" . $clang->gT("Plugins") ."</a></li>";
            }

        } ?>
    </ul>
    <?php
        if ($action == "editsurveysettings") $sURL="admin/database/index/updatesurveysettings";
        else $sURL="admin/survey/sa/insert";
    ?>
    <?php echo CHtml::form(array($sURL), 'post', array('id'=>'addnewsurvey', 'name'=>'addnewsurvey', 'class'=>'form30')); ?>
