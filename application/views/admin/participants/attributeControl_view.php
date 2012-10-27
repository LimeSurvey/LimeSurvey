<script type="text/javascript">
    var deleteCaption = "<?php $clang->eT("Delete attribute", 'js') ?>";
    var deleteMsg = "<?php $clang->eT("Delete selected attribute(s) and associated data?", 'js') ?>";
    var addCaption = "<?php $clang->eT("Add attribute", 'js') ?>";
    var refreshMsg = "<?php $clang->eT("Refresh list", 'js') ?>";
    var searchMsg = "<?php $clang->eT("Search attributes", 'js') ?>";
    var pagerMsg = "<?php $clang->eT("Page {0} of {1}", 'js') ?>";
    var viewRecordTxt= '<?php $clang->eT("View {0} - {1} of {2}",'js');?>';
    var emptyRecordsTxt= "<?php $clang->eT("No attributes to view", 'js') ?>";

    var attributeInfoUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/getAttributeInfo_json"); ?>";
    var editAttributeUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/editAttributeInfo"); ?>";
    var attributeControlCols = '["<?php $clang->eT('Actions'); ?>", "<?php $clang->eT('Attribute name'); ?>", "<?php $clang->eT('Attribute type'); ?>", "<?php $clang->eT('Visible in participants panel'); ?>"]';
    var attributeTypeSelections = "TB:<?php $clang->eT("Text box"); ?>;DD:<?php $clang->eT("Drop-down list"); ?>;DP:<?php $clang->eT("Date"); ?>";
    var attributeTypeSearch = "<?php $clang->eT("Text box"); ?>:<?php $clang->eT("Text box"); ?>; <?php $clang->eT("Date"); ?>:<?php $clang->eT("Date"); ?>; <?php $clang->eT("Drop-down list"); ?>:<?php $clang->eT("Drop-down list"); ?>"
    var attributeEditUrl = "<?php echo Yii::app()->getController()->createUrl("admin/participants/viewAttribute/aid"); ?>";
</script>
<div class="ui-notify" id="flashmessage">
    <div id="flashinfo" style="opacity: 0; overflow: hidden; height: 40px; margin-top: 0px; margin-bottom: 0px; padding-top: 0px; padding-bottom: 0px;" class="ui-state-highlight ui-corner-all ui-notify-message">
        <!-- close link -->
        <a class="ui-notify-close" href="#">
            <span class="ui-icon ui-icon-close" style="float:right">&nbsp;</span>
        </a>

        <!-- alert icon -->
        <span style="float:left; margin:2px 5px 0 0;" class="ui-icon ui-icon-info">&nbsp;</span>
        <p id='flashmessagetext'></p><br>
    </div>
</div>
<div class='header ui-widget-header'><strong><?php $clang->eT("Attribute management"); ?></strong></div>

<br/>

<table id="attributeControl">
    <tr><td>&nbsp;</td></tr>
</table>

<div id="pager"></div>

<br />