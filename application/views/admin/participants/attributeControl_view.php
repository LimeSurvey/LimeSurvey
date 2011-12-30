<script type="text/javascript">
    var attributeInfoUrl = "<?php echo Yii::app()->createUrl("admin/participants/sa/getAttributeInfo_json"); ?>";
    var editAttributeUrl = "<?php echo Yii::app()->createUrl("admin/participants/sa/editAttributeInfo"); ?>";
    var attributeControlCols = '["<?php $clang->eT('Actions'); ?>", "<?php $clang->eT('Attribute Name'); ?>", "<?php $clang->eT('Attribute Type'); ?>", "<?php $clang->eT('Visible in participants panel'); ?>"]';
    var attributeTypeSelections = "DD:<?php $clang->eT("Drop-down list"); ?>;DP:<?php $clang->eT("Date"); ?>;TB:<?php $clang->eT("Text Box"); ?>";
    var attributeTypeSearch = "<?php $clang->eT("Drop-down list"); ?>:<?php $clang->eT("Drop-down list"); ?>;<?php $clang->eT("Date"); ?>:<?php $clang->eT("Date"); ?>;<?php $clang->eT("Text Box"); ?>:<?php $clang->eT("Text Box"); ?>"
    var attributeEditUrl = "<?php echo Yii::app()->createUrl("admin/participants/sa/viewAttribute/aid"); ?>";
</script>

<div class='header ui-widget-header'><strong><?php $clang->eT("Attribute control"); ?></strong></div>

<br/>

<table id="attributeControl">
    <tr><td>&nbsp;</td></tr>
</table>

<div id="pager"></div>

<br />