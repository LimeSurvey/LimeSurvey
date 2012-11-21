<div id='remotecontrol'>
    <?php echo sprintf($clang->gT('RemoteControl is available using %s for transport and exposes the following functionality:'),$method); ?>
    <br/><br/>
    <?php
    foreach ($list as $method => $info) {
        echo sprintf('<b>%s</b><br/>%s<br/>',$method, $info['description']);
    }
    ?>
</div>