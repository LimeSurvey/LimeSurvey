<script type="text/javascript">
    var sLoadText = '<?php eT("Loading...",'js');?>';
    var sEditAttributeMsg = '<?php eT("Edit attribute",'js');?>';
    var sDeleteButtonCaption = "<?php eT("Delete", 'js') ?>";
    var sSaveButtonCaption = "<?php eT("Save", 'js') ?>";
    var sCancel = "<?php eT("Cancel", 'js') ?>";
    var sSelectRowMsg = "<?php eT("Select at least one attribute.", 'js') ?>";
    var sWarningMsg = "<?php eT("Warning", 'js') ?>";
    var deleteCaption = "<?php eT("Delete attribute", 'js') ?>";
    var deleteMsg = "<?php eT("Delete selected attribute(s) and associated data?", 'js') ?>";
    var addCaption = "<?php eT("Add attribute", 'js') ?>";
    var addDisabledCaption = "<?php eT("Maximum 60 attributes. Please remove an attribute before adding.", 'js') ?>";
    var sRequired = "<?php eT("This field is required.", 'js') ?>";
    var refreshMsg = "<?php eT("Refresh list", 'js') ?>";
    var searchMsg = "<?php eT("Search attributes", 'js') ?>";
    var pagerMsg = "<?php eT("Page {0} of {1}", 'js') ?>";
    var viewRecordTxt= '<?php eT("View {0} - {1} of {2}",'js');?>';
    var emptyRecordsTxt= "<?php eT("No attributes to view", 'js') ?>";
    var sFindButtonCaption= "<?php eT("Find", 'js') ?>";
    var sResetButtonCaption= "<?php eT("Reset", 'js') ?>";
    var sSearchTitle= "<?php eT("Search...", 'js') ?>";
    var sOptionAnd= "<?php eT("AND", 'js') ?>";
    var sOptionOr= "<?php eT("OR", 'js') ?>";
    var attributeInfoUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/getAttributeInfo_json"); ?>";
    var editAttributeUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/editAttributeInfo"); ?>";
    var attributeControlCols = '["<?php eT('Actions'); ?>", "<?php eT('Attribute name'); ?>", "<?php eT('Attribute type'); ?>", "<?php eT('Visible in participants panel'); ?>"]';
    var attributeTypeSelections = "TB:<?php eT("Text box"); ?>;DD:<?php eT("Drop-down list"); ?>;DP:<?php eT("Date"); ?>";
    var attributeTypeSearch = "<?php eT("Text box"); ?>:<?php eT("Text box"); ?>; <?php eT("Date"); ?>:<?php eT("Date"); ?>; <?php eT("Drop-down list"); ?>:<?php eT("Drop-down list"); ?>"
    var attributeEditUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/sa/viewAttribute/aid"); ?>";
    var sOperator1= '<?php eT("equal",'js');?>';
    var sOperator2= '<?php eT("not equal",'js');?>';
    var sOperator3= '<?php eT("less",'js');?>';
    var sOperator4= '<?php eT("less or equal",'js');?>';
    var sOperator5= '<?php eT("greater",'js');?>';
    var sOperator6= '<?php eT("greater or equal",'js');?>';
    var sOperator7= '<?php eT("begins with",'js');?>';
    var sOperator8= '<?php eT("does not begin with",'js');?>';
    var sOperator9= '<?php eT("is in",'js');?>';
    var sOperator10= '<?php eT("is not in",'js');?>';
    var sOperator11= '<?php eT("ends with",'js');?>';
    var sOperator12= '<?php eT("does not end with",'js');?>';
    var sOperator13= '<?php eT("contains",'js');?>';
    var sOperator14= '<?php eT("does not contain",'js');?>';
</script>
<div class='header ui-widget-header'><strong><?php eT("Attribute management"); ?></strong></div>

<br/>

<table id="attributeControl">
    <tr><td>&nbsp;</td></tr>
</table>

<div id="pager"></div>

<br />