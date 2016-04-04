<div class="col-lg-12 list-surveys">
    <div class="row">
        <div class="col-lg-12 content-right">
            <div class="jumbotron message-box <?php if(isset($errormsg) && $errormsg) {echo 'message-box-error';}?>">
                <h2><?php eT("Record Deleted"); ?> (ID: <?php echo $id; ?>)</h2>
                <p>
                    <input class="btn btn-lg btn-default" type='submit' value='<?php eT("Browse responses"); ?>' onclick="window.open('<?php echo $this->createUrl("/admin/responses/sa/index/surveyid/{$surveyid}/all"); ?>', '_top');" />
                </p>
            </div>
        </div>
    </div>
</div>    
