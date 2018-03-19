<?php
/**
* Display email result
* @var $title string
* @var $message html
*/

if(isset($nosidebodyblock) && $nosidebodyblock === true ){ ?>
    <div class='side-body  <?php echo getSideBodyClass(false); ?>'>
<?php } ?>

<div class="row" id="token-emailpost-messagebox">
    <div class="col-sm-11 col-sm-offset-1 content-right">
        <div class="jumbotron message-box">
            <h2><?php if ($bEmail) eT("Sending invitations..."); else eT("Sending reminders...");?></h2>
            <div style='border: 1px solid #ccc; max-height: 80em; overflow: scroll; text-align:left; padding-left:0.5em;'>
                <?php echo $tokenoutput ?>
            </div>
        </div>
    </div>
</div>

