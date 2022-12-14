<?php
App()->getClientScript()->registerScriptFile(
    App()->getConfig('adminscripts') . 'topbar.js',
    CClientScript::POS_END
);
?>
<div class='menubar surveybar' id="usermanagementbar">
    <div class="container-fluid">
        <div class='row'>
            <div class="col-lg-9">
                <?php if (!isset($inImportView)) { ?>
                    <?php if (Permission::model()->hasGlobalPermission('users', 'create')) {
                        ?>
                        <!-- Add User -->
                    <button 
                    	data-href="<?= $this->createUrl("userManagement/addEditUser") ?>" 
                    	data-bs-toggle="modal" 
                    	title="<?php eT('Add a new survey administrator'); ?>"
                        class="btn btn-outline-secondary UserManagement--action--openmodal">
                            <i class="ri-add-circle-fill text-success"></i> <?php eT("Add user"); ?>
                        </button>

                        <!-- Add Dummy User -->
                    <button 
                    	data-href="<?= $this->createUrl("userManagement/addDummyUser") ?>" 
                    	data-bs-toggle="modal" 
                    	title="<?php eT('Add a new survey administrator with random values'); ?>"
                     	class="btn btn-outline-secondary UserManagement--action--openmodal">
                            <i class="ri-add-box-fill text-success"></i> <?= gT('Add dummy user') ?>
                        </button>

                        <!-- Import CSV -->
                    <button data-href="<?= $this->createUrl("userManagement/renderUserImport", ["importFormat" => "csv"]) ?>" data-bs-toggle="modal" title="<?php eT('Import survey administrators from CSV'); ?>" class="btn btn-outline-secondary UserManagement--action--openmodal">
                            <span class="ri-upload-fill text-success"></span> <?php eT("Import (CSV)"); ?>
                        </button>

                        <!-- Import JSON -->
                    <button data-href="<?= App()->createUrl("userManagement/renderUserImport", ["importFormat" => "json"]) ?>" data-bs-toggle="modal" title="<?php eT('Import survey administrators from Json'); ?>" class="btn btn-outline-secondary UserManagement--action--openmodal">
                            <span class="ri-upload-fill text-success"></span> <?php eT("Import (JSON)"); ?>
                        </button>

                        <?php
                    } ?>
                    <?php if (Permission::model()->hasGlobalPermission('users', 'export')) { ?>
                        <div class="btn-group">
                            <!-- Export -->
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown" title="<?php eT('Export survey administrators'); ?>">
                                <i class="ri-download-fill text-success"></i> <?php eT("Export"); ?>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                <?= CHtml::link(gT("CSV"), App()->createUrl("userManagement/exportUser", ["outputFormat" => "csv"]), ["class" => "dropdown-item"]); ?>
                                </li>
                                <li>
                                <?= CHtml::link(gT("JSON"), App()->createUrl("userManagement/exportUser", ["outputFormat" => "json"]), ["class" => "dropdown-item"]); ?>
                                </li>
                            </ul>
                        </div>
                    <?php } ?>
                <?php } else {
                    ?>
                    <a 	class="btn btn-outline-secondary" 
                    	href="<?php echo $this->createUrl('userManagement/index'); ?>" 
                    	role="button">
                        <span class="ri-rewind-fill"></span>
                        &nbsp;
                        <?php eT('Return to main view'); ?>
                    </a>
                    <?php
                } ?>
            </div>

            <div class="col-lg-3 text-end">
                <?php if (!isset($inImportView)): ?>
                    <a class="btn btn-outline-secondary" href="<?php echo $this->createUrl('admin/index'); ?>" role="button">
                        <span class="ri-rewind-fill"></span>
                        &nbsp;
                        <?php eT('Back'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
