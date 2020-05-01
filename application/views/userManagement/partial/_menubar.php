<div class='menubar surveybar' id="usermanagementbar">
    <div class='row'>
        <div class="col-md-9">
            <?php if(!isset($inImportView)) { ?>
                <?php if(Permission::model()->hasGlobalPermission('users', 'create')) {
                    ?>
                    <button  data-href="<?=$this->createUrl("userManagement/addEditUser")?>" data-toggle="modal" title="<?php eT('Add a new survey administrator'); ?>"
                             class="btn btn-default UserManagement--action--openmodal">
                        <i class="fa fa-plus-circle text-success"></i> <?php eT("Add user"); ?>
                    </button>
                    <button  data-href="<?=$this->createUrl("userManagement/addDummyUser")?>" data-toggle="modal" title="<?php eT('Add a new survey administrator with random values'); ?>"
                             class="btn btn-default UserManagement--action--openmodal">
                        <i class="fa fa-plus-square text-success"></i> <?=gT('Add dummy user')?>
                    </button>

                    <button  data-href="<?=$this->createUrl("userManagement/renderUserImport",["importFormat"=>"csv"])?>" data-toggle="modal" title="<?php eT('Import survey administrators from CSV'); ?>" class="btn btn-default UserManagement--action--openmodal">
                        <span class="icon-import text-success"></span> <?php eT("Import (CSV)"); ?>
                    </button>

                    <button  data-href="<?=App()->createUrl("userManagement/renderUserImport",["importFormat"=>"json"])?>" data-toggle="modal" title="<?php eT('Import survey administrators from Json'); ?>" class="btn btn-default UserManagement--action--openmodal">
                        <span class="icon-import text-success"></span> <?php eT("Import (JSON)"); ?>
                    </button>

                <?php
                } ?>
                <?php if(Permission::model()->hasGlobalPermission('users', 'export')) { ?>
                    <div div class="btn-group">
                        <button class="btn btn-default" type="button" data-toggle="dropdown">
                            <i class="fa fa-upload text-success"></i> <?php eT("Export");?>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <?= CHtml::link(gT("CSV"),App()->createUrl("userManagement/exportUser",["outputFormat"=>"csv"]));?>
                            </li>
                            <li>
                                <?= CHtml::link(gT("JSON"),App()->createUrl("userManagement/exportUser",["outputFormat"=>"json"]));?>
                            </li>
                        </ul>
                    </div>
                <?php } ?>
            <?php } else {
                ?>
            <a class="btn btn-default" href="<?php echo $this->createUrl('userManagement/index'); ?>" role="button">
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
