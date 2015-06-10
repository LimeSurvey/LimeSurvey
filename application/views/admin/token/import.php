<div class='header ui-widget-header'>" . gT("Token file upload") . "</div>\n"
    <div class='messagebox ui-corner-all'>
        <?php if (!empty($sError)) { ?>
            <div class='warningheader'>" . gT("Error") . "</div>
            <p>" . echo $sError .</p>
        <?php } ?>
    </div>
</div>
