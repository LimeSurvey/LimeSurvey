<?php echo CHtml::beginForm($action, 'post', array('class'=>'form-horizontal container-fluid')) ?>
<div class="row">
    <label class='col-sm-2 col-md-offset-2 text-right control-label' for='ugid'>
        <?php eT("User group"); ?>
    </label>
    <div class='col-sm-4 '>
        <?php echo CHtml::dropDownList(
            'ugid',
            '',
            CHtml::listData($oAddGroupList,'ugid','name'),
            array(
                'empty' => gT("Please choose..."),
                'class'=> 'form-control',
                'required' => true,
            )
        ); ?>
    </div>
    <div class='col-sm-4 '>
        <?php echo CHtml::button(gT("Add group users"),array('class'=>'btn btn-default', 'type'=>'submit')); ?>
    </div>
</div>
</form>
