<div class='menubar'>
   <div class='menubar-title ui-widget-header'>
   <strong><?php echo $clang->gT("Label Sets Administration"); ?></strong>
   </div>
   <div class='menubar-main'>
   <div class='menubar-left'>
   <a href='<?php echo site_url("admin"); ?>' title="<?php echo $clang->gTview("Return to survey administration"); ?>" >
    <img name='Administration' src='<?php echo $this->config->item('imageurl'); ?>/home.png' align='left' alt='<?php echo $clang->gT("Return to survey administration"); ?>' /></a>
   <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='11' height='20' align='left' alt='' />
   <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' align='left' alt='' />
   <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='76' align='left' height='20' alt='' />
   <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
   <a href='<?php echo site_url("admin/labels/exportmulti");?>' title="<?php echo $clang->gTview("Export Label Set"); ?>" >
    <img src='<?php echo $this->config->item('imageurl'); ?>/dumplabelmulti.png' alt='<?php echo $clang->gT("Export multiple label sets"); ?>' align='left' /></a>
   </div>
   <div class='menubar-right'>
   <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='5' height='20' alt='' />
   <font class='boxcaption'><?php echo $clang->gT("Labelsets"); ?>: </font>
   <select onchange="window.open(this.options[this.selectedIndex].value,'_top')">
   <option value=''
    <?php if (!isset($lid) || $lid<1) { ?> selected='selected' <?php } ?>
    ><?php echo $clang->gT("Please Choose..."); ?></option>
    
    <?php if (count($labelsets)>0)
    {
        foreach ($labelsets as $lb)
        { ?>
            <option value='<?php echo site_url("admin/labels/view/".$lb[0]); ?>'
            <?php if ($lb[0] == $lid) { ?> selected='selected' <?php } ?>
            ><?php echo $lb[0]; ?>: <?php echo $lb[1]; ?></option>
        <?php }
    } ?>

    </select>
    <a href="#" onclick="window.open('<?php echo site_url("admin/labels/newlabel") ?>', '_top')"
     title="<?php echo $clang->gTview("Create or import new label set(s)"); ?>">
    <img src='<?php echo $this->config->item('imageurl'); ?>/add.png' name='AddLabel' alt='<?php echo $clang->gT("Create or import new label set(s)"); ?>' /></a>
   <img src='<?php echo $this->config->item('imageurl'); ?>/seperator.gif'  alt='' />
   <img src='<?php echo $this->config->item('imageurl'); ?>/blank.gif' width='5' height='20' alt='' />
    
    <a href="#" onclick="window.open('<?php echo site_url("admin/authentication/logout");?>', '_top')"
     title="<?php echo $clang->gTview("Logout"); ?>" >
    <img src='<?php echo $this->config->item('imageurl'); ?>/logout.png' name='Logout' alt='<?php echo $clang->gT("Logout"); ?>' /></a>
    
    <a href="#" onclick="showhelp('show')" title="<?php echo $clang->gTview("Show Help"); ?>">
    <img src='<?php echo $this->config->item('imageurl'); ?>/showhelp.png' name='ShowHelp' 
    alt='<?php echo $clang->gT("Show Help"); ?>' /></a>
   </div>
   </div>
   </div>