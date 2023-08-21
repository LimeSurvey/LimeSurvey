<div class="jumbotron">
    <h2 class="pagetitle h3"><?= gT('Delete user');?></h2>
    <?php
    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => sprintf(gT("User permissions deleted for: %s"),CHtml::encode($oUser->users_name)),
        'type' => 'success',
    ]);
    ?>
    <p><?php
        echo CHtml::link(
            gT("Continue"),
            array( "surveysGroupsPermission/index", 'id'=>$model->gsid),
            array('class' => 'btn btn-outline-secondary')
        );
    ?> </p>
</div>
