<div class='menubar' id="usergroupbar">
    <div class='row container-fluid'>
        <div class="col-lg-6 col-sm-8">

            <!-- Add -->
            <?php if (Permission::model()->hasGlobalPermission('usergroups','create') && isset($usergroupbar['returnbutton']['url'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/usergroups/sa/add"); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/add.png" alt='<?php eT("Add new user group"); ?>'  />
                    <?php eT("Add new user group"); ?>
                </a>                
            <?php endif; ?>            

            <!-- Mail to all Members -->
            <?php if(isset($usergroupbar['edit'])): ?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/usergroups/sa/mail/ugid/".$ugid); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/invite.png" alt='<?php eT("Mail to all Members"); ?>'  />
                    <?php eT("Mail to all Members"); ?>
                </a>                
             <?php endif;?>

            <!-- Edit current user group -->
            <?php if(isset($usergroupbar['edit'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/usergroups/sa/edit/ugid/".$ugid); ?>" role="button">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/edit.png" alt='<?php eT("Edit current user group"); ?>'  />
                    <?php eT("Edit current user group"); ?>
                </a>                                
            <?php endif;?>

            <!-- Delete current user group -->
            <?php if(isset($usergroupbar['edit']) &&  (Yii::app()->session['loginID'] == $grow['owner_id'] || Permission::model()->hasGlobalPermission('usergroups','delete'))):?>
                <a class="btn btn-default" href='#' onclick="if (confirm('<?php eT("Are you sure you want to delete this entry?","js"); ?>')) { <?php echo convertGETtoPOST($this->createUrl('admin/usergroups/sa/delete/ugid/'.$ugid)); ?>}">
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/delete.png" alt='<?php eT("Delete current user group"); ?>'  />
                    <?php eT("Delete current user group"); ?>
                </a>                                
            <?php endif;?>
        </div>
        
        <div class="col-lg-6 col-sm-4 text-right">

            <?php if(isset($usergroupbar['savebutton']['form'])):?>
                <a class="btn btn-default" href="#" role="button" id="save-form-button" aria-data-form-id="<?php echo $usergroupbar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    <?php echo $usergroupbar['savebutton']['text'];?>
                </a>
            <?php endif;?>
            
            <?php if(isset($usergroupbar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $this->createUrl($usergroupbar['closebutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-close" aria-hidden="true"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>
            <?php if(isset($usergroupbar['returnbutton']['url'])):?>
                <a class="btn btn-default pull-right" href="<?php echo $this->createUrl($usergroupbar['returnbutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-backward" aria-hidden="true"></span>
                    &nbsp;&nbsp;
                    <?php echo $usergroupbar['returnbutton']['text']; ?>
                </a>
            <?php endif;?>
                        

            <!-- labelsetchanger -->
            <?php if(isset($usergroupbar['returnbutton']['url'])):?>
                <div class="form-group form-inline col-md-7 pull-right">
                    <label for='labelsetchanger'><?php eT("User groups"); ?>:</label>
                    <select id='labelsetchanger' onchange="window.open(this.options[this.selectedIndex].value,'_top')" class="form-control">
                        <?php echo getUserGroupList($ugid,'optionlist'); ?>
                    </select>            
                </div>
            <?php endif; ?>
                        
        </div>
    </div>
</div>