<!-- Token export options -->
<div class="side-body">
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
            $this->widget('ext.SettingsWidget.SettingsWidget', array(
                'settings' => $aSettings,
                'action'=>$sAction,
                'form' => true,
                'title' => gT("Token export options"),
                'buttons' => $aButtons,
            ));
            ?>
        </div>
    </div>
</div>      
        
