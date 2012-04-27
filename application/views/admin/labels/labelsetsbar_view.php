<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Label set administration"); ?></strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href='<?php echo $this->createUrl("/admin/index"); ?>' title="<?php $clang->eTview("Return to survey administration"); ?>" >
                <img name='Administration' src='<?php echo $sImageURL; ?>home.png' alt='<?php $clang->eT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo $sImageURL; ?>blank.gif' width='11' height='20' alt='' />
            <img src='<?php echo $sImageURL; ?>seperator.gif' alt='' />
            <img src='<?php echo $sImageURL; ?>blank.gif' width='76' height='20' alt='' />
            <img src='<?php echo $sImageURL; ?>seperator.gif' border='0' hspace='0' alt='' />
            <a href='<?php echo $this->createUrl("admin/labels/exportmulti");?>' title="<?php $clang->eTview("Export label set"); ?>" >
                <img src='<?php echo $sImageURL; ?>dumplabelmulti.png' alt='<?php $clang->eT("Export multiple label sets"); ?>' /></a>
        </div>
        <div class='menubar-right'>
            <img src='<?php echo $sImageURL; ?>blank.gif' width='5' height='20' alt='' />
            <label for='labelsetchanger'><?php $clang->eT("Label sets:");?> </label>
            <select id='labelsetchanger' onchange="window.open(this.options[this.selectedIndex].value,'_top')">
                <option value=''
                    <?php if (!isset($lid) || $lid<1) { ?> selected='selected' <?php } ?>
                    ><?php $clang->eT("Please choose..."); ?></option>

                <?php if (count($labelsets)>0)
                    {
                        foreach ($labelsets as $lb)
                        { ?>
                        <option value='<?php echo $this->createUrl("admin/labels/view/lid/".$lb[0]); ?>'
                            <?php if ($lb[0] == $lid) { ?> selected='selected' <?php } ?>
                            ><?php echo $lb[0]; ?>: <?php echo $lb[1]; ?></option>
                        <?php }
                } ?>

            </select>
            <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/labels/newlabelset") ?>', '_top')"
                title="<?php $clang->eTview("Create or import new label set(s)"); ?>">
                <img src='<?php echo $sImageURL; ?>add.png' name='AddLabel' alt='<?php $clang->eT("Create or import new label set(s)"); ?>' /></a>
            <img src='<?php echo $sImageURL; ?>seperator.gif'  alt='' />
            <img src='<?php echo $sImageURL; ?>blank.gif' width='5' height='20' alt='' />

            <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/authentication/logout");?>', '_top')"
                title="<?php $clang->eTview("Logout"); ?>" >
                <img src='<?php echo $sImageURL; ?>logout.png' name='Logout' alt='<?php $clang->eT("Logout"); ?>' /></a>

            <a href="#" onclick="showhelp('show')" title="<?php $clang->eTview("Show help"); ?>">
                <img src='<?php echo $sImageURL; ?>showhelp.png' name='ShowHelp'
                    alt='<?php $clang->eT("Show help"); ?>' /></a>
        </div>
    </div>
   </div>
   <script type="text/javascript"><!--
   var sImageURL = '<?php echo $sImageURL ?>'; //-->
   </script>