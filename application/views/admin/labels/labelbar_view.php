<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Label Set"); ?>:</strong> <?php echo $row['label_name']; ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' width='40' height='20' border='0' hspace='0' align='left' alt='' />
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
            <a href='<?php echo $this->createUrl("admin/labels/sa/editlabelset/lid/".$lid); ?>' title="<?php $clang->eTview("Edit label set"); ?>" >
 			<img name='EditLabelsetButton' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/edit.png' alt='<?php $clang->eT("Edit label set"); ?>' align='left'  /></a>
 			<a href='#' title='<?php $clang->eTview("Delete label set"); ?>' onclick="if (confirm('<?php $clang->eT("Do you really want to delete this label set?","js"); ?>')) { <?php echo get2post($this->createUrl("admin/labels/sa/process")."?action=deletelabelset&amp;lid=$lid"); ?>}" >
 			<img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/delete.png' border='0' alt='<?php $clang->eT("Delete label set"); ?>' align='left' /></a>
 			<img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
 			<a href='<?php echo $this->createUrl("admin/export/sa/dumplabel/lid/$lid");?>' title="<?php $clang->eTview("Export this label set"); ?>" >
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/dumplabel.png' alt='<?php $clang->eT("Export this label set"); ?>' align='left' /></a>
        </div>
        <div class='menubar-right'>
            <input type='image' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/close.gif' title='<?php $clang->eT("Close Window"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/labels/sa/view"); ?>', '_top')" />
        </div>
    </div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>