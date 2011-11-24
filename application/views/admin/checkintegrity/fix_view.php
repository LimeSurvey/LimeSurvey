<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php $clang->eT("Data consistency check"); ?><br />
        <span style='font-size:7pt;'><?php $clang->eT("If errors are showing up you might have to execute this script repeatedly."); ?></span>
    </div>
    <ul>
    <?php foreach ($messages as $sMessage) {?>
     <li><?php echo $sMessage;?></li>
    <?php } ?>
    </ul>


    <p><?php $clang->eT("Check database again?"); ?><br />
    <a href='<?php echo $this->createUrl('admin/checkintegrity');?>'><?php $clang->eT("Check again"); ?></a><br />
</div>
