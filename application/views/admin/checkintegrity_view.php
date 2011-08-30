<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'><?php echo $clang->gT("Data Consistency Check"); ?><br />
        <span style='font-size:7pt;'><?php echo $clang->gT("If errors are showing up you might have to execute this script repeatedly."); ?></span>
    </div>

    <ul>
    <?php
    if (isset($cid))
    {?>
        <li><?php echo $clang->gT("The following conditions should be deleted:"); ?></li>
        <?php
        foreach ($cid as $cd) {?>
            CID: <?php echo $cd[0].' '.$clang->gT("Reason:")." {$cd[1]}";?><br /><?php
        }?>
        <br />
    <?php
    }
    else
    { ?>
        <li><?php echo $clang->gT("All conditions meet consistency standards"); ?></li><?php
    } ?>
    </ul>
</div>