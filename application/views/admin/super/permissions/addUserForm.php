<?php echo CHtml::beginForm($action, 'post', array('class'=>'form-horizontal container-fluid')) ?>
<div class="row">
    <label class='col-sm-2 col-md-offset-2 text-right control-label' for='uid'>
        <?php eT("User"); ?>
    </label>
    <div class='col-sm-4 '>
        <?php echo CHtml::dropDownList(
            'uid',
            '',
            CHtml::listData(
                $oAddUserList,
                'uid',
                'DisplayName'
            ),
            array(
                'empty' => gT("Please choose..."),
                'class'=> 'form-control',
                'required' => true,
            )
        ); ?>
    </div>
    <div class='col-sm-4 '>
        <?php echo CHtml::button(gT("Add user"),array('class'=>'btn btn-default', 'type'=>'submit')); ?>
    </div>
</div>
</form>
