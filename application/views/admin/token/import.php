<div class='header ui-widget-header'>" . $clang->gT("Token file upload") . "</div>\n"
    <div class='messagebox ui-corner-all'>
        <?php if (!empty($sError)) { ?>
            <div class='warningheader'>" . $clang->gT("Error") . "</div>
            <p>" . echo $sError .</p>
        <?php } ?>
    </div>
</div>
