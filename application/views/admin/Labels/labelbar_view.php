<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php echo $clang->gT("Label Set"); ?>:</strong> <?php echo $row['label_name']; ?>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='40' height='20' border='0' hspace='0' align='left' alt='' />
            <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
            <a href='<?php echo site_url("admin/labels/editlabel/".$lid); ?>' title="<?php echo $clang->gTview("Edit label set"); ?>" >
 			<img name='EditLabelsetButton' src='<?php echo $this->config->item('imageurl'); ?>/edit.png' alt='<?php echo $clang->gT("Edit label set"); ?>' align='left'  /></a>
 			<a href='#' title='<?php echo $clang->gTview("Delete label set"); ?>' onclick="if (confirm('<?php echo $clang->gT("Do you really want to delete this label set?","js"); ?>')) {<?php echo get2post(site_url("admin/labels/process")."?action=deletelabelset&amp;lid=$lid"); ?>}" >
 			<img src='<?php echo $this->config->item('imageurl'); ?>/delete.png' border='0' alt='<?php echo $clang->gT("Delete label set"); ?>' align='left' /></a>
 			<img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
 			<a href='<?php echo site_url("admin/export/dumplabel/$lid");?>' title="<?php echo $clang->gTview("Export this label set"); ?>" >
            <img src='<?php echo $this->config->item('imageurl'); ?>/dumplabel.png' alt='<?php echo $clang->gT("Export this label set"); ?>' align='left' /></a>
        </div>
        <div class='menubar-right'>
            <input type='image' src='<?php echo $this->config->item('imageurl'); ?>/close.gif' title='<?php echo $clang->gT("Close Window"); ?>' onclick="window.open('<?php echo site_url("admin/labels/view"); ?>', '_top')" />
        </div>
    </div>
</div>
<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>