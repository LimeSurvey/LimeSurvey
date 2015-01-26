<script type="text/javascript">
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '<?php eT("If you are using token functions or notifications emails you need to set an administrator email address.",'js'); ?>'
    var sURLParameters = '';
    var sAddParam = '';
</script>
<div id='tabs'>
    <ul>
        <li><a href='#general'><?php eT("General"); ?></a></li>
        <li><a href='#presentation'><?php eT("Presentation & navigation"); ?></a></li>
        <li><a href='#publication'><?php eT("Publication & access control"); ?></a></li>
        <li><a href='#notification'><?php eT("Notification & data management"); ?></a></li>
        <li><a href='#tokens'><?php eT("Tokens"); ?></a></li>
        <?php if ($action == "newsurvey") { ?>
        <li><a href='#import'><?php eT("Import"); ?></a></li>
        <li><a href='#copy'><?php eT("Copy"); ?></a></li>
        <?php }
        elseif ($action == "editsurveysettings") { ?>
        <li><a href='#panelintegration'><?php eT("Panel integration"); ?></a></li>
        <li><a href='#resources'><?php eT("Resources"); ?></a></li>

        <?php
            if (isset($pluginSettings))
            {
                echo "<li><a href='#pluginsettings'>" . gT("Plugins") ."</a></li>";
            }

        } ?>
    </ul>
    <?php
        if ($action == "editsurveysettings" && $surveyid)
        {
            echo CHtml::form(array('admin/survey','sa'=>'editsurveysettings','surveyid'=>$surveyid), 'post', array('id'=>'addnewsurvey', 'name'=>'addnewsurvey', 'class'=>'form30'));
        }
        else
        {
            echo CHtml::form(array('admin/survey','sa'=>'insert'), 'post', array('id'=>'addnewsurvey', 'name'=>'addnewsurvey', 'class'=>'form30'));
        }
    ?>
