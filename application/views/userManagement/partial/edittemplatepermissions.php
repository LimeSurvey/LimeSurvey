<?php
Yii::app()->getController()->renderPartial(
    '/layouts/partial_modals/modal_header',
    ['modalTitle' => gT('Theme template permission')]
);
?>

<?=TbHtml::form(
        array("userManagement/saveThemePermissions"),
        'post',
        array('name'=>'UserManagement--modalform', 'id'=>'UserManagement--modalform')
); ?>
<div class="modal-body">
    <div class="container-center">
            <input type="hidden" name="userid" value="<?php echo $oUser->uid;?>" />
            <div class="list-group-item row list-group-item-info">
                <div class="col-xs-6 text-left">
                    <button id="UserManagement--action-userthemepermissions-select-all" class="btn btn-default"> 
                        <?php eT('Select all');?>
                    </button>
                </div>
                <div class="col-xs-6 text-right">
                    <button id="UserManagement--action-userthemepermissions-select-none" class="btn btn-default"> 
                        <?php eT('Select none');?>
                    </button>
                </div>
            </div>
            <div class="list-group">
                <div class="list-group-item row">
                    <div class="col-xs-6"><?php eT('Theme name');?></div>
                    <div class="col-xs-6"><?php eT('Access');?></div>
                </div>
                <?php foreach ($aTemplates as $aTemplate) {?>
                    <div class="list-group-item row">
                        <div class="col-xs-6"><?=$aTemplate['folder']?></div>
                        <div class="col-xs-6">
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => 'TemplatePermissions['.$aTemplate['folder'].']',
                                'id'=>$aTemplate['folder'].'_use',
                                'value' => $aTemplate['value'],
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off'),
                                'htmlOptions' => ['class' => 'UserManagement--themepermissions-themeswitch']
                            ));
                            ?>
                        </div>
                    </div>
                <?php } ?>
            </div>

    </div>
</div>
<div class="modal-footer modal-footer-buttons row ls-space margin top-25">
    <button class="btn btn-error selector--exitForm" id="exitForm"><?=gT('Cancel')?></button>
    <button class="btn btn-success selector--submitForm" id="submitForm"><?=gT('Save')?></button>
</div>
</form>

