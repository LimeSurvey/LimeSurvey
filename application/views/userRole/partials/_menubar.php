<?php
    App()->getClientScript()->registerScriptFile(
        App()->getConfig('adminscripts') . 'topbar.js',
        CClientScript::POS_END
    );
?>
<div class='menubar surveybar' id="rolemanagementbar">
    <div class='row'>
        <div class="col-md-9">
            <?php if (Permission::model()->hasGlobalPermission('superadmin', 'read')) { ?>
                <button data-href="<?= App()->createUrl("userRole/editRoleModal") ?>" data-toggle="modal" title="<?php eT('Add a new permission role'); ?>" class="btn btn-default RoleControl--action--openmodal">
                    <i class="fa fa-plus-circle text-success"></i> <?php eT("Add user role"); ?>
                </button>
                <button data-href="<?= App()->createUrl("userRole/showImportXML") ?>" data-toggle="modal" title="<?php eT('Import permission role from XML'); ?>" class="btn btn-default RoleControl--action--openmodal">
                    <i class="fa fa-upload text-success"></i> <?php eT("Import (XML)"); ?>
                </button>
            <?php } ?>
        </div>
        <div class="col-md-3 text-right">
            <?php if (!isset($inImportView)) : ?>
                <a class="btn btn-default" href="<?php echo $this->createUrl('admin/index'); ?>" role="button">
                    <span class="fa fa-backward"></span>
                    &nbsp;
                    <?php eT('Back'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
