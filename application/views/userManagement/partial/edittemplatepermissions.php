<div class="modal-header">
    <h3>
        <?php eT("Permission");?>
    </h3>
</div>
<div class="modal-body">
    <div class="container-center">        
        <?=TbHtml::form(array("userManagement/saveThemePermissions"), 'post', array('name'=>'UserManagement--modalform', 'id'=>'UserManagement--modalform')); ?>
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
            <div class="row ls-space margin top-25">
                <button class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1 selector--submitForm" id="submitForm"><?=gT('Save')?></button>
                <button class="btn btn-error col-sm-3 col-xs-5 col-xs-offset-1 selector--exitForm" id="exitForm"><?=gT('Cancel')?></button>
            </div></div>
        </form>
    </div>
</div>

