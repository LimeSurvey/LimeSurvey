<div class="jumbotron">
    <h2 class="pagetitle h3"><?php eT('Add user group');?></h2>
    <?php if($result['warning']) : ?>
    <?php
    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => $result['warning'],
        'type' => 'warning',
    ]);
    ?>
    <?php endif ?>
    <?php if($result['success']) : ?>
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $result['success'],
            'type' => 'success',
        ]);
        ?>
    <?php endif ?>
    <?php if($result['error']) : ?>
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $result['error'],
            'type' => 'error',
        ]);
        ?>
    <?php endif ?>
    <p><?php
        echo CHtml::link(
            gT("Set the permission for this user group on this group."),
            array( "surveysGroupsPermission/viewUserGroup", 'id'=>$model->gsid, 'to' => $ugid),
            array('class' => 'btn btn-outline-secondary')
        );
    ?> </p>
</div>
