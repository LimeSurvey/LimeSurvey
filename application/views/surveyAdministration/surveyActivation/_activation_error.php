<?php

/**
 * View for the message box when survey activation goes wrong.
 */

?>

<div id="pjax-content" class="ls-flex-column align-items-flex-start align-content-center col-11 ls-flex-item transition-animate-width">
    <div class="ls-flex-column fill col-12 overflow-enabled">
        <div class="row col-12">
            <h3 class="pagetitle"><?php eT('Survey activation error'); ?></h3>
            <?php if (App()->getConfig('debug')) : ?>
                <p><?php eT('Database error!'); ?></p>
                <pre>
                    <?php echo $result['error']; ?>
                </pre>
            <?php elseif ($result['error'] == 'surveytablecreation' && !App()->getConfig('debug')) :
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'header' => gT("The survey response table could not be created."),
                    'text' => gT("Usually this is caused by having too many (sub-)questions in your survey. Please try removing questions from your survey."),
                    'type' => 'warning',
                ]);
                ?>
            <?php else : ?>
                <?php
                $this->widget('ext.AlertWidget.AlertWidget', [
                    'text' => gT("Timings table could not be created."),
                    'type' => 'warning',
                ]);
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>
