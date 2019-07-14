<div class='menubar surveybar' id="usermanagementbar">
    <div class='row'>
        <div class="col-md-9">
            <?php if(!isset($inImportView)) { ?>
                <?php if(Permission::model()->hasGlobalPermission('users', 'create')) {
                    ?>
                    <button  data-href="<?=App()->createUrl("admin/usermanagement/sa/editusermodal")?>" data-toggle="modal" title="<?php eT('Add a new survey administrator'); ?>" class="btn btn-default UserManagement--action--openmodal">
                        <i class="fa fa-plus-circle text-success"></i> <?php eT("Add user"); ?>
                    </button>
                    <button  data-href="<?=App()->createUrl("admin/usermanagement/sa/adddummyuser")?>" data-toggle="modal" title="<?php eT('Add a new survey administrator with random values'); ?>" class="btn btn-default UserManagement--action--openmodal">
                        <i class="fa fa-plus-square text-success"></i> <?=gT('Add dummy user')?>
                    </button>
                    <button  data-href="<?=App()->createUrl("admin/usermanagement/sa/importuser")?>" data-toggle="modal" title="<?php eT('Import survey administrators from CSV'); ?>" class="btn btn-default UserManagement--action--openmodal">
                        <i class="fa fa-upload text-success"></i> <?php eT("Import (CSV)"); ?>
                    </button>
                    <a  href="<?=App()->createUrl("admin/usermanagement/sa/importfromjson")?>" data-toggle="modal" title="<?php eT('Import survey administrators from JSON'); ?>" class="btn btn-default">
                        <i class="fa fa-upload text-success"></i> <?php eT("Import (JSON)"); ?>
                    </a>
                <?php
                } ?>
                <?php if(Permission::model()->hasGlobalPermission('users', 'export')) { ?>
                    <button  data-href="<?=App()->createUrl("admin/usermanagement/sa/exportusers")?>" data-toggle="modal" title="<?php eT('Export survey administrators'); ?>" class="btn btn-default UserManagement--action--openmodal">
                        <i class="fa fa-upload text-success"></i> <?php eT("Export (CSV)");?>
                    </button>
                <?php } ?>
            <?php } else {
                ?>
            <a class="btn btn-default" href="<?php echo $this->createUrl('admin/usermanagement/sa/view'); ?>" role="button">
                    <span class="fa fa-backward"></span>
                    &nbsp;
                    <?php eT('Return to main view'); ?>
                </a>
            <?php
            } ?>
        </div>

        <div class="col-md-3 text-right">
            <?php if(!isset($inImportView)): ?>
                <a class="btn btn-default" href="<?php echo $this->createUrl('admin/index'); ?>" role="button">
                    <span class="fa fa-backward"></span>
                    &nbsp;
                    <?php eT('Return to admin home'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>