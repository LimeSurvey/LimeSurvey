<div class="row">
    <div class="col-sm-12 col-md-8 col-md-offset-2">
<h1>Database upgrade required.</h1>

<ul>
    <li>Click the button below to start the database upgrade.</li>
    <li>Output will be shown incrementally in the frame below.</li>
    <li>If a migration fails try to fix the issues, reload this page and start again.</li>
</ul>
<iframe id="upgrade" style="width: 100%; min-height: 200px;">

</iframe>
<div class='btn-toolbar'>
<?php
    
    echo TbHtml::button('Start database upgrade.', ['id' => 'start', 'color' => 'primary']);
    if (App()->maintenanceMode) {
        echo TbHtml::linkButton('Exit maintenance mode.', [
            'id' => 'abort', 
            'url' => App()->createUrl('upgrade/database', ['upgrade' => 'abort']),
            'color' => 'danger',
            'confirm' => 'Are you sure you wisth to exist maintenance mode? Exiting maintenance mode without finishing database upgrades could result in a degraded user experience.'
        ]);
    }
?>
</div>
<script>
    $('#start').on('click', function(e) {
        $('#upgrade').attr('src', "<?=  App()->createAbsoluteUrl('upgrade/database', ['upgrade' => 'run']); ?>");
        $('#start').attr('disabled', true);
    })
</script>
</div>
</div>
    