<?php if ($output == "") : ?>
    <?php
    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => gT('Please select filters and click on the "View statistics" button to generate the statistics.'),
        'type' => 'info',
        'htmlOptions' => ['id' => 'view-stats-alert-info']
    ]);
    ?>
<?php else : ?>
    <div class="row">
        <?php echo $output; ?>
    </div>
<?php endif; ?>
<div id="statsContainerLoading">
    <p><?php eT('Please wait, loading data...'); ?></p>
    <div class="preloader loading">
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
        <span class="slice"></span>
    </div>
</div>
