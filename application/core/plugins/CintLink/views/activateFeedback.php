<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row welcome survey-action">
        <div class="col-lg-12 content-right">
            <div class='jumbotron message-box'>
                <h3><?php echo $plugin->gT("Activate Survey");?>&nbsp;(<?php echo $surveyId; ?>)</h3>
                <p class='lead'>
                    <?php echo $plugin->gT("Survey has been activated. Results table has been successfully created."); ?>
                </p>
                <p class='lead'>
                    <?php echo $plugin->gT("This survey uses Cint Link. Therefore it's not possible to create a participant table."); ?>
                </p>
                <p>
                    <a
                        href='<?php echo $hrefHome; ?>'
                        class='btn btn-default'
                    />
                        <?php echo $plugin->gT("Back to survey home"); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
