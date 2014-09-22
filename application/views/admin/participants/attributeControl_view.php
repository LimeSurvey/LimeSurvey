<script type="text/javascript">
    var sLoadText = '<?php $clang->eT("Loading...",'js');?>';
    var sEditAttributeMsg = '<?php $clang->eT("Edit attribute",'js');?>';
    var sDeleteButtonCaption = "<?php $clang->eT("Delete", 'js') ?>";
    var sSaveButtonCaption = "<?php $clang->eT("Save", 'js') ?>";
    var sCancel = "<?php $clang->eT("Cancel", 'js') ?>";
    var sSelectRowMsg = "<?php $clang->eT("Select at least one attribute.", 'js') ?>";
    var sWarningMsg = "<?php $clang->eT("Warning", 'js') ?>";
    var deleteCaption = "<?php $clang->eT("Delete attribute", 'js') ?>";
    var deleteMsg = "<?php $clang->eT("Delete selected attribute(s) and associated data?", 'js') ?>";
    var addCaption = "<?php $clang->eT("Add attribute", 'js') ?>";
    var addDisabledCaption = "<?php $clang->eT("Maximum 60 attributes. Please remove an attribute before adding.", 'js') ?>";
    var sRequired = "<?php $clang->eT("This field is required.", 'js') ?>";
    var refreshMsg = "<?php $clang->eT("Refresh list", 'js') ?>";
    var searchMsg = "<?php $clang->eT("Search attributes", 'js') ?>";
    var pagerMsg = "<?php $clang->eT("Page {0} of {1}", 'js') ?>";
    var viewRecordTxt= '<?php $clang->eT("View {0} - {1} of {2}",'js');?>';
    var emptyRecordsTxt= "<?php $clang->eT("No attributes to view", 'js') ?>";
    var sFindButtonCaption= "<?php $clang->eT("Find", 'js') ?>";
    var sResetButtonCaption= "<?php $clang->eT("Reset", 'js') ?>";
    var sSearchTitle= "<?php $clang->eT("Search...", 'js') ?>";
    var sOptionAnd= "<?php $clang->eT("AND", 'js') ?>";
    var sOptionOr= "<?php $clang->eT("OR", 'js') ?>";
    var attributeInfoUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getAttributeInfo_json"); ?>";
    var editAttributeUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/editAttributeInfo"); ?>";
    var attributeControlCols = '["<?php $clang->eT('Actions'); ?>", "<?php $clang->eT('Attribute name'); ?>", "<?php $clang->eT('Attribute type'); ?>", "<?php $clang->eT('Visible in participants panel'); ?>"]';
    var attributeTypeSelections = "TB:<?php $clang->eT("Text box"); ?>;DD:<?php $clang->eT("Drop-down list"); ?>;DP:<?php $clang->eT("Date"); ?>";
    var attributeTypeSearch = "<?php $clang->eT("Text box"); ?>:<?php $clang->eT("Text box"); ?>; <?php $clang->eT("Date"); ?>:<?php $clang->eT("Date"); ?>; <?php $clang->eT("Drop-down list"); ?>:<?php $clang->eT("Drop-down list"); ?>"
    var attributeEditUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/viewAttribute/aid"); ?>";
    var sOperator1= '<?php $clang->eT("equal",'js');?>';
    var sOperator2= '<?php $clang->eT("not equal",'js');?>';
    var sOperator3= '<?php $clang->eT("less",'js');?>';
    var sOperator4= '<?php $clang->eT("less or equal",'js');?>';
    var sOperator5= '<?php $clang->eT("greater",'js');?>';
    var sOperator6= '<?php $clang->eT("greater or equal",'js');?>';
    var sOperator7= '<?php $clang->eT("begins with",'js');?>';
    var sOperator8= '<?php $clang->eT("does not begin with",'js');?>';
    var sOperator9= '<?php $clang->eT("is in",'js');?>';
    var sOperator10= '<?php $clang->eT("is not in",'js');?>';
    var sOperator11= '<?php $clang->eT("ends with",'js');?>';
    var sOperator12= '<?php $clang->eT("does not end with",'js');?>';
    var sOperator13= '<?php $clang->eT("contains",'js');?>';
    var sOperator14= '<?php $clang->eT("does not contain",'js');?>';
</script>
<div class='header ui-widget-header'><strong><?php $clang->eT("Attribute management"); ?></strong></div>

<br/>

<table id="attributeControl">
    <tr><td>&nbsp;</td></tr>
</table>

<div id="pager"></div>

<br />