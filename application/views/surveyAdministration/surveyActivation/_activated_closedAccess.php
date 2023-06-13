<?php

/** @var string $nameTokensTable */

?>

<div class="row">
        <div class="col-12">
            <div class="card card-primary border-left-success h-100">
                <div class="card-header">
                    <h5 class="card-title">
                        <?php et('Congrats! Your survey has been activated successfully in closed access mode');?>
                    </h5>
                </div>
                <div class="card-body d-flex">
                    <?php
                        et('This survey is now only accessible to users who got an access code either manually or by URL. ');
                        et('To create an access code for your participants, click on "Generate access code" in the top bar. ');
                        et('You can switch back to open-access mode at any time. Click on the red "Delete participants table" button in the top bar. ');
                        et('Participant table ') . '( ' . $nameTokensTable . ' )';
                    ?>
                </div>
            </div>
        </div>
</div>
