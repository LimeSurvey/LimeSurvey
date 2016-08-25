<div class='row text-warning'>
    <div class='col-sm-2'>
        <span class='fa fa-exclamation-circle fa-4x'></span>
    </div>
    <div class='col-sm-10'>
        <?php echo sprintf($plugin->gT(
                    'A Cint order is paid or about to be paid, but survey %s is not activated. Please activate it <i>as soon as possible</i> to enable the review process.',
                    'js'
                ), $title);
        ?>
    </div>
</div>
