<div class='messagebox ui-corner-all'>
    <div class='header ui-widget-header'>
        <?php if ($bEmail) $clang->eT("Sending invitations..."); else $clang->eT("Sending reminders...");?>
    </div>
    <?php
    if ($tokenids)
    {
        echo " (" . $clang->gT("Sending to Token IDs") . ":&nbsp;" . implode(", ", $tokenids) . ")";
    }
    ?>
    <br /><br />
    <div style='border: 1px solid #ccc; height: 200px; overflow: scroll; text-align:left; padding-left:0.5em;'>
        <?php echo $tokenoutput ?>
    </div>
</div>
