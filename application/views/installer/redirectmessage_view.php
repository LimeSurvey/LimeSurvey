<div class="row">
    <div class="col-md-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-md-9">
        <h2><?php echo $title; ?></h2>
        <legend><?php eT("Database exists"); ?></legend>
        <div class='alert alert-success'><?php echo $noticeMessage; ?></div>
        <p><?php echo $text; ?></p>
    </div>
</div>

