<script type="text/javascript">
    var attributeInfoUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/getAttributeInfo_json"); ?>";
    var editAttributeUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/editAttributeInfo"); ?>";
    var attributeControlCols = '["<?php $clang->eT('Actions'); ?>", "<?php $clang->eT('Attribute name'); ?>", "<?php $clang->eT('Attribute type'); ?>", "<?php $clang->eT('Visible in participants panel'); ?>"]';
    var attributeTypeSelections = "DD:<?php $clang->eT("Drop-down list"); ?>;DP:<?php $clang->eT("Date"); ?>;TB:<?php $clang->eT("Text box"); ?>";
    var attributeTypeSearch = "<?php $clang->eT("Drop-down list"); ?>:<?php $clang->eT("Drop-down list"); ?>;<?php $clang->eT("Date"); ?>:<?php $clang->eT("Date"); ?>;<?php $clang->eT("Text box"); ?>:<?php $clang->eT("Text box"); ?>"
    var attributeEditUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/viewAttribute/aid"); ?>";
</script>

<div class='header ui-widget-header'><strong><?php $clang->eT("Attribute control"); ?></strong></div>

<br/>

<table id="attributeControl">
    <tr><td>&nbsp;</td></tr>
</table>

<div id="pager"></div>

<br />