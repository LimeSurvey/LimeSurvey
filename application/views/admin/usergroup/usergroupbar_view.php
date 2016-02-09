<div class='menubar' id="usergroupbar">
    <div class='row container-fluid'>
        <div class="col-lg-6 col-sm-8">

            <!-- Add -->
            <?php if (Permission::model()->hasGlobalPermission('usergroups','create') && isset($usergroupbar['returnbutton']['url'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/usergroups/sa/add"); ?>" role="button">
                    <span class="icon-add text-success"></span>
                    <?php eT("Add new user group"); ?>
                </a>
            <?php endif; ?>

            <!-- Mail to all Members -->
            <?php if(isset($usergroupbar['edit'])): ?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/usergroups/sa/mail/ugid/".$ugid); ?>" role="button">
                    <span class="icon-invite text-success"></span>
                    <?php eT("Mail to all Members"); ?>
                </a>
             <?php endif;?>

            <!-- Edit current user group -->
            <?php if(isset($usergroupbar['edit'])):?>
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/usergroups/sa/edit/ugid/".$ugid); ?>" role="button">
                    <span class="glyphicon glyphicon-pencil text-success"></span>
                    <?php eT("Edit current user group"); ?>
                </a>
            <?php endif;?>

            <!-- Delete current user group -->
            <?php if(isset($usergroupbar['edit']) &&  (Yii::app()->session['loginID'] == $grow['owner_id'] || Permission::model()->hasGlobalPermission('usergroups','delete'))):?>
                <a class="btn btn-default" href='#' onclick="if (confirm('<?php eT("Are you sure you want to delete this entry?","js"); ?>')) { <?php echo convertGETtoPOST($this->createUrl('admin/usergroups/sa/delete/ugid/'.$ugid)); ?>}">
                    <span class="glyphicon glyphicon-trash text-success"></span>
                    <?php eT("Delete current user group"); ?>
                </a>
            <?php endif;?>
        </div>

        <div class="col-lg-6 col-sm-4 text-right">

            <?php if(isset($usergroupbar['savebutton']['form'])):?>
                <a class="btn btn-default" href="#" role="button" id="save-form-button" data-form-id="<?php echo $usergroupbar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok" ></span>
                    <?php echo $usergroupbar['savebutton']['text'];?>
                </a>
            <?php endif;?>

            <!-- Close -->
            <?php if(isset($usergroupbar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $usergroupbar['closebutton']['url']; ?>" role="button">
                    <span class="glyphicon glyphicon-close" ></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>

            <?php if(isset($usergroupbar['returnbutton']['url'])):?>
                <a class="btn btn-default pull-right" href="<?php echo $this->createUrl($usergroupbar['returnbutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-backward" ></span>
                    &nbsp;&nbsp;
                    <?php echo $usergroupbar['returnbutton']['text']; ?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>
