<div class="jumbotron">
    <h2 class="pagetitle h3"><?= gT('Delete user');?></h2>
    <p class="alert alert-success"><?= printf(gT("User permissions deleted for: %s"),CHtml::encode($oUser->users_name)); ?>
    <p><?php
        echo CHtml::link(
            gT("Continue"),
            array( "surveysGroupsPermission/index", 'id'=>$model->gsid),
            array('class' => 'btn btn-default')
        );
    ?> </p>
</div>
