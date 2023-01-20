<div class="jumbotron">
    <h2 class="pagetitle h3"><?php eT('Add user');?></h2>
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
        $this->widget('ext.AlertWidget.AlertWidget', ['errorSummaryModel' => $result['error']]);
        ?>
    <?php endif ?>
    <p><?php
        echo CHtml::link(
            gT("Set the permission for this user on this group."),
            array( "surveysGroupsPermission/viewUser", 'id'=>$model->gsid, 'to' => $uid),
            array('class' => 'btn btn-outline-secondary')
        );
    ?> </p>
</div>
