<?php echo CHtml::beginForm($action, 'post', array('class'=>'form-horizontal')) ?>
<div class="row">
    <label class='col-md-2 offset-lg-2 text-end form-label' for='ugid'>
        <?php eT("User group"); ?>
    </label>
    <div class='col-md-4'>
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
    <div class='col-md-4 '>
        <?php echo CHtml::button(gT("Add group users"),array('class'=>'btn btn-outline-secondary', 'type'=>'submit')); ?>
    </div>
</div>
</form>
