<div class="modal-header">
    <h3>
        <?php eT("ApplyRemove role from user");?>
    </h3>
</div>
<div class="modal-body selector--edit-role-container">
    <div class="container-center form">     
    <?php $form = $this->beginWidget('TbActiveForm', array(
            'id' => 'UserManagement--modalform',
            'action' => App()->createUrl('admin/usermanagement', ['sa' => 'applyaddrole']),
            'enableAjaxValidation'=>false,
            'enableClientValidation'=>false,
        ));?>
   
        <div class="row">
            <div class="col-xs-12 alert alert-info">
                <?=gT("Careful: Applying a role to the user will overwrite any individual permissions given to the user!")?>
            </div>
        </div>
        <div class="form-group">
            <label for="roleselector"><?=gT("Select role to apply to users")?></label>
            <?php $this->widget(
                'yiiwheels.widgets.select2.WhSelect2', 
                array(
                    'name' => 'roleselector',
                    'asDropDownList' => true,
                    'htmlOptions'=>array('multiple'=>'multiple','style'=>"width: 100%"),
                    'data' => $aPossibleRoles,
                    'value' => $aCurrentRoles,
                    )
                );
            ?>
        </div>        
        <div class="row ls-space margin top-15">
            <hr />
        </div>
        <div class="row ls-space margin top-5">
            <button class="btn btn-success col-sm-3 col-xs-5 col-xs-offset-1" id="submitForm"><?=gT('Save')?></button>
            <button class="btn btn-error col-sm-3 col-xs-5 col-xs-offset-1" id="exitForm"><?=gT('Cancel')?></button>
        </div>
    <?php $this->endWidget(); ?>
    </div>
</div>