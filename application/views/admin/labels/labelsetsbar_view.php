<div class='menubar'>
    <div class='menubar-title ui-widget-header'>
        <strong><?php echo $clang->gT("Label Sets Administration"); ?></strong>
    </div>
    <div class='menubar-main'>
        <div class='menubar-left'>
            <a href='<?php echo $this->createUrl('/admin'); ?>' title="<?php echo $clang->gTview("Return to survey administration"); ?>" >
            <img name='Administration' src='<?php echo Yii::app()->getConfig('imageurl'); ?>/home.png' align='left' alt='<?php echo $clang->gT("Return to survey administration"); ?>' /></a>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' width='11' height='20' align='left' alt='' />
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' align='left' alt='' />
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' width='76' align='left' height='20' alt='' />
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif' border='0' hspace='0' align='left' alt='' />
            <a href='<?php echo $this->createUrl('admin/labels/exportmulti'); ?>' title="<?php echo $clang->gTview("Export label set"); ?>" >
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/dumplabelmulti.png' alt='<?php echo $clang->gT("Export multiple label sets"); ?>' align='left' /></a>
        </div>
        <div class='menubar-right'>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' width='5' height='20' alt='' />
            <label for='labelsetchanger'><?php echo $clang->gT("Label sets:");?>: </label>
            <select id='labelsetchanger' onchange="window.open(this.options[this.selectedIndex].value,'_top')">
                <option value=''
                    <?php if (!isset($lid) || $lid<1) { ?> selected='selected' <?php } ?>
                    ><?php echo $clang->gT("Please Choose..."); ?></option>

                <?php 
                        foreach ($labelsets as $lb)
                    {
                ?>
                        <option value='<?php echo $this->createUrl("/admin/labels/view/".$lb['lid']); ?>'
                            <?php if ($lb['lid'] == $lid) { ?> selected='selected' <?php } ?>
                            ><?php echo $lb['lid']; ?>: <?php echo $lb['label_name']; ?></option>
               	<?php }?>

            </select>
            <a href="#" onclick="window.open('<?php echo $this->createUrl('admin/labels/newlabelset'); ?>', '_top')"
                title="<?php echo $clang->gTview("Create or import new label set(s)"); ?>">
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/add.png' name='AddLabel' alt='<?php echo $clang->gT("Create or import new label set(s)"); ?>' /></a>
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/seperator.gif'  alt='' />
            <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/blank.gif' width='5' height='20' alt='' />

            <a href="#" onclick="window.open('<?php echo $this->createUrl('admin/authentication/logout'); ?>', '_top')"
                title="<?php echo $clang->gTview("Logout"); ?>" >
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/logout.png' name='Logout' alt='<?php echo $clang->gT("Logout"); ?>' /></a>

            <a href="#" onclick="showhelp('show')" title="<?php echo $clang->gTview("Show Help"); ?>">
                <img src='<?php echo Yii::app()->getConfig('imageurl'); ?>/showhelp.png' name='ShowHelp'
                    alt='<?php echo $clang->gT("Show Help"); ?>' /></a>
        </div>
    </div>
   </div>