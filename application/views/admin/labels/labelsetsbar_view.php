<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php $clang->eT("Label set administration"); ?></strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href='<?php echo $this->createUrl("/admin/index"); ?>'>
                <img src='<?php echo $sImageURL; ?>home.png' alt='<?php $clang->eT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo $sImageURL; ?>blank.gif' width='11' height='20' alt='' />
            <img src='<?php echo $sImageURL; ?>separator.gif' alt='' />
            <img src='<?php echo $sImageURL; ?>blank.gif' width='76' height='20' alt='' />
            <img src='<?php echo $sImageURL; ?>separator.gif' alt='' />
            <a href='<?php echo $this->createUrl("admin/labels/sa/exportmulti");?>'>
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
                        <option data-labelset-id='<?php echo $lb[0]; ?>' value='<?php echo $this->createUrl("admin/labels/sa/view/lid/".$lb[0]); ?>'
                            <?php if ($lb[0] == $lid) { ?> selected='selected' <?php } ?>
                            ><?php echo htmlspecialchars($lb[1],ENT_QUOTES); ?></option>
                        <?php }
                } ?>

            </select>
            <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/labels/sa/newlabelset") ?>', '_top')">
                <img src='<?php echo $sImageURL; ?>add.png' alt='<?php $clang->eT("Create or import new label set(s)"); ?>' /></a>
            <img src='<?php echo $sImageURL; ?>separator.gif'  alt='' />
            <img src='<?php echo $sImageURL; ?>blank.gif' width='5' height='20' alt='' />

            <a href="#" onclick="window.open('<?php echo $this->createUrl("admin/authentication/sa/logout");?>', '_top')">
                <img src='<?php echo $sImageURL; ?>logout.png' alt='<?php $clang->eT("Logout"); ?>' /></a>

            <a href="http://manual.limesurvey.org" target='_blank'>
                <img src='<?php echo $sImageURL; ?>showhelp.png' alt='<?php $clang->eT("Show help"); ?>' /></a>
        </div>
    </div>
</div>
<script type="text/javascript">
    var sImageURL = '<?php echo $sImageURL ?>';
    var duplicatelabelcode='<?php eT('Error: You are trying to use duplicate label codes.','js'); ?>';
    var otherisreserved='<?php eT("Error: 'other' is a reserved keyword.",'js'); ?>';
    var quickaddtitle='<?php eT('Quick-add subquestion or answer items','js'); ?>';
</script>
