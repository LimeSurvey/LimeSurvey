<?php
/**
 * This is shown when the question has a customized relevance equation
 */
?>

<div class="row">
    <div class="col-12">
        <?php
        $this->widget('ext.AlertWidget.AlertWidget', [
            'text' => gT("Note: This question uses a customized condition. If you create a condition using this editor the current customized condition will be overwritten."),
            'type' => 'warning',
        ]);
        ?>
    </div>
</div>
