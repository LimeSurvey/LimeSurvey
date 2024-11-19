<?php
/**
 * optional plugins options panels
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyPlugins');

?>

<?php App()->getClientScript()->registerScript("plugins-panel-variables", "

    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '".gT("If you are using token functions or notifications emails you need to set an administrator email address.",'js')."'
    var sURLParameters = '';
    var sAddParam = '';
", LSYii_ClientScript::POS_BEGIN );
?>

<div id="advanced-question-editor">
    <?php if (isset($pluginSettings)):
            foreach ($pluginSettings as $id => $plugin)
            {
                $this->renderPartial('/admin/survey/subview/accordion/_plugin_panel', array('id'=>$id,'plugin'=>$plugin));
            }
    endif; ?>
</div>
