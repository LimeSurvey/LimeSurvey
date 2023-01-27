<?php
/**
 * This view render the 'no simple graph' message
 */
?>
<div class="row">
    <div class="col-md-12">
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => gT("No simple graph for this question type"),
        'type' => 'warning',
        'htmlOptions' => ['class' => 'no-simple-graph']
        ]);
        ?>
    </div>
</div>
