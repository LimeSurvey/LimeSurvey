<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo $clang->gT("Data consistency check"); ?><br />
        <span style='font-size:7pt;'><?php echo $clang->gT("If errors are showing up you might have to execute this script repeatedly."); ?></span>
    </div>
    <ul>
    <?php foreach ($messages as $sMessage) {?>
     <li><?php echo $sMessage;?></li>
    <?php } ?>
    </ul>


    <p><?php echo $clang->gT("Check database again?"); ?><br />
    <a href='<?php echo site_url('admin/checkintegrity');?>'><?php echo $clang->gT("Check again"); ?></a><br />
</div>