<?php
App()->getClientScript()->registerScriptFile(
    App()->getConfig('adminscripts') . 'topbar.js',
    CClientScript::POS_END
);
?>
<div class='menubar surveybar' id="rolemanagementbar">
    <div class="container-fluid">
        <div class='row'>
            <div class="col-lg-9">
                <?php if (Permission::model()->hasGlobalPermission('superadmin', 'read')) { ?>
                <button data-href="<?= App()->createUrl("userRole/editRoleModal") ?>" data-bs-toggle="modal"
                        title="<?php eT('Add a new permission role'); ?>" class="btn btn-outline-secondary RoleControl--action--openmodal">
                        <i class="fa fa-plus-circle text-success"></i> <?php eT("Add user role"); ?>
                    </button>
                <button data-href="<?= App()->createUrl("userRole/showImportXML") ?>" data-bs-toggle="modal"
                        title="<?php eT('Import permission role from XML'); ?>" class="btn btn-outline-secondary RoleControl--action--openmodal">
                        <i class="fa fa-upload text-success"></i> <?php eT("Import (XML)"); ?>
                    </button>
                <?php } ?>
            </div>
            <div class="col-lg-3 text-end">
                <?php if (!isset($inImportView)) : ?>
                <a class="btn btn-outline-secondary" href="<?php echo $this->createUrl('admin/index'); ?>" role="button">
                        <span class="fa fa-backward"></span>
                        &nbsp;
                        <?php eT('Back'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
