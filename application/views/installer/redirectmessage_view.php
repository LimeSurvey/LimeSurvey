<div class="row">
    <div class="col-lg-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-lg-9">
        <h2><?php echo $title; ?></h2>
        <legend><?php eT("Database exists"); ?></legend>
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => $noticeMessage,
            'type' => 'success',
        ]);
        ?>
        <p><?php echo $text; ?></p>
    </div>
</div>

