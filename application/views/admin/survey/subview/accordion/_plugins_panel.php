<?php
/**
 * Optionnal plugins options panels
 */
?>
<?php if (isset($pluginSettings)):
        foreach ($pluginSettings as $id => $plugin)
        {
            $this->renderPartial('/admin/survey/subview/accordion/_plugin_panel', array('id'=>$id,'plugin'=>$plugin));
        }
endif; ?>
