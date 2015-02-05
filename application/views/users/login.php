<div class="row" style="">
    <div class="col-md-6 col-md-offset-3" style="margin-top: 50px;">
        <?php
            /* @var $this CController */
            foreach($loginForms as $name => $settings) {
                $tabs[] = [
                    'label' => $name,
                    'content' => $this->widget('SettingsWidget', [
                        'settings' => $settings
                    ], true)
                ];
            }
            SettingsWidget::class;
            $tabs[0]['active']= true;
            $this->widget('TbTabs', [
                'type' => TbHtml::NAV_TYPE_PILLS,
                'tabs' => $tabs
            ]);
            
        ?>
    </div>
</div>
