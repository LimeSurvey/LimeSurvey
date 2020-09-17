<?php
/**
 * View for the message box when survey activation goes wrong.
 */
?>

<div id="pjax-content" class="ls-flex-column align-items-flex-start align-content-center col-11 ls-flex-item transition-animate-width">
    <div id="in_survey_common" class="container-fluid ls-flex-column fill col-12 overflow-enabled">
        <div class="row col-12">
            <h3 class="pagetitle"><?php eT('Survey activation error'); ?></h3>
            <?php if ($result['error'] == 'surveytablecreation'): ?>
                <div class='alert alert-warning' role='alert'>
                    <?php eT("The survey response table could not be created."); ?>
                    <?php eT("Usually this is caused by having too many (sub-)questions in your survey. Please try removing questions from your survey."); ?>
                </div>
            <?php else: ?>
                <div class='alert alert-warning' role='alert'>
                    <?php eT("Timings table could not be created."); ?>
                </div>
            <?php endif; ?>

            <?php if (App()->getConfig('debug')): ?>
                <p><?php eT('Database error'); ?></p>
                <pre>
                    <?php echo $result['error']; ?>
                </pre>
            <?php endif; ?>
        </div>
    </div>
</div>
