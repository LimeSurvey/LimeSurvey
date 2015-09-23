<div class="row" style="">
    <div class="col-md-6 col-md-offset-3" style="margin-top: 50px;">
        <?php
            /* @var $this CController */
        
            foreach($loginForms as $id => $config) {
                $settings = $config['settings'];
                $settings['_logintype'] = [
                    'type' => 'hidden',
                    'current' => $id
                ];
                $tabs[] = [
                    'active' => !isset($tabs),
                    'label' => $config['label'],
                    'content' => $this->widget('SettingsWidget', [
                        'settings' => $settings,
                        'buttons' => [
                            gT('Log in') => [
                                'type' => 'submit',
                                'color' => 'primary'
                            ]
                        ]
                    ], true)
                ];
            }
            $this->widget('TbTabs', [
                'type' => TbHtml::NAV_TYPE_PILLS,
                'tabs' => $tabs
            ]);
            
        ?>
    </div>
</div>
