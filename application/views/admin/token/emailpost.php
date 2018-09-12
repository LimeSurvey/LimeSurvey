<?php
/**
 * Display email result
 * @var $title string
 * @var $message html
 */
?>
<div class='side-body  <?php echo getSideBodyClass(false); ?>'>
    <div class="row" id="token-emailpost-messagebox">
        <div class="col-sm-11 col-sm-offset-1 content-right">
            <div class="jumbotron message-box">
                <h2 ><?php if ($bEmail) eT("Sending invitations..."); else eT("Sending reminders...");?></h2>
                <p  style='border: 1px solid #ccc; height: 200px; overflow: scroll; text-align:left; padding-left:0.5em;'>
                    <?php echo $tokenoutput ?>
                </p>
            </div>
        </div>
    </div>
</div>
