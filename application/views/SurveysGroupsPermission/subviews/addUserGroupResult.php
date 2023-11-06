<div class="jumbotron">
    <h2 class="pagetitle h3"><?php eT('Add user group');?></h2>
    <?php if($result['warning']) : ?>
        <p class="alert alert-warning"><?= $result['warning'] ?>
    <?php endif ?>
    <?php if($result['success']) : ?>
        <p class="alert alert-success"><?= $result['success'] ?>
    <?php endif ?>
    <?php if($result['error']) : ?>
        <p class="alert alert-error"><?= $result['error'] ?>
    <?php endif ?>
    <p><?php
        echo CHtml::link(
            gT("Set the permission for this user group on this group."),
            array( "surveysGroupsPermission/viewUserGroup", 'id'=>$model->gsid, 'to' => $ugid),
            array('class' => 'btn btn-default')
        );
    ?> </p>
</div>
