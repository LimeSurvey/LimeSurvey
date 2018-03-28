
<div class='col-lg-12'>
    <div class='pagetitle h3'><?php eT('Plugin manager'); ?></div>
    <?php foreach($result as $scannedPlugin): ?>
        <div class='col-sm-12'>
            <label class='control-label'>
                <?php echo $scannedPlugin['pluginName']; ?>
            </label>
            <a href='' class='btn btn-success'>Install</a>
        </div>
    <?php endforeach; ?>
</div>
