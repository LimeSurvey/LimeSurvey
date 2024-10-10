<div class="col-12 list-surveys">
    <div class="row">
        <div class="col-12 content-right">
            <div class="jumbotron message-box <?php if(isset($errormsg) && $errormsg) {echo 'message-box-error';}?>">
                <h2><?php eT("Record Deleted"); ?> (ID: <?php echo $id; ?>)</h2>
                <p>
                    <input
                        class="btn btn-lg btn-outline-secondary"
                        type='submit'
                        value='<?php eT("Browse responses"); ?>'
                        onclick="window.open('<?php echo $this->createUrl(
                            "/responses/browse/", ['surveyId' => $surveyid]
                        ); ?>', '_top');" />
                </p>
            </div>
        </div>
    </div>
</div>    
