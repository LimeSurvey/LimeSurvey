<?php
/**
 * Optionnal plugins options panels
 */
?>
<script type="text/javascript">
    var jsonUrl = '';
    var sAction = '';
    var sParameter = '';
    var sTargetQuestion = '';
    var sNoParametersDefined = '';
    var sAdminEmailAddressNeeded = '<?php  eT("If you are using token functions or notifications emails you need to set an administrator email address.",'js'); ?>'
    var sURLParameters = '';
    var sAddParam = '';
</script>
<?php if (isset($pluginSettings)):
        foreach ($pluginSettings as $id => $plugin)
        {
            $this->renderPartial('/admin/survey/subview/accordion/_plugin_panel', array('id'=>$id,'plugin'=>$plugin));
        }
endif; ?>
